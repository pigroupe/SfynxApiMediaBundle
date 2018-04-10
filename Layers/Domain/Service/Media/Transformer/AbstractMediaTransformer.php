<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Gaufrette\FilesystemInterface;

use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\ResponseMedia;

abstract class AbstractMediaTransformer implements MediaTransformerInterface
{
    /** @var string */
    protected $cacheDirectory;

    /**
     * @param string $cacheDirectory
     */
    public function __construct(string $cacheDirectory)
    {
        $this->cacheDirectory = $cacheDirectory;
    }

    /**
     * Get available formats
     *
     * @return array
     */
    abstract protected function getAvailableFormats();

    /**
     * Process the transformation
     *
     * @param Filesystem $storageProvider
     * @param Media $media
     * @return ResponseMedia
     */
    abstract protected function process(FilesystemInterface $storageProvider, Media $media, array $options = []);

    /**
     * {@inheritdoc}
     */
    public function checkFormat($format)
    {
        return in_array(strtolower($format), $this->getAvailableFormats());
    }

//    /**
//     * Set default options
//     *
//     * @param OptionsResolver
//     */
//    protected function setDefaultOptions(OptionsResolver $resolver)
//    {
//        $resolver->setRequired([
//            'storage_key',
//            'format'
//        ]);
//        $resolver->setDefaults([
//            'format' => $this->getAvailableFormats(),
//            'cacheStorageProvider' => ''
//        ]);
//    }

    /**
     * {@inheritdoc}
     */
    public function transform(FilesystemInterface $storageProvider, Media $media, array $options = [])
    {
//        $resolver = new OptionsResolver();
//        $this->setDefaultOptions($resolver);
//        $options = $resolver->resolve($options);

//        dump($options);exit;

        $options['cacheDirectory'] = $this->cacheDirectory;

        $responseMedia = $this
            ->process($storageProvider, $media, $options)
            ->setETag(sprintf('%s%s',
                $media->getReference(),
                null !== $this->getFormat($options) ? '.' . $this->getFormat($options) : ''
            ))
        ;

        return $responseMedia;
    }

    /**
     * @param array $options
     * @return mixed
     */
    protected function getFormat(array $options)
    {
        return $options['format'];
    }
}
