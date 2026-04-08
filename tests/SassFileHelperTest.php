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

    public function testResolveSassInputsRelativeGlobDoubleStarUsesBaseDir(): void
    {
        $helper = new SassFileHelper();

        $projectDir = __DIR__.'/fixtures';
        $inputs = $helper->resolveSassInput('assets/**/*.scss', $projectDir);

        $this->assertNotEmpty($inputs, 'Expected at least one match for relative ** glob under fixtures.');
        $this->assertContains(SassFileHelper::normalizePath($projectDir.'/assets/lib/libcss.scss'), $inputs);
    }
}
