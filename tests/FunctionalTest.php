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
        $this->assertStringContainsString('color: red', $asset->content);
    }
}
