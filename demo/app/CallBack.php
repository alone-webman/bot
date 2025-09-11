<?php

namespace demo\app;

use Exception;
use Throwable;
use AloneWebMan\Bot\BotReq;
use AloneWebMan\Bot\Facade;

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
     * @param array $result
     * @return void
     */
    public function process(array $array, array $result): void {
        // $result && dump($result);
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
    public function exec(array $post, BotReq $req): void {
        //  dump($post);
    }

    /**
     * 运行结束
     * @param mixed $app
     * @return void
     */
    public function end(mixed $app) {
        // dump($app);
    }

    /**
     * 程序报错
     * @param array               $post
     * @param Exception|Throwable $error
     * @param array               $array
     * @return void
     */
    public function error(array $post, Exception|Throwable $error, array $array = []): void {
        $content = Facade::json($array);
        $config = alone_bot_config($this->plugin);
        alone_bot($config['dev_bot_key'])->chat_id($config["dev_chat_id"])->sendMessage($content);
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