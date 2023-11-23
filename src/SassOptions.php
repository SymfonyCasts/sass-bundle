<?php

/*
 * This file is part of the SymfonyCasts SassBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfonycasts\SassBundle;

/**
 * --no-charset
 * --style
 * --no-source-map
 * --embed-source-map.
 *
 * @see https://sass-lang.com/documentation/cli/dart-sass/
 */
final class SassOptions
{
    private array $options;

    private array $defaultOptions = [
        'style' => 'expanded',
        'charset' => true,
        'source-map' => true,
        'embed-source-map' => null,
        'embed-sources' => null,
    ];

    public function __construct(array $options = [])
    {
        // TODO better checks... or drop that class ?
        $this->options = [...$this->defaultOptions, ...array_intersect_key($options, $this->defaultOptions)];
    }

    /**
     * Select the style of the generated CSS (default: expanded).
     *
     * @see https://sass-lang.com/documentation/cli/dart-sass/#style
     */
    public function style(string $value): static
    {
        if (!\in_array($value, ['expanded', 'compressed'])) {
            throw new \InvalidArgumentException(sprintf('Invalid value "%s" for option "style".', $value));
        }

        $this->options['style'] = $value;

        return $this;
    }

    /**
     * Writes the entire CSS file on a single line (remove spaces & comments).
     *
     * @see style()
     */
    public function compressed(): static
    {
        return $this->style('compressed');
    }

    /**
     * Writes each selector and declaration on its own line (default).
     *
     * @see style()
     */
    public function expanded(): static
    {
        return $this->style('expanded');
    }

    /**
     * Write a @charset declaration if necessary (only in expanded style) (default: true).
     */
    public function charset(bool $value = true): static
    {
        $this->options['charset'] = false;

        return $this;
    }

    /**
     * Generate a source map file (default: true).
     *
     * @see embedSourceMaps()
     * @see embedSources()
     */
    public function sourceMaps(bool $value = true): static
    {
        $this->options['source-map'] = $value;

        return $this;
    }

    /**
     * Embed the content of the source map file in the generated CSS (default: false).
     */
    public function embedSourceMap(bool $value = true): static
    {
        $this->options['embed-source-map'] = $value ?: null;

        return $this;
    }

    /**
     * Embed the content of the Sass sources in the source map (default: false).
     */
    public function embedSources(bool $value = true): static
    {
        $this->options['embed-sources'] = $value ?: null;

        return $this;
    }

    public function toArray(): array
    {
        $options = [];
        foreach ($this->options as $key => $value) {
            if (null === $value) {
                continue;
            }
            // --no-charset
            if (\is_bool($value)) {
                $options[] = '--'.($value ? $key : 'no-'.$key);
                continue;
            }
            // --style=expanded
            $options[] = sprintf('--%s=%s', $key, $value);
        }

        return $options;
    }
}
