<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media;

use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\ImagickException;
use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;

class ImageMedia
{
    private $media;
    private $sourcePath;
    private $imagick;

    /**
     * Constructor
     *
     * @param Media $media
     * @param string $sourcePath
     */
    public function __construct(Media $media, $sourcePath)
    {
        $this->imagick = new \Imagick($sourcePath);
        $this->media = $media;
        $this->sourcePath = $sourcePath;
    }

    /**
     * Save
     *
     * @param string $storePath
     * @throws ImagickException
     * @return ImageMedia
     */
    public function save($storePath)
    {
        if (!$this->imagick->writeImage($storePath)) {
            throw new ImagickException('writeImage');
        }
        $this->imagick->destroy();
        // IMPORTANT to avoid error when multiple GET urls have benn send in same time with the same reference and differents queries
        try {
            unlink($this->sourcePath);
        } catch (\Exception $e) {}

        return true;
    }

    /**
     * Grayscale
     *
     * @throws ImagickException
     * @return \Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\ImageMedia
     */
    public function grayscale()
    {
        if (!$this->imagick->modulateImage(100, 0, 100)) {
            throw new ImagickException('setImageColorspace');
        }
        return $this;
    }

    /**
     * Resize
     *
     * @param integer $width
     * @param integer $height
     * @param integer $maxwidth
     * @param integer $maxheight
     * @param integer $minwidth
     * @param integer $minheight
     * @throws ImagickException
     * @return ImageMedia
     */
    public function resize($width, $height, $maxwidth, $maxheight, $minwidth, $minheight)
    {
        if ($width || $height) {
            if (!$this->imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1)) {
                throw new ImagickException('resizeImage');
            }
            return $this;
        }
        $height = $this->media->getMetadata('height');
        $width = $this->media->getMetadata('width');
        if (!$height) {
            $height = $this->imagick->getImageHeight();
        }
        if (!$width) {
            $width = $this->imagick->getImageWidth();
        }

        if ($minheight && $minheight > $height) {
            $width = $width * $minheight / $height;
            $height = $minheight;
        }
        if ($minwidth && $minwidth > $width) {
            $height = $height * $minwidth / $width;
            $width = $minwidth;
        }
        if ($maxheight && $maxheight < $height) {
            $width = $width * $maxheight / $height;
            $height = $maxheight;
        }
        if ($maxwidth && $maxwidth < $width) {
            $height = $height * $maxwidth / $width;
            $width = $maxwidth;
        }
        if (!$this->imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1)) {
            throw new ImagickException('resizeImage');
        }
        return $this;
    }

    /**
     * Rotate
     *
     * @param integer $degrees
     * @throws ImagickException
     * @return ImageMedia
     */
    public function rotate($degrees)
    {
        if (!$this->imagick->rotateImage(new \ImagickPixel('none'), $degrees)) {
            throw new ImagickException('rotateImage');
        }
        return $this;
    }

    /**
     * Quality
     *
     * @param integer $quality
     * @throws ImagickException
     * @return ImageMedia
     */
    public function quality($quality)
    {
        if (!$this->imagick->setImageCompressionQuality($quality)) {
            throw new ImagickException('setImageCompressionQuality');
        }
        return $this;
    }

    /**
     * Format
     *
     * @param string $format
     * @throws ImagickException
     * @return ImageMedia
     */
    public function format($format)
    {
        if (!$this->imagick->setImageFormat($format)) {
            throw new ImagickException('setImageFormat');
        }
        if ('pdf' === $this->media->getExtension()) {
            $this->imagick->setIteratorIndex(0);
        }
        return $this;
    }

    /**
     * Scale
     *
     * @param integer $scale
     * @throws ImagickException
     * @return ImageMedia
     */
    public function scale($scale)
    {
        $width = $this->media->getMetadata('width') * $scale / 100;
        if (!$this->imagick->resizeImage($width, null, \Imagick::FILTER_LANCZOS, 1)) {
            throw new ImagickException('resizeImage');
        }
        return $this;
    }
}