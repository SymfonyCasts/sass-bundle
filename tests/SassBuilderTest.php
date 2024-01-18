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
        if (file_exists($outputCss = __DIR__.'/fixtures/assets/app.output.css')) {
            unlink($outputCss);
            if (file_exists($sourceMap = __DIR__.'/fixtures/assets/app.output.css.map')) {
                unlink($sourceMap);
            }
        }
        if (is_dir($distDir = __DIR__.'/fixtures/assets/dist')) {
            foreach (scandir($distDir) as $file) {
                if (str_ends_with($file, '.css') || str_ends_with($file, '.css.map')) {
                    unlink($distDir.'/'.$file);
                }
            }
        }
    }

    public function testIntegration(): void
    {
        $builder = new SassBuilder(
            [__DIR__.'/fixtures/assets/app.scss'],
            __DIR__.'/fixtures/assets',
            __DIR__.'/fixtures',
            null,
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

    public function testEmbedSources(): void
    {
        $builder = new SassBuilder(
            [__DIR__.'/fixtures/assets/app.scss'],
            __DIR__.'/fixtures/assets',
            __DIR__.'/fixtures',
            null,
            [
                'embed_sources' => true,
                'embed_source_map' => true,
            ]
        );

        $process = $builder->runBuild(false);
        $process->wait();

        $this->assertTrue($process->isSuccessful(), $process->getOutput());
        $this->assertFileExists(__DIR__.'/fixtures/assets/app.output.css');

        $result = file_get_contents(__DIR__.'/fixtures/assets/app.output.css');

        $this->assertStringContainsString('sourceMappingURL=data:application/json', $result);
        $this->assertStringContainsString('color: red', $result);
        $this->assertStringContainsString('color:%20$color;', $result);
    }

    public function testSassOptions(): void
    {
        $builder = new SassBuilder(
            [__DIR__.'/fixtures/assets/app.scss'],
            __DIR__.'/fixtures/assets',
            __DIR__.'/fixtures',
            null,
            [
                'style' => 'compressed',
                'source_map' => false,
            ]
        );

        $process = $builder->runBuild(false);
        $process->wait();

        $this->assertTrue($process->isSuccessful(), $process->getExitCodeText());
        $this->assertFileExists(__DIR__.'/fixtures/assets/app.output.css');

        $result = file_get_contents(__DIR__.'/fixtures/assets/app.output.css');

        $this->assertStringNotContainsString('/** FOO BAR */', $result);
        $this->assertStringContainsString('}ul li{color:red}', $result);
        $this->assertStringNotContainsString('sourceMappingURL=', $result);
    }

    /**
     * @dataProvider provideSassPhpOptions
     */
    public function testSassBuilderConvertPhpOptions(array $phpOptions, array $expectedCliOptions): void
    {
        $builder = new SassBuilder(
            [__DIR__.'/fixtures/assets/app.scss'],
            __DIR__.'/fixtures/assets',
            __DIR__.'/fixtures',
            null,
            $phpOptions,
        );

        $this->assertSame($expectedCliOptions, $builder->getBuildOptions());
    }

    public function testSassOptionStopOnError(): void
    {
        $process = $this->createBuilder(['file_error_foo.scss', 'file_error_bar.scss'])->runBuild(false);
        $process->wait();
        $this->assertFalse($process->isSuccessful());
        $this->assertStringContainsString('error_foo', $process->getErrorOutput());
        $this->assertStringContainsString('error_bar', $process->getErrorOutput());

        $process = $this->createBuilder(['file_error_foo.scss', 'file_error_bar.scss'], ['stop_on_error' => true])->runBuild(false);
        $process->wait();
        $this->assertFalse($process->isSuccessful());
        $this->assertStringContainsString('error_foo', $process->getErrorOutput());
        $this->markTestSkipped('Sass binary does not stop on error - might be related to recent changes in the async handling');
        $this->assertStringNotContainsString('error_bar', $process->getErrorOutput());
    }

    public function testSassOptionQuiet(): void
    {
        $process = $this->createBuilder('file_with_warning.scss', ['quiet' => true])->runBuild(false);
        $process->wait();
        $this->assertTrue($process->isSuccessful());
        $this->assertStringNotContainsString('WARNING', $process->getErrorOutput());

        $process = $this->createBuilder('file_with_warning.scss', ['quiet' => false])->runBuild(false);
        $process->wait();
        $this->assertTrue($process->isSuccessful());
        $this->assertStringContainsString('WARNING', $process->getErrorOutput());
    }

    private function createBuilder(array|string $sassFiles, array $options = []): SassBuilder
    {
        return new SassBuilder(
            array_map(fn (string $file): string => __DIR__.'/fixtures/assets/'.$file, (array) $sassFiles),
            __DIR__.'/fixtures/assets/dist',
            __DIR__.'/fixtures',
            null,
            $options,
        );
    }

    public static function provideSassPhpOptions()
    {
        yield 'No Option set' => [
            [],
            [
                '--style=expanded',
                '--source-map',
            ],
        ];
        yield 'All Options = NULL' => [
            [
                'style' => null,
                'charset' => null,
                'error_css' => null,
                'source_map' => null,
                'embed_sources' => null,
                'embed_source_map' => null,
                'quiet' => null,
                'quiet_deps' => null,
                'stop_on_error' => null,
                'trace' => null,
            ],
            [],
        ];
        yield 'Negatable Options = TRUE' => [
            [
                'style' => 'compressed',
                'charset' => true,
                'error_css' => true,
                'source_map' => true,
                'embed_sources' => true,
                'embed_source_map' => true,
                'quiet' => true,
                'quiet_deps' => true,
                'stop_on_error' => true,
                'trace' => true,
            ],
            [
                '--style=compressed',
                '--charset',
                '--error-css',
                '--source-map',
                '--embed-sources',
                '--embed-source-map',
                '--quiet',
                '--quiet-deps',
                '--stop-on-error',
                '--trace',
            ],
        ];
        yield 'Negatable Options = FALSE' => [
            [
                'style' => 'expanded',
                'charset' => false,
                'error_css' => false,
                'source_map' => false,
                'embed_sources' => false,
                'embed_source_map' => false,
                'quiet' => false,
                'quiet_deps' => false,
                'stop_on_error' => false,
                'trace' => false,
            ],
            [
                '--style=expanded',
                '--no-charset',
                '--no-error-css',
                '--no-source-map',
                '--no-quiet',
                '--no-quiet-deps',
                '--no-stop-on-error',
                '--no-trace',
            ],
        ];
    }
}
