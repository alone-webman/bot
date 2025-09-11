<?php

namespace demo\app;

use AloneWebMan\Bot\BotHelper;

class Common extends BotHelper {
    /**
     * 执行main前公共执行
     * @return bool 返回false不执行main
     */
    public function index(): bool {
        return true;
    }

    /**
     * 单个机器人信息
     * @return mixed
     */
    public function getBotInfo(): mixed {
        return ["bot_ket" => $this->getConfig('dev_bot_key')];
    }

    /**
     * 当前机器人Key
     * @return string
     */
    public function getBotKey(): string {
        return ($this->bot['bot_ket'] ?? $this->getConfig('dev_bot_key'));
    }
}