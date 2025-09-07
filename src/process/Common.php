<?php

namespace AloneWebMan\Bot\process;

use AloneWebMan\Bot\Facade;

class Common {
    protected string $plugin_name = "";

    public function getPluginName(mixed $worker): string {
        $this->plugin_name = Facade::getPluginName($worker);
        return $this->plugin_name;
    }
}