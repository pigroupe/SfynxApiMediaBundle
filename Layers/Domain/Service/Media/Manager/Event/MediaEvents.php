<?php

namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Manager\Event;

/**
 * MediaEvents
 *
 * @author Gabriel Bondaz <gabriel.bondaz@idci-consulting.fr>
 */
final class MediaEvents
{
    /**
     * @var string
     */
    const PRE_CREATE =  'sfynx_api_media.media.pre_create';
    const POST_CREATE = 'sfynx_api_media.media.post_create';

    const PRE_UPDATE =  'sfynx_api_media.media.pre_update';
    const POST_UPDATE = 'sfynx_api_media.media.post_update';

    const PRE_DELETE =  'sfynx_api_media.media.pre_delete';
    const POST_DELETE = 'sfynx_api_media.media.post_delete';
}
