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
        unlink(__DIR__.'/fixtures/assets/app.output.css');
        if (file_exists($sourceMap = __DIR__.'/fixtures/assets/app.output.css.map')) {
            unlink(__DIR__.'/fixtures/assets/app.output.css.map');
        }
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

    public function testSassDefaultOptions(): void
    {
        $builder = new SassBuilder(
            [__DIR__.'/fixtures/assets/app.scss'],
            __DIR__.'/fixtures/assets',
            __DIR__.'/fixtures',
            null,
            true,
            [
                'charset' => true,
                // 'embed-source-map' => true,
            ]
        );

        $process = $builder->runBuild(false);
        $process->wait();

        $this->assertTrue($process->isSuccessful());
        $this->assertFileExists(__DIR__.'/fixtures/assets/app.output.css');

        $result = file_get_contents(__DIR__.'/fixtures/assets/app.output.css');

        $this->assertStringContainsString("\nul li {\n", $result);
        $this->assertStringContainsString("color: red;\n", $result);
        $this->assertStringContainsString('@charset', $result);
        $this->assertStringContainsString('sourceMappingURL=', $result);
    }

    public function testSassOptionsProd(): void
    {
        $builder = new SassBuilder(
            [__DIR__.'/fixtures/assets/app.scss'],
            __DIR__.'/fixtures/assets',
            __DIR__.'/fixtures',
            null,
            false,
            [
                'style' => 'compressed',
                'charset' => false,
                'source-map' => false,
            ]
        );

        $process = $builder->runBuild(false);
        $process->wait();

        $this->assertTrue($process->isSuccessful());
        $this->assertFileExists(__DIR__.'/fixtures/assets/app.output.css');

        $result = file_get_contents(__DIR__.'/fixtures/assets/app.output.css');

        $this->assertStringNotContainsString('/** FOO BAR */', $result);
        $this->assertStringContainsString('}ul li{color:red}', $result);
        $this->assertStringNotContainsString('charset=', $result);
        $this->assertStringNotContainsString('sourceMappingURL=', $result);
    }
}
