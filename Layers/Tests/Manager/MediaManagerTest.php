<?php

namespace Sfynx\ApiMediaBundle\Layers\Tests\Manager;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\EntityManager;
use Gaufrette\Adapter\Local;
use Gaufrette\Filesystem;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Manager\EntityManager;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\MetadataExtractor\DefaultMetadataExtractor;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\MetadataExtractor\ImageMetadataExtractor;

class MediaManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $entityRepository = $this->getMockBuilder("Doctrine\ORM\EntityRepository")
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $entityRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue(null))
        ;

        $entityManager = $this->getMockBuilder("Doctrine\ORM\EntityManager")
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($entityRepository))
        ;

        $eventDispatcher = $this->getMockBuilder("Symfony\Component\EventDispatcher\EventDispatcherInterface")
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $localAdapter = new Local('/tmp/media_storage', true);
        $filesystem = new Filesystem($localAdapter);

        $gaufrette = $this->getMockBuilder("Knp\Bundle\GaufretteBundle\FilesystemMap")
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $gaufrette
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($filesystem))
        ;

        $this->mediaManager = new EntityManager(
            array(
                'working_directory'   => '/tmp/media_working',
                'cache_directory'     => '/tmp/media_cache',
                'storage_provider'    => 'default_media',
                'api_public_endpoint' => '//media-manager.local/app_dev.php/api',
            ),
            $entityManager,
            $eventDispatcher,
            $gaufrette
        );

        // MetadataExtractor
        $imageMetadataExtractor   = new ImageMetadataExtractor();
        $defaultMetadataExtractor = new DefaultMetadataExtractor();

        $this
            ->mediaManager
            ->addMetadataExtractor($imageMetadataExtractor)
            ->addMetadataExtractor($defaultMetadataExtractor)
        ;
    }

    public function testAddMedia()
    {
        copy(__DIR__.'/../data/linux.png', '/tmp/test_copy_linux.png');

        $uploadedFile = new UploadedFile(
            '/tmp/test_copy_linux.png',
            'dummy_test_file',
            null,
            null,
            null,
            true
        );

        $media = $this->mediaManager->addMedia(array('media' => $uploadedFile));

        $this->assertEquals($media->getExtension(), 'png');
        $this->assertEquals($media->getProviderServiceName(), 'default_media');
        $this->assertEquals($media->getMetadata(), array('width' => 128, 'height' => 128));
        $this->assertTrue($media->getEnabled());
    }
}