<?php

/*
 * This file is part of the SymfonyCasts SassBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfonycasts\SassBundle\Tests\AssetMapper;

use PHPUnit\Framework\TestCase;
use Symfony\Component\AssetMapper\MappedAsset;
use Symfonycasts\SassBundle\AssetMapper\SassCssCompiler;
use Symfonycasts\SassBundle\SassBuilder;

class SassCssCompilerTest extends TestCase
{
    public function testSupports()
    {
        $builder = $this->createMock(SassBuilder::class);

        $asset = new MappedAsset('assets/app.scss', __DIR__.'/../fixtures/assets/app.scss');

        $compiler = new SassCssCompiler(
            [realpath(__DIR__.'/../fixtures/assets/app.scss')],
            realpath(__DIR__.'/../fixtures/var/sass'),
            $builder
        );

        $this->assertTrue($compiler->supports($asset));
    }
}
