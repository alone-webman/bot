<?php

namespace AloneWebMan\Bot\command;

use AloneWebMan\Bot\Facade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginCommand extends Command {
    protected static $defaultName        = 'alone:bot-plugin';
    protected static $defaultDescription = 'bot plugin set <info>[plugin name]</info>';

    protected function configure(): void {
        $this->addArgument('name', InputArgument::OPTIONAL, 'name', "");
    }


    public function execute(InputInterface $input, OutputInterface $output): int {
        $name = $input->getArgument('name');
        echo "--------------------------------------------------------\r\n";
        print_r($name);
        echo "\r\n--------------------------------------------------------\r\n";
        return self::SUCCESS;
    }

}