<?php

namespace AloneWebMan\Bot\command;

use AloneWebMan\Bot\Facade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BotCommand extends Command {
    protected static $defaultName        = 'alone:bot-plugin';
    protected static $defaultDescription = 'create bot plugin <info>[plugin name]</info>';

    protected function configure(): void {
        $this->addArgument('name', InputArgument::OPTIONAL, 'name');
    }


    public function execute(InputInterface $input, OutputInterface $output): int {
        $name = $input->getArgument('name');
        echo "--------------------------------------------------------\r\n";
        print_r(static::createBot($name));
        echo "\r\n--------------------------------------------------------\r\n";
        return self::SUCCESS;
    }

    /**
     * @param string|int|null $name
     * @return array|string
     */
    public static function createBot(string|int|null $name): array|string {
        if (empty($name)) {
            return "Plugin name cannot be empty - php webman alone:bot-plugin [plugin name]";
        }
        $pluginPath = base_path("plugin/$name");
        if (is_dir($pluginPath)) {
            return "$name The plugin already exists";
        }
        $list = [];
        $configPath = base_path("plugin/$name/config");
        Facade::mkDir($configPath);
        $configList = ["app.php", "process.php", "route.php", "telegram.php"];
        $port = rand(1, 3) . rand(2, 9) . rand(001, 999);
        $key = md5($name . time() . $port);
        foreach ($configList as $file) {
            $files = __DIR__ . '/../../demo/config/' . $file;
            $body = @file_get_contents($files);
            $body = Facade::tag($body, [
                "key"  => $key,
                "name" => $name,
                "port" => $port
            ]);
            $saveName = $configPath . "/" . $file;
            if (!empty(@file_put_contents($saveName, $body))) {
                $list["success"][] = "plugin/$name/config/$file";
            } else {
                $list["error"][] = "plugin/$name/config/$file";
            }
        }
        $appPath = base_path("plugin/$name/app");
        Facade::mkDir($appPath);
        $appList = ["Bot.php", "Channel.php", "Common.php", "Group.php"];
        foreach ($appList as $file) {
            $files = __DIR__ . '/../../demo/app/' . $file;
            $body = @file_get_contents($files);
            $body = str_replace("demo\app;", "plugin\\" . $name . "\app;", $body);
            $saveName = $appPath . "/" . $file;
            if (!empty(@file_put_contents($saveName, $body))) {
                $list["success"][] = "plugin/$name/app/$file";
            } else {
                $list["error"][] = "plugin/$name/app/$file";
            }
        }
        return $list;
    }
}