<?php

declare(strict_types=1);

/*
 * This file is part of the SymfonyCasts SassBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfonycasts\SassBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfonycasts\SassBundle\SassBinary;

class VersionTest extends TestCase
{
    protected function tearDown(): void
    {
        if (file_exists(__DIR__.'/fixtures/var')) {
            $filesystem = new Filesystem();
            $filesystem->remove(__DIR__.'/fixtures/var');
        }
    }

    public function testVersionDownloaded(): void
    {
        $testedVersion = '1.69.5'; // This should differ from the SassBinary::DEFAULT_VERSION constant
        $binary = new SassBinary(__DIR__.'/fixtures/var/version', null, $testedVersion);

        $binary->downloadExecutable();
        $sassVersionProcess = new Process([__DIR__.'/fixtures/var/version/dart-sass/sass', '--version']);
        $sassVersionProcess->run();

        $this->assertSame(trim($sassVersionProcess->getOutput(), \PHP_EOL), $testedVersion);
    }
}
