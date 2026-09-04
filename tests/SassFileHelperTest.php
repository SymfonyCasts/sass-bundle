<?php

/*
 * This file is part of the SymfonyCasts SassBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfonycasts\SassBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfonycasts\SassBundle\SassFileHelper;

class SassFileHelperTest extends TestCase
{
    public function testResolveSassInputsRelativeGlobUsesBaseDir(): void
    {
        $helper = new SassFileHelper();

        $projectDir = __DIR__.'/fixtures';
        $inputs = $helper->resolveSassInput('assets/lib/*.scss', $projectDir);

        $this->assertNotEmpty($inputs, 'Expected at least one match for relative glob under fixtures.');
        $this->assertContains(SassFileHelper::normalizePath($projectDir.'/assets/lib/libcss.scss'), $inputs);
    }

    /**
     * @dataProvider provideSassPaths
     */
    public function testIsSassFileAndStripSassExtension(string $path, bool $isSass, string $stripped): void
    {
        $this->assertSame($isSass, SassFileHelper::isSassFile($path));
        $this->assertSame($stripped, SassFileHelper::stripSassExtension($path));
    }

    public static function provideSassPaths(): iterable
    {
        yield 'scss' => ['assets/styles/app.scss', true, 'assets/styles/app'];
        yield 'sass' => ['assets/styles/app.sass', true, 'assets/styles/app'];
        yield 'css' => ['assets/styles/app.css', false, 'assets/styles/app.css'];
        yield 'no extension' => ['assets/styles/app', false, 'assets/styles/app'];
        // the extension has to be at the end, not merely present in the path
        yield 'directory named like the extension' => ['assets/.scss/app.css', false, 'assets/.scss/app.css'];
    }

    public function testResolveSassInputsRelativeGlobDoubleStarUsesBaseDir(): void
    {
        $helper = new SassFileHelper();

        $projectDir = __DIR__.'/fixtures';
        $inputs = $helper->resolveSassInput('assets/**/*.scss', $projectDir);

        $this->assertNotEmpty($inputs, 'Expected at least one match for relative ** glob under fixtures.');
        $this->assertContains(SassFileHelper::normalizePath($projectDir.'/assets/lib/libcss.scss'), $inputs);
    }
}
