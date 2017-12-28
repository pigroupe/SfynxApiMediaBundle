<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer;

use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Gaufrette\Filesystem;

use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\ResponseMedia;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\ImageMedia;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\UnavailableTransformationException;

class UnicaMediaTransformer extends AbstractMediaTransformer
{
    /**
     * @var string
     */
    private $cacheDirectory;

    /**
     * @var string
     */
    private $batchPath;

    private static $fileinfo;

    /**
     * @param string $cacheDirectory
     * @param string $batchPath
     */
    public function __construct($cacheDirectory, $batchPath)
    {
        $this->cacheDirectory = $cacheDirectory.'/unica';
        $this->batchPath      = $batchPath;

        self::$fileinfo = finfo_open(FILEINFO_MIME_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAvailableFormats()
    {
        return array('unica');
    }

    /**
     * {@inheritdoc}
     */
    public function process(Filesystem $storageProvider, Media $media, array $options = array())
    {
        if ('batch_media' !== $media->getProviderServiceName()) {
            throw new UnavailableTransformationException(array(
                'provider_service_name' => $media->getProviderServiceName()
            ));
        }

        $fs         = new SymfonyFilesystem();
        $run        = str_replace('/clean', '', $media->getReferencePrefix());
        $pdfPath    = sprintf('%s/%s.pdf', $this->batchPath, $run);
        $page       = $media->getMetadata('page');
        $outputPath = sprintf('%s/%s-%d.pdf', $this->cacheDirectory, $run, $page);

        if (!$fs->exists($outputPath)) {
            if (!$fs->exists($this->cacheDirectory)) {
                $fs->mkdir($this->cacheDirectory, 0770);
            }

            $process = new Process(sprintf(
                'pdftk %s cat %d output %s',
                $pdfPath,
                $page,
                $outputPath
            ));

            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
        }

        $responseMedia = new ResponseMedia();

        return $responseMedia
            ->setContent(file_get_contents($outputPath))
            ->setContentType(finfo_file(self::$fileinfo, $outputPath))
            ->setContentLength(filesize($outputPath))
            ->setContentDisposition(sprintf('attachment; filename="%s.pdf"', $media->getReference()))
            ->setLastModifiedAt(\DateTime::createFromFormat('U', filemtime($outputPath)))
        ;
    }
}
