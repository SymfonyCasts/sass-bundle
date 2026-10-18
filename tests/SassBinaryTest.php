<?php

/*
 * This file is part of the SymfonyCasts SassBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfonycasts\SassBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfonycasts\SassBundle\SassBinary;

class SassBinaryTest extends TestCase
{
    /**
     * @dataProvider provideOsFamilies
     */
    public function testDefaultBinaryPathMatchesTheDartSassLauncherName(string $osFamily, string $expectedName): void
    {
        $binary = new SassBinary(__DIR__.'/fixtures/download');

        $method = new \ReflectionMethod($binary, 'getDefaultBinaryPath');
        $path = $method->invoke($binary, $osFamily);

        // if the path doesn't match the launcher shipped by dart-sass,
        // the binary is never found and gets re-downloaded on every build
        self::assertSame(__DIR__.'/fixtures/download/dart-sass/'.$expectedName, $path);
    }

    public static function provideOsFamilies(): iterable
    {
        yield 'Windows' => ['Windows', 'sass.bat'];
        // "Darwin" contains "win": it must not be mistaken for Windows
        yield 'macOS' => ['Darwin', 'sass'];
        yield 'Linux' => ['Linux', 'sass'];
        yield 'BSD' => ['BSD', 'sass'];
    }

    public function testDefaultBinaryPathUsesTheCurrentOsFamilyByDefault(): void
    {
        $binary = new SassBinary(__DIR__.'/fixtures/download');

        $method = new \ReflectionMethod($binary, 'getDefaultBinaryPath');

        self::assertSame($method->invoke($binary, \PHP_OS_FAMILY), $method->invoke($binary));
    }
}
