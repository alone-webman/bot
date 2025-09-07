<?php

namespace demo\app;

use Exception;
use Throwable;
use AloneWebMan\Bot\Facade;
use AloneWebMan\Bot\BotHelper;

class Common extends BotHelper {

    /**
     * 程序报错回调
     * @param Exception|Throwable $error
     * @param array               $array
     * @return void
     */
    public function error(Exception|Throwable $error, array $array = []): void {
        $bot = alone_bot($this->getConfig('dev_bot_key', ''));
        // $bot->chat_id("")->sendMessage(Facade::json($array));
    }

    /**
     * 信息处理类型
     * @param string $token
     * @return string|int 1=实时,2=协程,3=队列,4=异步
     */
    public function getSendType(string $token): string|int {
        return 2;
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

    /**
     * 执行main前公共执行
     * @return bool 返回false不执行main
     */
    public function index(): bool {
        return true;
    }
}