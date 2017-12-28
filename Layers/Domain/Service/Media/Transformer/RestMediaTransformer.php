<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer;

use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Gaufrette\Filesystem;

use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\ResponseMedia;
use Sfynx\CrawlerBundle\Crawler\Transformer\Doctrine2OtherTransformer;

class RestMediaTransformer extends AbstractMediaTransformer
{
    /**
     * {@inheritdoc}
     */
    protected function getAvailableFormats()
    {
        return array('json', 'xml', 'csv');
    }

    /**
     * {@inheritdoc}
     */
    public function process(Filesystem $storageProvider, Media $media, array $options = [])
    {
        $responseMedia = new ResponseMedia();
        $export = Doctrine2OtherTransformer::export($media, $options['format']);

        $responseMedia
            ->setContent($export->getContent())
            ->setContentType(sprintf(
                '%s; charset=UTF-8',
                $export->getContentType()
            ))
            ->setLastModifiedAt($media->getCreatedAt())
        ;

        return $responseMedia;
    }
}
