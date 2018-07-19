<?php

namespace Sfynx\ApiMediaBundle\Layers\Application\Cmd;

use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\MediaNotFoundException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ReplaceMediaCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sfynx-media:replace')
            ->setDescription('Updates the existing media with ones in the specified folder.')
            ->addArgument('path', InputArgument::REQUIRED, 'The folder to look through')
            ->addOption('recursive', 'r', InputOption::VALUE_OPTIONAL, 'Look through sub-folders as well? (recursive)', -1)
            ->addOption('extension', null, InputOption::VALUE_OPTIONAL, 'File extension to look for', '*')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command.

Here is an example:
<info>php app/console %command.name% folderPath</info>
This will go through the files at folderPath and update the EntityManager entries accordingly.
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timeStart = microtime(true);

        //Restrict search to extension / handle '.' or not '.'
        $pattern = $input->getOption('extension');
        if ('*' !== $pattern) {
            $pattern = '*.'.$pattern;
        }

        $finder = new Finder();
        $finder
            ->files()
            ->in($input->getArgument('path'))
            ->name($pattern);
        ;

        //Define if recursive and if so determine the max depth
        if ($input->getOption('recursive') >= 0) {
            $finder->depth('<='.$input->getOption('recursive'));
        }

        $fileCount = iterator_count($finder);
        $imported = 0;

        $output->writeln(['<comment>Starting import</comment>','']);

        $progress = new ProgressBar($output, $fileCount);
        $progress->start();

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        foreach ($finder as $file) {
            //Remove the extension from filename
            $filename = preg_split('/\./', $file->getBasename())[0];

            try {
                //Find corresponding media by reference
                $media = $this->getContainer()->get('sfynx.apimedia.manager.media.entity')->retrieveMedia($filename);
                $media->setUploadedFile(new UploadedFile(
                    $file->getRealPath(),
                    $filename,
                    finfo_file($finfo, $file->getRealPath()),
                    filesize($file->getRealPath()),
                    UPLOAD_ERR_OK,
                    true
                ));
                $this->getContainer()->get('sfynx.apimedia.manager.media.entity')->changeMedia($media);
                $imported += 1;
            } catch (MediaNotFoundException $e) {
                $output->writeln(['',sprintf('<error>%s n\'existe pas en base</error>',$file->getRealPath())]);
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
            }

            $progress->advance();
        }

        $timeEnd = microtime(true);
        $progress->finish();

        $output->writeln(['', '', sprintf(
            '<comment>Imported %d / %d files in %d sec.</comment>',
            $imported,
            $fileCount,
            $timeEnd - $timeStart
        )]);
    }
}
