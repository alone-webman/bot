<?php

namespace demo\app;
/**
 * 命令执行
 */
class Command {
    // 当前插件名
    public string $plugin = "";
    // 请求token
    public string $token = "";

    /**
     * 机器人列表
     * @return array
     */
    public function bot(): array {
        return [
            [
                "id"      => 1,
                "name"    => "测试机器人",
                "domain"  => $this->getConfig("domain"),
                "key"     => $this->getConfig("dev_bot_key"),
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

    /**
     * 命令列表
     * @return array
     */
    public function command(): array {
        return [
            'start' => "开始启动"
        ];
    }

    /**
     * 获取配置
     * @param string|null $key
     * @param mixed       $default
     * @return mixed
     */
    public function getConfig(string|null $key = null, mixed $default = null): mixed {
        $config = alone_bot_config($this->plugin);
        return isset($key) ? ($config[$key] ?? $default) : $config;
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