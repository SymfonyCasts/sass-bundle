<?php

namespace Symfonycasts\SassBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\AssetMapper\MappedAsset;

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