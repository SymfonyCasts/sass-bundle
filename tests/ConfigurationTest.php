<?php

declare(strict_types=1);

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
                    '%kernel.project_dir%/assets/scss/app.scss'
                ]
            ]
        ]);
    }

    public function testMultipleSassRootPaths(): void
    {
        $this->assertConfigurationIsValid([
            'symfonycasts_sass' => [
                'root_sass' => [
                    '%kernel.project_dir%/assets/scss/app.scss',
                    '%kernel.project_dir%/assets/admin/scss/admin.scss'
                ]
            ]
        ]);
    }

    public function testMultipleSassRootPathsWithSameFilename(): void
    {
        $this->assertConfigurationIsInvalid([
            'symfonycasts_sass' => [
                'root_sass' => [
                    '%kernel.project_dir%/assets/scss/app.scss',
                    '%kernel.project_dir%/assets/admin/scss/app.scss'
                ]
            ]
        ],
        'Invalid configuration for path "symfonycasts_sass.root_sass": The root sass-paths need to end with unique filenames.');
    }
}
