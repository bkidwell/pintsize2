<?php

namespace Pintsize\CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Hello extends Command
{
    protected function configure()
    {
        $this
            ->setName('app:hello')
            ->setDescription('Hello world')
            ->setHelp('Hello world')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeLn('Hello world.');
    }
}
