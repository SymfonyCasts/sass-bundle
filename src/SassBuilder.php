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
     * @var array<string, bool|string>
     */
    private readonly array $sassOptions;

    /**
     * @see https://sass-lang.com/documentation/cli/dart-sass/
     */
    private const DEFAULT_OPTIONS = [
        'charset' => true,
        'style' => 'expanded',
        'source_map' => true,
        'embed_source_map' => false,
        'embed_sources' => false,
    ];

    /**
     * @param array<string>              $sassPaths
     * @param array<string, bool|string> $sassOptions
     */
    public function __construct(
        private readonly array $sassPaths,
        private readonly string $cssPath,
        private readonly string $projectRootDir,
        private readonly ?string $binaryPath,
        bool|array $sassOptions = [],
    ) {
        if (\is_bool($sassOptions)) {
            // Until 0.3, the $sassOptions argument was a boolean named $embedSourceMap
            trigger_deprecation('symfonycasts/sass-bundle', '0.4', 'Passing a boolean to embed the source map is deprecated. Set \'sass_options.embed_source_map\' instead.');
            $sassOptions = ['embed_source_map' => $sassOptions];
            // ...and source maps were always generated.
            $sassOptions['source_map'] = true;
        }

        $this->sassOptions = $this->configureOptions($sassOptions);
    }

    public function runBuild(bool $watch): Process
    {
        $binary = $this->createBinary();

        $args = $this->getScssCssTargets();
        if ($watch) {
            $args[] = '--watch';
        }
        foreach ($this->sassOptions as $option => $value) {
            $option = str_replace('_', '-', $option);
            if (\is_bool($value)) {
                $args[] = $value ? '--'.$option : '--no-'.$option;
                continue;
            }
            $args[] = '--'.$option.'='.$value;
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
        foreach ($this->sassPaths as $sassPath) {
            if (!is_file($sassPath)) {
                throw new \Exception(sprintf('Could not find Sass file: "%s"', $sassPath));
            }

            $targets[] = $sassPath.':'.$this->guessCssNameFromSassFile($sassPath, $this->cssPath);
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
    public static function guessCssNameFromSassFile(string $sassFile, string $outputDirectory): string
    {
        $fileName = basename($sassFile, '.scss');

        return $outputDirectory.'/'.$fileName.'.output.css';
    }

    private function configureOptions(array $options = []): array
    {
        $validOptions = array_keys(self::DEFAULT_OPTIONS);
        $invalidOptions = array_diff(array_keys($options), $validOptions);
        if (\count($invalidOptions) > 0) {
            throw new \InvalidArgumentException(sprintf('Invalid Sass options: "%s". Valid options are: "%s".', implode('", "', $invalidOptions), implode('", "', $validOptions)));
        }

        return array_diff_assoc($options, self::DEFAULT_OPTIONS);
    }

    private function createBinary(): SassBinary
    {
        return new SassBinary($this->projectRootDir.'/var', $this->binaryPath, $this->output);
    }
}
