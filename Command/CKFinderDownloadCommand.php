<?php
/*
 * This file is a part of the CKFinder bundle for Symfony.
 *
 * Copyright (C) 2016, CKSource - Frederico Knabben. All rights reserved.
 *
 * Licensed under the terms of the MIT license.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace CKSource\Bundle\CKFinderBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class CKFinderDownloadCommand
 *
 * Command that downloads the CKFinder package and puts assets to the Resources/public directory of the bundle.
 */
class CKFinderDownloadCommand extends ContainerAwareCommand
{
    const ZIP_PACKAGE_URL = 'http://download.cksource.com/CKFinder/CKFinder%20for%20PHP/3.4.4/ckfinder_php_3.4.4.zip';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('ckfinder:download')
             ->setDescription('Downloads the CKFinder distribution package and extracts it to CKSourceCKFinderBundle.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targetPublicPath = realpath(__DIR__ . '/../Resources/public');

        if (!is_writable($targetPublicPath)) {
            $output->writeln('<error>The CKSourceCKFinderBundle::Resources/public directory is not writable (used path: ' . $targetPublicPath . ').</error>');

            return;
        }

        $targetConnectorPath = realpath(__DIR__ . '/../_connector');

        if (!is_writable($targetConnectorPath)) {
            $output->writeln('<error>The CKSourceCKFinderBundle::_connector directory is not writable (used path: ' . $targetConnectorPath . ').</error>');

            return;
        }

        if (file_exists($targetPublicPath.'/ckfinder/ckfinder.js')) {
            $questionHelper = $this->getHelper('question');
            $questionText =
                'It looks like the CKFinder distribution package has already been installed. ' .
                "This command will overwrite the existing files.\nDo you want to proceed? [y/n]: ";
            $question = new ConfirmationQuestion($questionText, false);

            if (!$questionHelper->ask($input, $output, $question)) {
                return;
            }
        }

        /** @var ProgressBar $progressBar */
        $progressBar = null;

        $maxBytes = 0;
        $ctx = stream_context_create(array(), array(
            'notification' =>
            function ($notificationCode, $severity, $message, $messageCode, $bytesTransferred, $bytesMax) use (&$maxBytes, $output, &$progressBar) {
                switch ($notificationCode) {
                    case STREAM_NOTIFY_FILE_SIZE_IS:
                        $maxBytes = $bytesMax;
                        $progressBar = new ProgressBar($output, $bytesMax);
                        break;
                    case STREAM_NOTIFY_PROGRESS:
                        $progressBar->setProgress($bytesTransferred);
                        break;
                }
            }
        ));

        $output->writeln('<info>Downlading the CKFinder 3 distribution package.</info>');

        $zipContents = @file_get_contents(self::ZIP_PACKAGE_URL, false, $ctx);

        if ($zipContents === false) {
            $output->writeln(
                '<error>Could not download the distribution package of CKFinder.</error>');

            return;
        }

        if ($progressBar) {
            $progressBar->finish();
        }

        $output->writeln("\n" . 'Extracting CKFinder to the CKSourceCKFinderBundle::Resources/public directory.');

        $tempZipFile = tempnam(sys_get_temp_dir(), 'tmp');
        file_put_contents($tempZipFile, $zipContents);
        $zip = new \ZipArchive();
        $zip->open($tempZipFile);

        $zipEntries = array();

        // These files won't be overwritten if already exists
        $filesToKeep = array(
            'ckfinder/config.js',
            'ckfinder/ckfinder.html'
        );

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);

            if (in_array($entry, $filesToKeep) && file_exists($targetPublicPath . '/' . $entry)) {
                continue;
            }

            $zipEntries[] = $entry;
        }

        $zip->extractTo($targetPublicPath, $zipEntries);

        $fs = new Filesystem();

        $output->writeln('Moving the CKFinder connector to the CKSourceCKFinderBundle::_connector directory.');
        $fs->mirror(
            $targetPublicPath . '/ckfinder/core/connector/php/vendor/cksource/ckfinder/src/CKSource/CKFinder',
            $targetConnectorPath
        );

        $output->writeln('Cleaning up.');
        $fs->remove(array(
            $tempZipFile,
            $targetPublicPath . '/ckfinder/core',
            $targetPublicPath . '/ckfinder/userfiles',
            $targetPublicPath . '/ckfinder/config.php',
            $targetPublicPath . '/ckfinder/README.md',
            $targetConnectorPath . '/README.md'
        ));

        $output->writeln('<info>Done. Happy coding!</info>');
    }
}
