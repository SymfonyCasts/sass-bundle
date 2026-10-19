<?php

/*
 * This file is part of the SymfonyCasts SassBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfonycasts\SassBundle\Tests\AssetMapper;

use PHPUnit\Framework\TestCase;
use Symfony\Component\AssetMapper\Path\PublicAssetsPathResolverInterface;
use Symfonycasts\SassBundle\AssetMapper\SassPublicPathAssetPathResolver;

class SassPublicPathAssetPathResolverTest extends TestCase
{
    /**
     * @dataProvider providePaths
     */
    public function testResolvePublicPath(string $innerPath, string $expectedPath): void
    {
        $resolver = new SassPublicPathAssetPathResolver($this->createInnerResolver($innerPath));

        $this->assertSame($expectedPath, $resolver->resolvePublicPath('styles/app'));
    }

    /**
     * @dataProvider providePaths
     */
    public function testGetPublicFilesystemPath(string $innerPath, string $expectedPath): void
    {
        $resolver = new SassPublicPathAssetPathResolver($this->createInnerResolver($innerPath));

        $this->assertSame($expectedPath, $resolver->getPublicFilesystemPath());
    }

    public static function providePaths(): iterable
    {
        yield 'scss' => ['/assets/styles/app-abc123.scss', '/assets/styles/app-abc123.css'];
        yield 'sass' => ['/assets/styles/app-abc123.sass', '/assets/styles/app-abc123.css'];
        yield 'css is left untouched' => ['/assets/styles/app-abc123.css', '/assets/styles/app-abc123.css'];
        yield 'js is left untouched' => ['/assets/app-abc123.js', '/assets/app-abc123.js'];
    }

    private function createInnerResolver(string $path): PublicAssetsPathResolverInterface
    {
        return new class($path) implements PublicAssetsPathResolverInterface {
            public function __construct(private readonly string $path)
            {
            }

            public function resolvePublicPath(string $logicalPath): string
            {
                return $this->path;
            }

            public function getPublicFilesystemPath(): string
            {
                return $this->path;
            }
        };
    }
}
