<?php

/*
 * This file is part of the SymfonyCasts SassBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfonycasts\SassBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfonycasts\SassBundle\SassBuilder;

class SassBuilderTest extends TestCase
{
    protected function tearDown(): void
    {
        @unlink(__DIR__.'/fixtures/assets/app.output.css');
        @unlink(__DIR__.'/fixtures/assets/app.output.css.map');

        @unlink(__DIR__.'/fixtures/assets/foo.output.css');
        @unlink(__DIR__.'/fixtures/assets/foo.output.css.map');

        @unlink(__DIR__.'/fixtures/assets/bar.output.css');
        @unlink(__DIR__.'/fixtures/assets/bar.output.css.map');
    }

    public function testIntegration(): void
    {
        $builder = new SassBuilder(
            [__DIR__.'/fixtures/assets/app.scss'],
            __DIR__.'/fixtures/assets',
            __DIR__.'/fixtures',
            null,
            false
        );

        $process = $builder->runBuild(false);
        $process->wait();

        $this->assertTrue($process->isSuccessful());
        $this->assertFileExists(__DIR__.'/fixtures/assets/app.output.css');
        $this->assertStringContainsString('color: red;', file_get_contents(__DIR__.'/fixtures/assets/app.output.css'));
    }

    public function testPathsConfigWithKeys(): void
    {
        $builder = new SassBuilder(
            [
                'foo' => __DIR__.'/fixtures/assets/app.scss',
                'bar' => __DIR__.'/fixtures/assets/admin/app.scss',
            ],
            __DIR__.'/fixtures/assets',
            __DIR__.'/fixtures',
            null,
            false
        );

        $process = $builder->runBuild(false);
        $process->wait();

        $this->assertTrue($process->isSuccessful());
        $this->assertFileExists(__DIR__.'/fixtures/assets/foo.output.css');
        $this->assertFileExists(__DIR__.'/fixtures/assets/bar.output.css');
        $this->assertStringContainsString('color: red;', file_get_contents(__DIR__.'/fixtures/assets/foo.output.css'));
        $this->assertStringContainsString('color: blue;', file_get_contents(__DIR__.'/fixtures/assets/bar.output.css'));
    }
}
