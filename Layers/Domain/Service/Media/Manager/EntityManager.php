<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Manager;

use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Generalisation\Interfaces\MediaManagerInterface;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Manager\Event\MediaEvent;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Manager\Event\MediaEvents;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\MetadataExtractor\MetadataExtractorInterface;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\MediaTransformerInterface;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\ResponseMedia;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\NoMatchedTransformerException;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\MediaNotFoundException;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\MediaAlreadyExistException;

use Sfynx\CoreBundle\Layers\Domain\Model\Interfaces\EntityInterface;
use Sfynx\CoreBundle\Layers\Domain\Service\Manager\Generalisation\Interfaces\ManagerInterface;
use Sfynx\CoreBundle\Layers\Domain\Service\Manager\Generalisation\AbstractManager;
use Sfynx\CoreBundle\Layers\Infrastructure\Persistence\Factory\Generalisation\AdapterFactoryInterface;

/**
 * Media manager.
 * @category   Sfynx\ApiMediaBundle\Layers
 * @package    Domain
 * @subpackage Service\Media\Manager
 */
class EntityManager extends AbstractManager implements MediaManagerInterface, ManagerInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    /** @var FilesystemMap */
    protected $filesystemMap;
    /** @var string */
    protected $cacheDirectory;
    /** @var array */
    protected $configuration;
    /** @var array */
    protected $metadataExtractors;
    /** @var array */
    protected $mediaTransformers;

    /**
     * @var array $defaults List of default values for optional parameters.
     */
    protected $defaults = [
        'blob_storage'       => null,
        'storage_providers'  => [],
        'storage_provider'   => null,
        'mapping'            => null,
        'description'        => null,
        'extension'          => null,
        'ip_source'          => null,
        'metadata'           => [],
        'mime_type'          => null,
        'name'               => null,
        'processing_file'    => null,
        'size'               => null,
        'source'             => null,
        'reference'          => null,
        'reference_prefix'   => null,
    ];

    /**
     * @var string[] $required List of required parameters for each methods.
     */
    protected $required = [
        'api_public_endpoint',
        'cache_directory',
        'media',
        'working_directory',
        'storage_providers',
        'storage_provider',
    ];

    /**
     * @var array[] $allowedTypes List of allowed types for each methods.
     */
    protected $allowedTypes = [
        'mapping'             => array('null', 'array'),
        'storage_providers'   => array('null', 'array'),
        'storage_provider'    => array('string'),
        'api_public_endpoint' => array('string'),
        'cache_directory'     => array('string'),
        'working_directory'   => array('string'),
        'description'         => array('null', 'string'),
        'extension'           => array('null', 'string'),
        'ip_source'           => array('null', 'string'),
        'media'               => array('Symfony\Component\HttpFoundation\File\UploadedFile'),
        'metadata'            => array('null', 'string', 'array'),
        'mime_type'           => array('null', 'string'),
        'name'                => array('null', 'string'),
        'processing_file'     => array('null', 'Symfony\Component\HttpFoundation\File\File'),
        'size'                => array('null', 'integer'),
        'source'              => array('null', 'string'),
        'reference'           => array('null', 'string'),
        'reference_prefix'    => array('null', 'string'),
    ];

    /**
     * @var array[] $normalizers List of normalizers transformation for each methods.
     */
    protected function getNormalizers()
    {
        return [
            'description' => function (Options $options, $value) {
                if (null !== $value) {
                    return $value;
                }
                return $options['media']->getClientOriginalName();
            },
            'extension' => function (Options $options, $value) {
                return $options['media']->guessExtension();
            },
            'metadata' => function (Options $options, $value) {
                if (null === $value) {
                    return [];
                }
                if (is_array($value)) {
                    return $value;
                }
                $decodedMetadata = json_decode($value, true);

                if (null === $decodedMetadata) {
                    return [];
                }

                return $decodedMetadata;
            },
            'mime_type' => function (Options $options, $value) {
                return $options['media']->getMimeType();
            },
            'name' => function (Options $options, $value) {
                if (null !== $value) {
                    return $value;
                }
                return $options['media']->getClientOriginalName();
            },
            'processing_file' => function (Options $options, $value) {
                return $options['media']->move(
                    $options['working_directory'],
                    uniqid('tmp_media_')
                );
            },
            'size' => function (Options $options, $value) {
                return $options['processing_file']->getSize();
            },
            'reference' => function (Options $options, $value) {
                $now = new \DateTime();

                return sprintf('%s-%s-%s-%d',
                    sprintf("%u", crc32($options['source'])),
                    $now->format('U'),
                    md5(sprintf("%s%s%s",
                        $options['mime_type'],
                        $options['name'],
                        $options['size']
                    )),
                    rand(0, 9999)
                );
            },
            'reference_prefix' => function (Options $options, $value) {
                return EntityManager::guessReferencePrefix($options);
            }
        ];
    }

    /**
     * Constructor
     *
     * @param AdapterFactoryInterface $factory
     * @param EventDispatcherInterface $eventDispatcher
     * @param FilesystemMap            $filesystemMap
     * @param $cacheDirectory
     * @param array                    $configuration
     */
    public function __construct(
        AdapterFactoryInterface $factory,
        EventDispatcherInterface $eventDispatcher,
        FilesystemMap $filesystemMap,
        $cacheDirectory,
        array $configuration = []
    )
    {
        parent::__construct($factory);

        $this->eventDispatcher    = $eventDispatcher;
        $this->filesystemMap      = $filesystemMap;
        $this->cacheDirectory     = $cacheDirectory;
        $this->configuration      = $configuration;
        $this->metadataExtractors = [];
        $this->mediaTransformers  = [];
    }

    /**
     * Magic call
     * Triger to repository methods call
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->getRepositoryQuery(), $method), $args);
    }

    /**
     * Get EventDispatcher
     *
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function add(EntityInterface $entity, $flush = true): void
    {
        $this->getEventDispatcher()->dispatch(
            MediaEvents::PRE_CREATE,
            new MediaEvent($entity)
        );

        $this->getCommandRepository()->persist($entity, $flush);

        $this->getEventDispatcher()->dispatch(
            MediaEvents::POST_CREATE,
            new MediaEvent($entity)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function update(EntityInterface $entity, $andFlush = true): void
    {
        $this->getEventDispatcher()->dispatch(
            MediaEvents::PRE_UPDATE,
            new MediaEvent($entity)
        );

        $this->getCommandRepository()->merge($entity, true);

        $this->getEventDispatcher()->dispatch(
            MediaEvents::POST_UPDATE,
            new MediaEvent($entity)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(EntityInterface $entity): void
    {
        $this->getEventDispatcher()->dispatch(
            MediaEvents::PRE_DELETE,
            new MediaEvent($entity)
        );

        $this->getCommandRepository()->remove($entity, true);

        $this->getEventDispatcher()->dispatch(
            MediaEvents::POST_DELETE,
            new MediaEvent($entity)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addMetadataExtractor(MetadataExtractorInterface $metadataExtractor): EntityManager
    {
        $this->metadataExtractors[] = $metadataExtractor;
        return $this;
    }

    /**
     * Guess a metadata extractor based on the given mime type
     *
     * @param string $mimeType
     * @return MetadataExtractorInterface
     */
    protected function guessMetadataExtractor($mimeType): MetadataExtractorInterface
    {
        foreach ($this->metadataExtractors as $metadataExtractor) {
            if ($metadataExtractor->checkMimeType($mimeType)) {
                return $metadataExtractor;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addMediaTransformer(MediaTransformerInterface $mediaTransformer): EntityManager
    {
        $this->mediaTransformers[] = $mediaTransformer;
        return $this;
    }

    /**
     * Guess a transformer on the given format
     *
     * @param string $format
     * @return MediaTransformerInterface
     */
    protected function guessMediaTransformer($format): MediaTransformerInterface
    {
        foreach ($this->mediaTransformers as $mediaTransformer) {
            if ($mediaTransformer->checkFormat($format)) {
                return $mediaTransformer;
            }
        }
        throw new NoMatchedTransformerException($format);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveMedia($reference): Media
    {
        $media = $this->getQueryRepository()->findOneBy(['reference' => $reference]);
        if (!$media) {
            throw new MediaNotFoundException($reference);
        }
        return $media;
    }

    /**
     * {@inheritdoc}
     */
    public function addMedia(array $parameters): Media
    {
        $resolver = new OptionsResolver();
        $this->setupParameters($resolver);

        $resolvedParameters = $resolver->resolve(array_merge(
            $this->getConfiguration(),
            $parameters
        ));

        $media = $this->getQueryRepository()->findOneBy([
            'reference' => $resolvedParameters['reference']
        ]);
        if (null !== $media) {
            throw new MediaAlreadyExistException();
        }

        $provider = $this->getFilesystemMap()->get($resolvedParameters['storage_provider']);
        $provider->write(
            $this->buildStorageKey(
                $resolvedParameters['reference_prefix'],
                $resolvedParameters['reference']
            ),
            file_get_contents($resolvedParameters['processing_file']->getRealPath())
        );

        // Keep media informations in database
        $media = new Media();
        $media
            ->setSource($resolvedParameters['source'])
            ->setIpSource($resolvedParameters['ip_source'])
            ->setReference($resolvedParameters['reference'])
            ->setReferencePrefix($resolvedParameters['reference_prefix'])
            ->setExtension($resolvedParameters['extension'])
            ->setProviderServiceName($resolvedParameters['storage_provider'])
            ->setName($resolvedParameters['name'])
            ->setDescription($resolvedParameters['description'])
            ->setSize($resolvedParameters['size'])
            ->setMimeType($resolvedParameters['mime_type'])
            ->setMetadata(array_merge_recursive(
                $resolvedParameters['metadata'],
                $this
                    ->guessMetadataExtractor($resolvedParameters['mime_type'])
                    ->extract($resolvedParameters['processing_file']->getRealPath())
            ))
        ;
        $this->add($media);

        // Remove the media once the provider has well stored it.
        unlink($resolvedParameters['processing_file']->getRealPath());
        $resolvedParameters['processing_file'] = null;

        return $media;
    }

    /**
     * {@inheritdoc}
     */
    public function changeMedia(Media $media): bool
    {
        if (null == $media->getUploadedFile()) {
            return false;
        }

        $workingFileName = uniqid('tmp_media_');
        $file = $media->getUploadedFile()->move(
            $this->getConfiguration("working_directory"),
            $workingFileName
        );

        $media
            ->setExtension($file->guessExtension())
            ->setMimeType($file->getMimeType())
            ->setName($media->getUploadedFile()->getClientOriginalName())
            ->setSize($file->getSize())
        ;
        $this->update($media);

        $storageProvider = $this->getFilesystemMap()->get($media->getProviderServiceName());
        $storageIdentifier = $this->buildStorageKey($media->getReferencePrefix(), $media->getReference());

        $storageProvider->delete($storageIdentifier);
        $storageProvider->write($storageIdentifier, file_get_contents($file->getRealPath()));
        $this->clearMediaCache($media);
        unlink($this->getConfiguration("working_directory").DIRECTORY_SEPARATOR.$workingFileName);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMedia($reference): void
    {
        $media = $this->retrieveMedia($reference);
        $storageProvider = $this->getFilesystemMap()->get($media->getProviderServiceName());
        $storageProvider->delete(
            $this->buildStorageKey(
                $media->getReferencePrefix(),
                $media->getReference()
            )
        );
        $this->delete($media);
    }

    /**
     * {@inheritdoc}
     */
    public function clearMediaCache(Media $media): bool
    {
        $process = new Process(sprintf('rm -f %s/%s_*', $this->cacheDirectory, $media->getReference()));
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(Media $media, $options): ResponseMedia
    {
        $mediaTransformer = $this->guessMediaTransformer($options['format']);

        return $mediaTransformer->transform(
            $this->getFilesystemMap()->get($media->getProviderServiceName()),
            $media,
            array_merge(
                $options,
                [
                    'storage_key' => $this->buildStorageKey(
                        $media->getReferencePrefix(),
                        $media->getReference()
                    )
                ]
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMediaPublicUri(Media $media): string
    {
        return sprintf('%s/media/%s',
            $this->getConfiguration('api_public_endpoint'),
            $media->getReference()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildStorageKey($referencePrefix, $reference): string
    {
        if (null === $referencePrefix) {
            return $reference;
        }
        return sprintf('%s/%s', $referencePrefix, $reference);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilesystemMap(): FilesystemMap
    {
        return $this->filesystemMap;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration($key = null)
    {
        if (null === $key) {
            return $this->configuration;
        }
        if (isset($this->configuration[$key])) {
            return $this->configuration[$key];
        }
        return null;
    }

    /**
     * Guess reference prefix
     *
     * @param array|Media $media
     *
     * @return string|null.
     */
    public static function guessReferencePrefix($media)
    {
        $metadata = [];
        $nodes = [];
        $source   = null;

        if ($media instanceof Media) {
            $metadata = $media->getMetadata();
            $source   = $media->getSource();
        } else {
            $metadata = $media['metadata'];
            $source   = $media['source'];
        }

        if (isset($metadata['customer'])) {
            $nodes[] = $metadata['customer'];
        }
        if (isset($metadata['offer'])) {
            $nodes[] = $metadata['offer'];
        }
        if (!empty($nodes)) {
            return implode('/', $nodes);
        }
        if (!empty($source)) {
            return $source;
        }
        return null;
    }

    /**
     * Setup parameters.
     *
     * @param OptionsResolver $resolver.
     * @return array
     */
    protected function setupParameters(OptionsResolver $resolver)
    {
        $resolver->setDefaults($this->defaults);
        $resolver->setRequired($this->required);

        foreach ($this->allowedTypes as $optionName => $optionTypes) {
            $resolver->setAllowedTypes($optionName, $optionTypes);
        }
        foreach ($this->getNormalizers() as $optionName => $optionValues) {
            $resolver->setNormalizer($optionName, $optionValues);
        }
    }
}
