<?php

declare(strict_types=1);

/*
 * This file is part of the SymfonyCasts SassBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfonycasts\SassBundle\Tests;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Symfonycasts\SassBundle\DependencyInjection\SymfonycastsSassExtension;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration(): SymfonycastsSassExtension
    {
        return new SymfonycastsSassExtension();
    }

    public function testSingleSassRootPath(): void
    {
        $this->assertConfigurationIsValid([
            'symfonycasts_sass' => [
                'root_sass' => [
                    '%kernel.project_dir%/assets/scss/app.scss',
                ],
            ],
        ]);
    }

    public function testMultipleSassRootPaths(): void
    {
        $this->assertConfigurationIsValid([
            'symfonycasts_sass' => [
                'root_sass' => [
                    '%kernel.project_dir%/assets/scss/app.scss',
                    '%kernel.project_dir%/assets/admin/scss/admin.scss',
                ],
            ],
        ]);
    }

    public function testMultipleSassRootPathsWithSameFilename(): void
    {
        $this->assertConfigurationIsInvalid([
            'symfonycasts_sass' => [
                'root_sass' => [
                    '%kernel.project_dir%/assets/scss/app.scss',
                    '%kernel.project_dir%/assets/admin/scss/app.scss',
                ],
            ],
        ],
            'Invalid configuration for path "symfonycasts_sass.root_sass": The "root_sass" paths need to end with unique filenames.');
    }
}
