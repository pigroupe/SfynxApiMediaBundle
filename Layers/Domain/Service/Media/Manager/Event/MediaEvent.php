<?php

namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Manager\Event;

use Symfony\Component\EventDispatcher\Event;
use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;

/**
 * MediaEvent
 */
class MediaEvent extends Event
{
    protected $media;

    /**
     * Constructor
     *
     * @param Media $media
     */
    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    /**
     * Get Object
     *
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
    }
}