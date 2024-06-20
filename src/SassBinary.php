<?php

/*
 * This file is part of the SymfonyCasts SassBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfonycasts\SassBundle;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Process\Process;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SassBinary
{
    private HttpClientInterface $httpClient;
    private ?string $cachedVersion = null;

    public function __construct(
        private string $binaryDownloadDir,
        private ?string $binaryPath = null,
        private ?string $binaryVersion = null,
        private ?SymfonyStyle $output = null,
        ?HttpClientInterface $httpClient = null
    ) {
        $this->httpClient = $httpClient ?? HttpClient::create();
    }

    /**
     * @param array<string> $args
     */
    public function createProcess(array $args): Process
    {
        if (null === $this->binaryPath) {
            $binary = $this->getDefaultBinaryPath($this->getVersion());
            if (!is_file($binary)) {
                $this->downloadExecutable();
            }
        } else {
            $binary = $this->binaryPath;
        }

        array_unshift($args, $binary);

        return new Process($args);
    }

    public function downloadExecutable(): void
    {
        $url = sprintf('https://github.com/sass/dart-sass/releases/download/%s/%s', $this->getVersion(), $this->getBinaryName());
        $isZip = str_ends_with($url, '.zip');

        $this->output?->note('Downloading Sass binary from '.$url);

        if (!is_dir($this->binaryDownloadDir)) {
            mkdir($this->binaryDownloadDir, 0777, true);
        }

        $targetPath = $this->binaryDownloadDir.'/'.self::getBinaryName();
        $progressBar = null;

        $response = $this->httpClient->request('GET', $url, [
            'on_progress' => function (int $dlNow, int $dlSize, array $info) use (&$progressBar): void {
                if (0 === $dlSize) {
                    return;
                }

                if (!$progressBar) {
                    $progressBar = $this->output?->createProgressBar($dlSize);
                }

                $progressBar?->setProgress($dlNow);
            },
        ]);

        if (404 === $response->getStatusCode()) {
            if ($this->getLatestVersion() !== $this->getVersion()) {
                throw new \Exception(sprintf('Cannot download Sass binary. Please verify version `%s` exists for your machine.', $this->getVersion()));
            }
            throw new \Exception(sprintf('Cannot download Sass binary. Response code: %d', $response->getStatusCode()));
        }

        $fileHandler = fopen($targetPath, 'w');
        foreach ($this->httpClient->stream($response) as $chunk) {
            fwrite($fileHandler, $chunk->getContent());
        }

        fclose($fileHandler);
        $progressBar?->finish();
        $this->output?->writeln('');

        if ($isZip) {
            if (!\extension_loaded('zip')) {
                throw new \Exception('Cannot unzip the downloaded Sass binary. Please install the "zip" PHP extension.');
            }
            $archive = new \ZipArchive();
            $archive->open($targetPath);
            $archive->extractTo($this->binaryDownloadDir.'/dart-sass');
            $archive->close();
            unlink($targetPath);

            return;
        } else {
            $archive = new \PharData($targetPath);
            $archive->decompress();
            $archive->extractTo($this->binaryDownloadDir.'/dart-sass');

            // delete the .tar (the .tar.gz is deleted below)
            unlink(substr($targetPath, 0, -3));
        }

        unlink($targetPath);

        // Rename the extracted directory to its version
        rename($this->binaryDownloadDir.'/dart-sass/dart-sass', $this->binaryDownloadDir.'/dart-sass/'.$this->getVersion());

        $binaryPath = $this->getDefaultBinaryPath($this->getVersion());
        if (!is_file($binaryPath)) {
            throw new \Exception(sprintf('Could not find downloaded binary in "%s".', $binaryPath));
        }

        chmod($binaryPath, 0777);
    }

    public function getBinaryName(): string
    {
        $os = strtolower(\PHP_OS);
        $machine = strtolower(php_uname('m'));

        if (str_contains($os, 'darwin')) {
            if ('arm64' === $machine) {
                return $this->buildBinaryFileName('macos-arm64');
            }

            if ('x86_64' === $machine) {
                return $this->buildBinaryFileName('macos-x64');
            }

            throw new \Exception(sprintf('No matching machine found for Darwin platform (Machine: %s).', $machine));
        }

        if (str_contains($os, 'linux')) {
            $baseName = file_exists('/etc/alpine-release') ? 'linux-musl' : 'linux';
            if ('arm64' === $machine || 'aarch64' === $machine) {
                return $this->buildBinaryFileName($baseName.'-arm64');
            }
            if ('x86_64' === $machine) {
                return $this->buildBinaryFileName($baseName.'-x64');
            }

            throw new \Exception(sprintf('No matching machine found for Linux platform (Machine: %s).', $machine));
        }

        if (str_contains($os, 'win')) {
            if ('x86_64' === $machine || 'amd64' === $machine || 'i586' === $machine) {
                return $this->buildBinaryFileName('windows-x64', true);
            }

            throw new \Exception(sprintf('No matching machine found for Windows platform (Machine: %s).', $machine));
        }

        throw new \Exception(sprintf('Unknown platform or architecture (OS: %s, Machine: %s).', $os, $machine));
    }

    private function buildBinaryFileName(string $os, bool $isWindows = false): string
    {
        return 'dart-sass-'.$this->getVersion().'-'.$os.($isWindows ? '.zip' : '.tar.gz');
    }

    private function getDefaultBinaryPath(string $version): string
    {
        return $this->binaryDownloadDir.'/dart-sass/'.$version.'/sass';
    }

    private function getVersion(): string
    {
        return $this->cachedVersion ??= $this->binaryVersion ?? $this->getLatestVersion();
    }

    private function getLatestVersion(): string
    {
        try {
            $response = $this->httpClient->request('GET', 'https://api.github.com/repos/sass/dart-sass/releases/latest');

            return $response->toArray()['tag_name'] ?? throw new \Exception('Cannot get the latest version name from response JSON.');
        } catch (\Throwable $e) {
            throw new \Exception('Cannot determine latest Dart Sass CLI binary version. Please specify a version in the configuration.', previous: $e);
        }
    }
}
