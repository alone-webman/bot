<?php

namespace demo\app;

use Exception;
use Throwable;
use AloneWebMan\Bot\BotReq;

class CallBack {
    /**
     * 路由入口
     * @param string|int $plugin
     * @param string     $token
     * @param array      $post
     * @return mixed false=正常执行,返回什么输出什么到浏览器
     */
    public static function route(string|int $plugin, string $token, array $post): mixed {
        return false;
    }

    /**
     * @param string|int $plugin
     * @param string     $token
     * @param array      $post
     * @return void
     */
    public static function process(string|int $plugin, string $token, array $post): void {}

    /**
     * 处理类型
     * @param string|int $plugin
     * @param string     $token
     * @return string|int 1=实时,2=协程,3=队列,4=异步
     */
    public static function type(string|int $plugin, string $token): string|int {
        return 2;
    }

    /**
     * 收到信息
     * @param string|int $plugin
     * @param string     $token
     * @param array      $post
     * @param BotReq     $req
     * @return void
     */
    public static function message(string|int $plugin, string $token, array $post, BotReq $req): void {}

    /**
     * 运行结束
     * @param string|int $plugin
     * @param string     $token
     * @param array      $post
     * @param mixed      $app
     * @return void
     */
    public static function end(string|int $plugin, string $token, array $post, mixed $app) {}

    /**
     * 程序报错
     * @param string|int          $plugin
     * @param string              $token
     * @param array               $post
     * @param Exception|Throwable $error
     * @param array               $array
     * @return void
     */
    public static function error(string|int $plugin, string $token, array $post, Exception|Throwable $error, array $array = []): void {}

}