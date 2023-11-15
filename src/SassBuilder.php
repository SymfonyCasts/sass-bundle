<?php

/*
 * This file is part of the SymfonyCasts SassBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfonycasts\SassBundle;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

class SassBuilder
{
    private ?SymfonyStyle $output = null;

    /**
     * @param array<string> $sassPaths
     */
    public function __construct(
        private readonly array $sassPaths,
        private readonly string $cssPath,
        private readonly string $projectRootDir,
        private readonly ?string $binaryPath,
        private readonly bool $embedSourcemap,
    ) {
    }

    public function runBuild(bool $watch): Process
    {
        $binary = $this->createBinary();

        $args = $this->getScssCssTargets();

        if ($watch) {
            $args[] = '--watch';
        }

        if ($this->embedSourcemap) {
            $args[] = '--embed-source-map';
        }

        $process = $binary->createProcess($args);

        if ($watch) {
            $process->setTimeout(null);

            $inputStream = new InputStream();
            $process->setInput($inputStream);
        }

        $this->output?->note('Executing Sass (pass -v to see more details).');
        if ($this->output?->isVerbose()) {
            $this->output->writeln([
                '  Command:',
                '    '.$process->getCommandLine(),
            ]);
        }

        $process->start();

        return $process;
    }

    /**
     * @return array<string>
     */
    public function getScssCssTargets(): array
    {
        $targets = [];
        foreach ($this->sassPaths as $fileName => $sassPath) {
            if (!is_file($sassPath)) {
                throw new \Exception(sprintf('Could not find Sass file: "%s"', $sassPath));
            }

            $targets[] = $sassPath.':'.self::guessCssNameFromSassFile($sassPath, $this->cssPath, $fileName);
        }

        return $targets;
    }

    public function setOutput(SymfonyStyle $output): void
    {
        $this->output = $output;
    }

    /**
     * @internal
     */
    public static function guessCssNameFromSassFile(string $sassFile, string $outputDirectory, string|int $fileName = null): string
    {
        if (null === $fileName || \is_int($fileName)) {
            $fileName = basename($sassFile, '.scss');
        }

        return $outputDirectory.'/'.$fileName.'.output.css';
    }

    public function getIdentifierByLogicalPath(string $path): ?string
    {
        if (array_is_list($this->sassPaths)) {
            return null;
        }

        foreach ($this->sassPaths as $identifier => $configuredSassPath) {
            // as the configured paths include the project dir, we need to subtract it to be able to compare the paths
            $logicalPath = str_replace($this->projectRootDir.'/assets/', '', $configuredSassPath);

            if ($path === $logicalPath) {
                return $identifier;
            }
        }

        return null;
    }

    private function createBinary(): SassBinary
    {
        return new SassBinary($this->projectRootDir.'/var', $this->binaryPath, $this->output);
    }
}
