<?php

namespace AloneWebMan\Bot\demo\app;

use AloneWebMan\Bot\Facade;

/**
 * 频道信息入口
 */
class Channel extends Common {
    public function main(): void {
        $this->res->sendMessage(Facade::json($this->req));
    }
}