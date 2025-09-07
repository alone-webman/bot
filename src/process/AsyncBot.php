<?php

namespace AloneWebMan\Bot\process;

use AloneWebMan\Bot\Facade;
use Workerman\Connection\TcpConnection;

/**
 * 接收机器人信息(异步)
 */
class AsyncBot {
    public function onMessage(TcpConnection $connection, mixed $data): void {
        if (!empty($arr = Facade::isJson($data))) {
            if (!empty($plugin = $arr['plugin'] ?? '') && !empty($token = $arr['token'] ?? '') && !empty($post = $arr['post'] ?? [])) {
                Facade::exec($plugin, $token, $post);
            }
        }
    }
}