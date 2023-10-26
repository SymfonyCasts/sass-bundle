<?php

/*
 * This file is part of the SymfonyCasts SassBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfonycasts\SassBundle\AssetMapper;

use Symfony\Component\AssetMapper\Path\PublicAssetsPathResolverInterface;

class SassPublicPathAssetPathResolver implements PublicAssetsPathResolverInterface
{
    public function __construct(private readonly PublicAssetsPathResolverInterface $decorator)
    {
    }

    public function resolvePublicPath(string $logicalPath): string
    {
        $path = $this->decorator->resolvePublicPath($logicalPath);

        if (str_ends_with($path, '.scss')) {
            return substr($path, 0, -5).'.css';
        }

        return $path;
    }

    public function getPublicFilesystemPath(): string
    {
        $path = $this->decorator->getPublicFilesystemPath();

        if (str_ends_with($path, '.scss')) {
            return substr($path, 0, -5).'.css';
        }

        return $path;
    }
}
