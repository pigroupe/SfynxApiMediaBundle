<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Generalisation\Interfaces;

use Knp\Bundle\GaufretteBundle\FilesystemMap;

use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Manager\EntityManager;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\ResponseMedia;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\MetadataExtractor\MetadataExtractorInterface;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\MediaTransformerInterface;



/**
 * Query Repository Interface
 *
 * @category   Sfynx\ApiMediaBundle\Layers
 * @package    Domain
 * @subpackage Service\Media\Generalisation\Interfaces
 */
interface MediaManagerInterface
{
    /**
     * @param object $entity
     * @param bool $flush
     * @return void
     */
    public function add(object $entity, $flush = true): void;

    /**
     * Add metadata extractor
     *
     * @param MetadataExtractorInterface $metadataExtractor
     * @return EntityManager
     */
    public function addMetadataExtractor(MetadataExtractorInterface $metadataExtractor): EntityManager;

    /**
     * Add media transformer
     *
     * @param MediaTransformerInterface $mediaTransformer
     * @return EntityManager
     */
    public function addMediaTransformer(MediaTransformerInterface $mediaTransformer): EntityManager;

    /**
     * Retrieve mediaRaw
     *
     * @param string $reference
     * @return Media
     */
    public function retrieveMedia($reference): Media;

    /**
     * Add Media
     *
     * @param array $parameters
     * @return Media
     */
    public function addMedia(array $parameters): Media;

    /**
     * Change a media file.
     *
     * @param Media $media
     *
     * @return boolean True if the media was changed, false otherwise.
     * @throws Exception
     */
    public function changeMedia(Media $media): bool;

    /**
     * Delete mediaRaw
     *
     * @param string $reference
     */
    public function deleteMedia($reference): void;

    /**
     * Clear a media cached files.
     *
     * @param Media $media
     *
     * @return boolean True if the cache was clean, false otherwise.
     */
    public function clearMediaCache(Media $media): bool;

    /**
     * transform a given Media to a ResponseMedia based on given parameters
     *
     * @param Media $media
     * @param array $options
     * @return ResponseMedia
     */
    public function transform(Media $media, $options): ResponseMedia;

    /**
     * Get media public uri
     *
     * @param Media $media
     *
     * @return string
     */
    public function getMediaPublicUri(Media $media): string;

    /**
     * Build the storage key
     *
     * @param string $referencePrefix
     * @param string $reference
     * @return string
     */
    public function buildStorageKey($referencePrefix, $reference): string;

    /**
     * Returns the filesystem map.
     *
     * @return FilesystemMap
     */
    public function getFilesystemMap(): FilesystemMap;

    /**
     * Return the configuration
     *
     * @param string $key The configuration key to retrieve if given.
     * @return array|null
     */
    public function getConfiguration($key = null);
}
