<?php

namespace demo\app;

use AloneWebMan\Bot\Facade;

/**
 * 机器人信息入口
 */
class Bot extends Take {
    public function main(): void {
        $this->res->sendMessage(Facade::json($this->req));
    }
}