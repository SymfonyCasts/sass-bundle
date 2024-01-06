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

    public function testSingleSassRootPathAsString(): void
    {
        $configuration = [
            'symfonycasts_sass' => [
                'root_sass' => '%kernel.project_dir%/assets/scss/app.scss',
            ],
        ];
        $this->assertConfigurationIsValid($configuration);
        $this->assertProcessedConfigurationEquals($configuration, [
            'root_sass' => [
                '%kernel.project_dir%/assets/scss/app.scss',
            ],
        ], 'root_sass');
    }

    public function testSassOptionsEmpty(): void
    {
        $this->assertConfigurationIsValid([
            'symfonycasts_sass' => [
                'sass_options' => [
                ],
            ],
        ]);
    }

    public function testSassOptionsAreSet(): void
    {
        $this->assertConfigurationIsValid([
            'symfonycasts_sass' => [
                'sass_options' => [
                    'style' => 'compressed',
                    'charset' => true,
                    'source_map' => true,
                    'embed_sources' => true,
                    'embed_source_map' => true,
                    'error_css' => true,
                    'quiet' => true,
                    'quiet_deps' => true,
                    'stop_on_error' => true,
                    'trace' => true,
                ],
            ],
        ]);
        $this->assertConfigurationIsValid([
            'symfonycasts_sass' => [
                'sass_options' => [
                    'style' => 'compressed',
                    'charset' => false,
                    'source_map' => false,
                    'embed_sources' => false,
                    'embed_source_map' => false,
                    'error_css' => false,
                    'quiet' => false,
                    'quiet_deps' => false,
                    'stop_on_error' => false,
                    'trace' => false,
                ],
            ],
        ]);
    }

    public function testSassOptionsAreNullable(): void
    {
        $this->assertConfigurationIsValid([
            'symfonycasts_sass' => [
                'sass_options' => [
                    'style' => 'compressed',
                    'charset' => null,
                    'source_map' => null,
                    'embed_sources' => null,
                    'embed_source_map' => null,
                    'error_css' => null,
                    'quiet' => null,
                    'quiet_deps' => null,
                    'stop_on_error' => null,
                    'trace' => null,
                ],
            ],
        ]);
    }

    public function testSassOptionsWithInvalidStyle(): void
    {
        $this->assertConfigurationIsInvalid([
            'symfonycasts_sass' => [
                'sass_options' => [
                    'style' => 'not-a-valid-style',
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

    protected function getConfiguration(): SymfonycastsSassExtension
    {
        return new SymfonycastsSassExtension();
    }
}
