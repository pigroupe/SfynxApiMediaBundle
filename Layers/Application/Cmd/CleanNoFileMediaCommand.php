<?php

namespace Sfynx\ApiMediaBundle\Layers\Application\Cmd;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;

class CleanNoFileMediaCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sfynx-media:clean:no-file')
            ->setDescription('Display or remove media without associated files')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'The limit to processed', 10000)
            ->addOption('offset', 'o', InputOption::VALUE_REQUIRED, 'The offset to processed', 0)
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'if a media file is missing, the entity will be removed.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command.

<info>php app/console %command.name% -f</info>

If you have some doubt about media integrity, you could check it by this way.

<info>php app/console %command.name%</info>

Alternatively, you can clean media entities and remove those have no file associated:

<info>php app/console %command.name% --force</info>

EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timeStart = microtime(true);
        $output->writeln(sprintf('<comment>Start Media Cleaner</comment>'));

        $mediaManager = $this->getContainer()->get('sfynx.apimedia.manager.media.entity');

        $medias = $mediaManager->findBy(
            array(),
            array(),
            $input->getOption('limit'),
            $input->getOption('offset')
        );
        $noFiles = 0;
        $action = $input->getOption('force') ? 'REMOVED' : 'TO REMOVE';

        $progress = new ProgressBar($output, count($medias));
        $output->writeln('');
        $progress->start();
        $table = new Table($output);
        $table->setHeaders(array('Action', 'ID', 'ProviderServiceName', 'ReferencePrefix', 'Reference'));

        foreach ($medias as $media) {
            try {
                $storageProvider = $mediaManager
                    ->getFilesystemMap()
                    ->get($media->getProviderServiceName())
                ;

                $fileExists = $storageProvider
                    ->getAdapter()
                    ->exists($mediaManager->buildStorageKey(
                        $media->getReferencePrefix(),
                        $media->getReference()
                    ))
                ;

                if (!$fileExists) {
                    if ($input->getOption('force')) {
                        $mediaManager->delete($media);
                    }

                    $table->addRow(array(
                        $action,
                        $media->getId(),
                        $media->getProviderServiceName(),
                        $media->getReferencePrefix(),
                        $media->getReference(),
                    ));
                    $noFiles++;
                }
            } catch (\Exception $e) {
                $table->addRow(array(
                    'ERROR: '.$e->getMessage(),
                    $media->getId(),
                    $media->getProviderServiceName(),
                    $media->getReferencePrefix(),
                    $media->getReference(),
                ));
            }

            $progress->advance();
        }

        $progress->finish();
        $output->writeln('');
        $output->writeln('');

        $table->setStyle('borderless');
        $table->render();

        $timeEnd = microtime(true);
        $time = $timeEnd - $timeStart;

        $output->writeln('');
        $output->writeln(sprintf(
            '<comment>%d no file media %s [%d sec]</comment>',
            $noFiles,
            $action,
            $time
        ));
    }
}
