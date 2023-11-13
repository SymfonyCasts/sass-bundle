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

        if (str_contains($path, '.scss')) {
            return str_replace('.scss', '.css', $path);
        }

        return $path;
    }

    public function getPublicFilesystemPath(): string
    {
        trigger_deprecation('symfony/asset-mapper', '6.4', 'Calling "%s()" is deprecated, use "resolvePublicPath()" instead.', __METHOD__);

        $path = $this->decorator->getPublicFilesystemPath();

        if (str_contains($path, '.scss')) {
            return str_replace('.scss', '.css', $path);
        }

        return $path;
    }
}
