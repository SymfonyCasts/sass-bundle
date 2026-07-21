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
    public function testDefaultBinaryPathMatchesTheDartSassLauncherName(): void
    {
        $binary = new SassBinary(__DIR__.'/fixtures/download');

        $method = new \ReflectionMethod($binary, 'getDefaultBinaryPath');
        $path = $method->invoke($binary);

        // dart-sass ships a "sass.bat" launcher on Windows and a "sass" script elsewhere;
        // if the path doesn't match, the binary is never found and gets re-downloaded on every build
        $expectedName = str_contains(strtolower(\PHP_OS), 'win') ? 'sass.bat' : 'sass';

        self::assertSame(__DIR__.'/fixtures/download/dart-sass/'.$expectedName, $path);
    }
}
