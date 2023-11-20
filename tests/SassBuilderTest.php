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
    private array $outputFiles = [];

    protected function tearDown(): void
    {
        while ($path = array_shift($this->outputFiles)) {
            unlink($path);
            unlink(sprintf('%s.map', $path));
        }
    }

    public function testIntegration(): void
    {
        $builder = new SassBuilder(
            [__DIR__.'/fixtures/assets/app.scss'],
            __DIR__.'/fixtures/assets',
            __DIR__.'/fixtures',
            null,
            false,
            []
        );

        $process = $builder->runBuild(false);
        $process->wait();

        $this->assertTrue($process->isSuccessful());
        $this->assertFileExists(__DIR__.'/fixtures/assets/app.output.css');
        $this->outputFiles[] = __DIR__.'/fixtures/assets/app.output.css';
        $this->assertStringContainsString('color: red;', file_get_contents(__DIR__.'/fixtures/assets/app.output.css'));
    }

    public function testIntegrationWithLoadPaths(): void
    {
        $builder = new SassBuilder(
            [__DIR__.'/fixtures/assets/app_using_external.scss'],
            __DIR__.'/fixtures/assets',
            __DIR__.'/fixtures',
            null,
            false,
            [__DIR__.'/fixtures/external']
        );

        $process = $builder->runBuild(false);
        $process->wait();

        $this->assertTrue($process->isSuccessful());
        $this->assertFileExists(__DIR__.'/fixtures/assets/app_using_external.output.css');
        $this->outputFiles[] = __DIR__.'/fixtures/assets/app_using_external.output.css';
        $this->assertStringContainsString('color: red;', file_get_contents(__DIR__.'/fixtures/assets/app_using_external.output.css'));
    }
}
