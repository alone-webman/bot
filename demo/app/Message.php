<?php

namespace demo\app;

use Exception;
use Throwable;
use AloneWebMan\Bot\BotReq;
use AloneWebMan\Bot\Facade;

/**
 * 信息接收类
 */
class Message {
    use Common;

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
     * 机器人列表 (命令使用)
     * @return array
     */
    public function command(): array {
        return [
            1 => [
                // 名称
                "name"    => "测试机器人",
                // key
                "key"     => $this->getConfig("dev_bot_key"),
                // 按钮名称
                "button"  => "Open",
                // 命令列表
                "command" => [
                    'start' => "开始启动"
                ],
                // 接收信息类型
                "updates" => [
                    //普通消息
                    'message'              => true,
                    //回调查询（来自按钮点击）
                    'callback_query'       => true,
                    //匿名投票,接收投票详细
                    'poll'                 => false,
                    //实名投票 那个用户投了那个票
                    'poll_answer'          => false,
                    //频道消息
                    'channel_post'         => false,
                    //编辑过的普通消息
                    'edited_message'       => false,
                    //编辑过的频道消息
                    'edited_channel_post'  => false,
                    //内联查询
                    'inline_query'         => false,
                    //选择的内联结果
                    'chosen_inline_result' => false,
                    //运输查询（用于购物）
                    'shipping_query'       => false,
                    //预检查查询（用于购物）
                    'pre_checkout_query'   => false
                ]
            ]
        ];
    }
}