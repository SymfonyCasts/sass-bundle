<?php

/*
 * This file is part of the SymfonyCasts SassBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfonycasts\SassBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfonycasts\SassBundle\SassBuilder;

#[AsCommand(
    name: 'sass:watch',
    description: 'Watch the Sass assets and compile them on change. Alias to "sass:build --watch"'
)]
class SassWatchCommand extends SassBuildCommand
{
    public function __construct(
        SassBuilder $sassBuilder
    ) {
        parent::__construct($sassBuilder);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $input->setOption('watch', true);

        return parent::execute($input, $output);
    }
}
