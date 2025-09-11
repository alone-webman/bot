<?php

namespace demo\app;

use AloneWebMan\Bot\Facade;

/**
 * 群组信息入口
 */
class Group extends Take {
    public function main(): void {
        $this->res->sendMessage(Facade::json($this->req));
    }
}