<?php

declare(strict_types=1);

/*
 * This file is part of the SymfonyCasts SassBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfonycasts\SassBundle\Tests\AssetMapper;

use PHPUnit\Framework\TestCase;
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\AssetMapper\MappedAsset;
use Symfonycasts\SassBundle\AssetMapper\SassCssCompiler;
use Symfonycasts\SassBundle\SassBuilder;

final class SassCssCompilerTest extends TestCase
{
    private const CSS_DIR = __DIR__.'/../fixtures/var/sass';

    protected function setUp(): void
    {
        if (!is_dir(self::CSS_DIR)) {
            mkdir(self::CSS_DIR, 0777, true);
        }
    }

    public function testCompileSingleSassPath(): void
    {
        $scssFile = __DIR__.'/../fixtures/assets/app.scss';
        $scssPaths = [
            $scssFile,
        ];
        $cssFile = self::CSS_DIR.'/app.output.css';

        $compiler = new SassCssCompiler(
            $scssPaths,
            self::CSS_DIR,
            $this->createSassBuilder($scssPaths, self::CSS_DIR)
        );

        $mappedAsset = new MappedAsset(
            'app.scss',
            $scssFile,
            'app.css'
        );

        file_put_contents($cssFile, <<<EOF
            p {
               color: red;
            }
            EOF
        );

        $compiledContent = $compiler->compile(
            file_get_contents($scssFile),
            $mappedAsset,
            $this->createMock(AssetMapperInterface::class)
        );

        $this->assertStringEqualsFile(
            $cssFile,
            $compiledContent
        );
    }

    public function testCompileNamedSassPath()
    {
        $scssFile = __DIR__.'/../fixtures/assets/admin/app.scss';
        $scssPaths = [
            'admin' => $scssFile,
        ];
        $cssFile = self::CSS_DIR.'/admin.output.css';

        $compiler = new SassCssCompiler(
            $scssPaths,
            self::CSS_DIR,
            $this->createSassBuilder($scssPaths, self::CSS_DIR)
        );

        $mappedAsset = new MappedAsset(
            'admin/app.scss',
            $scssFile,
            'admin.css'
        );

        file_put_contents($cssFile, <<<EOF
            p {
               color: blue;
            }
            EOF
        );

        $compiledContent = $compiler->compile(
            file_get_contents($scssFile),
            $mappedAsset,
            $this->createMock(AssetMapperInterface::class)
        );

        $this->assertStringEqualsFile(
            $cssFile,
            $compiledContent
        );
    }

    private function createSassBuilder(array $sassPaths, string $cssPath): SassBuilder
    {
        return new SassBuilder(
            $sassPaths,
            $cssPath,
            __DIR__.'/../fixtures',
            null,
            false
        );
    }
}
