<?php

namespace Sfynx\ApiMediaBundle\Layers\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;

class ImportMediaCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    protected $filePath = null;

    /**
     * @var string
     */
    protected $delimiter = null;

    /**
     * @var string
     */
    protected $enclosure = null;

    /**
     * @var string
     */
    protected $provider = null;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sfynx-media:import')
            ->setDescription('Import media')
            ->addArgument('filePath', InputArgument::REQUIRED, 'The file path to use')
            ->addOption('with-header', 'w', InputOption::VALUE_NONE, 'Add this option if the CSV file contains a header')
            ->addOption('delimiter', 'd', InputOption::VALUE_REQUIRED, 'The csv delimiter', ',')
            ->addOption('enclosure', 'c', InputOption::VALUE_REQUIRED, 'The csv enclosure', '"')
            ->addOption('provider', 'p', InputOption::VALUE_REQUIRED, 'The media provider service name', 'batch_media')
            ->addOption('batch', 'b', InputOption::VALUE_REQUIRED, 'To execute the import in batch mode', 1)
            ->setHelp(<<<EOT
The <info>%command.name%</info> command.

Here is an example:
<info>php app/console %command.name% filePathToImport</info>

To prevent CSV header import:
<info>php app/console %command.name% filePathToImport [-w|--with-header]</info>

To specified the CSV delimiter ("," is used by default):
<info>php app/console %command.name% filePathToImport [-d|--delimiter DELIMITER]</info>

To specified the CSV enclosure ('"' is used by default):
<info>php app/console %command.name% filePathToImport [-c|--enclosure ENCLOSURE]</info>

To specified the media provider ("batch_media" is used by default):
<info>php app/console %command.name% filePathToImport [-p|--provider PROVIDER]</info>
EOT
        );
    }

    /**
     * Load data
     *
     * @param  boolean $hasHeader
     * @return array
     */
    protected function loadData($hasHeader = true)
    {
        $rows = array();
        if (($handle = fopen($this->filePath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 5000, $this->delimiter, $this->enclosure)) !== FALSE) {
                $row = $this->createMappedRowData($data);
                if (null !== $row) {
                    $rows[] = $row;
                }
            }

            fclose($handle);
        }

        // Remove the first row if hasHeader is true
        if ($hasHeader) {
            unset($rows[0]);
        }

        return $rows;
    }

    /**
     * Create mapped row data
     *
     * @param  array $data A row data
     * @return array
     */
    protected function createMappedRowData(array $data)
    {
        list(
            $reference,
            $referencePrefix,
            $extension,
            $name,
            $description,
            $size,
            $mimeType,
            $metadata,
            $error
        ) = $data;

        if (empty($reference)) {
            return null;
        }

        return array(
            "source"                => "BATCH",
            "ip_source"             => null,
            "reference"             => $reference,
            "reference_prefix"      => $referencePrefix,
            "extension"             => $extension,
            "provider_service_name" => $this->provider,
            "name"                  => $name,
            "description"           => $description,
            "size"                  => $size,
            "mime_type"             => $mimeType,
            "enabled"               => 1,
            "metadata"              => json_decode($metadata),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timeStart = microtime(true);

        $this->filePath  = $input->getArgument('filePath');
        $hasHeader       = $input->getOption('with-header');
        $this->delimiter = $input->getOption('delimiter');
        $this->enclosure = $input->getOption('enclosure');
        $this->provider  = $input->getOption('provider');
        $batch           = (integer)$input->getOption('batch');

        $countAdded    = 0;
        $countImported = 0;

        $output->writeln('<comment>Start  import</comment>');

        $rows = $this->loadData($hasHeader);
        $output->writeln('');
        $progress = new ProgressBar($output, count($rows));
        $progress->start();

        $mediaManager = $this->getContainer()->get('sfynx.apimedia.manager.media.entity');

        foreach ($rows as $i => $row) {
            $media = new Media();
            $media
                ->setSource($row['source'])
                ->setIpSource($row['ip_source'])
                ->setReference($row['reference'])
                ->setReferencePrefix($row['reference_prefix'])
                ->setExtension($row['extension'])
                ->setProviderServiceName($row['provider_service_name'])
                ->setName($row['name'])
                ->setDescription($row['description'])
                ->setSize($row['size'])
                ->setMimeType($row['mime_type'])
                ->setEnabled($row['enabled'])
                ->setMetadata($row['metadata'])
            ;

            $existingMedia = $mediaManager->getQueryRepository()->findOneBy(array('reference' => $media->getReference()));
            if (null !== $existingMedia) {
                $output->writeln(sprintf('<error>Already extisting media (%s)</error>', $media->getReference()));

                continue;
            }

            try {
                $mediaManager->persist($media, false);
                $countAdded++;
                if (0 === $countAdded % $batch) {
                    $mediaManager->getEntityManager()->flush();
                    $mediaManager->getEntityManager()->clear('TmsMediaBundle:Media');
                    $countImported = $countAdded;
                    $progress->advance($batch);
                }
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            }
        }

        $progress->finish();

        $timeEnd = microtime(true);

        $output->writeln(sprintf(
            '<comment>%d/%d imported [%d sec]</comment>',
            $countImported,
            count($rows),
            $timeEnd - $timeStart
        ));
    }
}
