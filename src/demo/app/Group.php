<?php

namespace AloneWebMan\Bot\demo\app;

use AloneWebMan\Bot\Facade;

/**
 * 群组信息入口
 */
class Group extends Common {
    public function main(): void {
        $this->res->sendMessage(Facade::json($this->req));
    }
}