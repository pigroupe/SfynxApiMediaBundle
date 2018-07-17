<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Generalisation;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Gaufrette\FilesystemInterface;

use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\ResponseMedia;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Generalisation\Interfaces\MediaTransformerInterface;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Token\TokenService;
use Sfynx\CoreBundle\Layers\Domain\Service\Request\Generalisation\RequestInterface;

abstract class AbstractMediaTransformer implements MediaTransformerInterface
{
    /** @var string */
    protected $cacheDirectory;
    /** @var TokenService */
    protected $tokenService;
    /** @var RequestInterface */
    protected $request;
    /** @var array */
    protected $extensions;

    /**
     * @param string $cacheDirectory
     * @param TokenService $tokenService
     * @param RequestInterface $request
     * @param array $extensions
     */
    public function __construct(string $cacheDirectory, TokenService $tokenService, RequestInterface $request, array $extensions = [])
    {
        $this->cacheDirectory = $cacheDirectory;
        $this->tokenService = $tokenService;
        $this->request = $request;
        $this->extensions = $extensions;
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

    /**
     * {@inheritdoc}
     */
    public function transform(FilesystemInterface $storageProvider, Media $media, array $options = [])
    {
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
