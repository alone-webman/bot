<?php

namespace AloneWebMan\Bot\process;

use AloneWebMan\Bot\Facade;
use AloneWebMan\Bot\BotMsg;

/**
 * 开发调试,拉取信息扔到异步处理
 */
class BotDev extends Common {
    public function onWorkerStart(mixed $worker): void {
        $this->getPluginName($worker);
        $config = Facade::config($this->plugin_name);
        $token = get_bot_route_token($config['dev_bot_key'], $config['md5_key']);
        BotMsg::botStart([
            // 插件名称
            "plugin"  => $this->plugin_name,
            // 机器人标识
            "token"   => $token,
            // 任务类型 1=实时,2=协程,3=队列,4=异步
            "task"    => ($config['dev_type'] ?? 2) ?: 2,
            // 异步连接
            "link"    => "frame://" . $config['async_connect'],
            // 定时器
            "timer"   => $config['dev_timer'],
            // 机器人token key
            "key"     => $config['dev_bot_key'],
            // 信息数量
            "limit"   => 100,
            // update_id 保存方式 (file|redis)
            "cache"   => "file",
            // update_id 文件绝对路径
            "file"    => run_path("runtime/" . $this->plugin_name . "_update_id_" . $token . ".cache"),
            // update_id redis key
            "redis"   => $this->plugin_name . "_update_id_" . $token,
            // 中间件
            "mid"     => function($array, $result) use ($token) {
                Facade::fun($this->plugin_name, $token, "CallBack", "process", $array, $result);
            },
            //接收信息类型
            "updates" => BotMsg::$updates
        ]);
    }
}