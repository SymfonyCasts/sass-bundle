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
use Symfonycasts\SassBundle\SassBuilder;

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

    public function testBuildCssFromIndentedSyntax(): void
    {
        self::bootKernel();

        $builder = self::getContainer()->get('sass.builder');
        \assert($builder instanceof SassBuilder);
        $process = $builder->runBuild(false);
        $process->wait();
        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());

        $assetMapper = self::getContainer()->get('asset_mapper');
        \assert($assetMapper instanceof AssetMapperInterface);

        $asset = $assetMapper->getAsset('indented_syntax.sass');
        $this->assertInstanceOf(MappedAsset::class, $asset);
        $this->assertStringEndsWith('.css', $asset->publicPath);
        $this->assertStringContainsString('.indented{color:blue}', $asset->content);
    }
}
