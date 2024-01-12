<?php

/*
 * This file is part of the SymfonyCasts SassBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfonycasts\SassBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\AssetMapper\MappedAsset;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfonycasts\SassBundle\SassBinary;

class FunctionalTest extends KernelTestCase
{
    protected function setUp(): void
    {
        file_put_contents(__DIR__.'/fixtures/assets/app.css', <<<EOF
            p {
               color: red;
            }
            EOF
        );

        if (file_exists(__DIR__.'/fixtures/var')) {
            $filesystem = new Filesystem();
            $filesystem->remove(__DIR__.'/fixtures/var');
        }
    }

    protected function tearDown(): void
    {
        unlink(__DIR__.'/fixtures/assets/app.css');
        if (file_exists(__DIR__.'/fixtures/var')) {
            $filesystem = new Filesystem();
            $filesystem->remove(__DIR__.'/fixtures/var');
        }
    }

    public function testBuildCssIfUsed(): void
    {
        self::bootKernel();

        $assetMapper = self::getContainer()->get('asset_mapper');
        \assert($assetMapper instanceof AssetMapperInterface);

        $asset = $assetMapper->getAsset('app.css');
        $this->assertInstanceOf(MappedAsset::class, $asset);

        if (null === $asset->content) {
            // Starting with Symfony 6.4, the asset mapper only store the content during compilation.
            $this->assertStringContainsString('color: red', file_get_contents($asset->sourcePath));
        } else {
            $this->assertStringContainsString('color: red', $asset->content);
        }
    }

    public function testVersionDownloaded(): void
    {
        $testedVersion = '1.69.5'; // This should differ from the latest version which downloaded by default
        $binary = new SassBinary(binaryDownloadDir: __DIR__.'/fixtures/var/version', binaryVersion: $testedVersion);

        $binary->downloadExecutable();
        $this->assertDirectoryExists(__DIR__.'/fixtures/var/version/dart-sass/1.69.5');

        $sassVersionProcess = new Process([__DIR__.'/fixtures/var/version/dart-sass/1.69.5/sass', '--version']);
        $sassVersionProcess->run();
        $this->assertSame(trim($sassVersionProcess->getOutput(), \PHP_EOL), $testedVersion);
    }
}
