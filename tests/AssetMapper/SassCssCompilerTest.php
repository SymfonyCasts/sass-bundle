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

        $compilerAbsolutePath = new SassCssCompiler(
            [__DIR__.'/../fixtures/assets/app.scss'],
            __DIR__.'/../fixtures/var/sass',
            __DIR__.'/../fixtures',
            $builder
        );

        $this->assertTrue($compilerAbsolutePath->supports($asset), 'Supports absolute paths');

        $compilerRelativePath = new SassCssCompiler(
            ['assets/app.scss'],
            __DIR__.'/../fixtures/var/sass',
            __DIR__.'/../fixtures',
            $builder
        );

        $this->assertTrue($compilerRelativePath->supports($asset), 'Supports relative paths');
    }

    public function testSupportsGlob()
    {
        $builder = $this->createMock(SassBuilder::class);

        $asset = new MappedAsset('assets/lib/libcss.scss', __DIR__.'/../fixtures/assets/lib/libcss.scss');

        $compilerAbsolutePath = new SassCssCompiler(
            [__DIR__.'/../fixtures/assets/*.scss', __DIR__.'/../fixtures/assets/**/*.scss'],
            __DIR__.'/../fixtures/var/sass',
            __DIR__.'/../fixtures',
            $builder
        );

        $this->assertTrue($compilerAbsolutePath->supports($asset), 'Supports absolute paths');

        $compilerRelativePath = new SassCssCompiler(
            ['assets/lib/*.scss', 'assets/lib/**/*.scss'],
            __DIR__.'/../fixtures/var/sass',
            __DIR__.'/../fixtures',
            $builder
        );

        $this->assertTrue($compilerRelativePath->supports($asset), 'Supports relative paths');
    }
}
