<?php

namespace AloneWebMan\Bot\command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BotSetCommand extends Command {
    protected static $defaultName        = 'alone:bot-set';
    protected static $defaultDescription = 'bot set <info>[plugin name]</info>';

    protected function configure(): void {
        $this->addArgument('name', InputArgument::OPTIONAL, "name");
    }


    public function execute(InputInterface $input, OutputInterface $output): int {
        $name = $input->getArgument("name");
        echo "--------------------------------------------------------\r\n";
        print_r("输入名称:$name,开发中");
        echo "\r\n--------------------------------------------------------\r\n";
        return self::SUCCESS;
    }
}