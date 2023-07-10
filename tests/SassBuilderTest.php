<?php

namespace Symfonycasts\SassBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfonycasts\SassBundle\SassBuilder;

class SassBuilderTest extends TestCase
{
    protected function tearDown(): void
    {
        unlink(__DIR__.'/fixtures/assets/app.output.css');
        unlink(__DIR__.'/fixtures/assets/app.output.css.map');
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
}