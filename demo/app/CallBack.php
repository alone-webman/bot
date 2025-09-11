<?php

namespace demo\app;

use Exception;
use Throwable;
use AloneWebMan\Bot\BotReq;

class CallBack {
    // 当前插件名
    public string $plugin = "";
    // 请求token
    public string $token = "";

    /**
     * 路由入口
     * @param array $post bot请求信息
     * @return mixed false=正常执行,返回什么输出什么到浏览器
     */
    public function route(array $post): mixed {
        return false;
    }

    /**
     * 进程入口
     * @param array $array 进程获取到的bot信息
     * @return void
     */
    public function process(array $array): void {
        dump($array);
    }

    /**
     * 处理类型
     * @return string|int 1=实时,2=协程,3=队列,4=异步
     */
    public function type(): string|int {
        return 2;
    }

    /**
     * 正在处理信息
     * @param array  $post
     * @param BotReq $req
     * @return void
     */
    public function message(array $post, BotReq $req): void {}

    /**
     * 运行结束
     * @param array $post
     * @param mixed $app
     * @return void
     */
    public function end(array $post, mixed $app) {}

    /**
     * 程序报错
     * @param array               $post
     * @param Exception|Throwable $error
     * @param array               $array
     * @return void
     */
    public function error(array $post, Exception|Throwable $error, array $array = []): void {
        dump($array);
    }

    /**
     * @param string $plugin 插件名
     * @param string $token  路由token
     */
    public function __construct(string $plugin, string $token) {
        $this->plugin = $plugin;
        $this->token = $token;
    }
}