<?php

namespace AloneWebMan\Bot;

use support\Redis;
use Workerman\Coroutine;
use Workerman\Connection\AsyncTcpConnection;

class BotMsg {
    // 接收信息类型
    public static array $updates = [
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
    ];

    /**
     * 机器人启动
     * @param array $config
     * @return void
     */
    public static function botStart(array $config): void {
        // 配置
        $config = array_merge([
            // 插件名称
            "plugin"  => "",
            // 机器人标识
            "token"   => "",
            // 任务类型 1=实时,2=协程,3=队列,4=异步
            "task"    => "",
            // 异步连接
            "link"    => "",
            // 定时器
            "timer"   => 0.1,
            // 机器人token key
            "key"     => "",
            // 信息数量
            "limit"   => 100,
            // update_id 保存方式 (file|redis)
            "cache"   => "",
            // update_id 文件绝对路径
            "file"    => "",
            // update_id redis key
            "redis"   => "",
            //接收信息类型
            "updates" => []
        ], $config);
        BotMsg::BotTimer($config["key"], function($array) use ($config) {
            switch ((int) $config["task"]) {
                case 2:
                    // 协程
                    Coroutine::create(function() use ($config, $array) {
                        foreach ($array as $post) {
                            Facade::exec($config["plugin"], $config["token"], $post);
                        }
                    });
                    break;
                case 3:
                    // 队列
                    foreach ($array as $post) {
                        Facade::queue($config["plugin"], $config["token"], $post);
                    }
                    break;
                case 4:
                    // 异步
                    $async = new AsyncTcpConnection($config["link"]);
                    $async->onConnect = function(AsyncTcpConnection $connection) use ($config, $array) {
                        foreach ($array as $post) {
                            $connection->send(json_encode(['plugin' => $config["plugin"], 'token' => $config["token"], 'post' => $post]));
                        }
                        $connection->close();
                    };
                    $async->connect();
                    break;
                default:
                    // 实时
                    foreach ($array as $post) {
                        Facade::exec($config["plugin"], $config["token"], $post);
                    }
                    break;
            }
        }, $config);
    }

    /**
     * 定时器获取机器人信息
     * @param string   $key      机器人key
     * @param callable $callback 回调数据
     * @param array    $config   配置信息
     * @return void
     */
    public static function BotTimer(string $key, callable $callback, array $config = []): void {
        // 接收信息类型
        $updates = [];
        $config['updates'] = array_merge(static::$updates, $config['updates'] ?? []);
        foreach ($config['updates'] as $k => $v) {
            if (is_numeric($k)) {
                $updates[] = $v;
            } elseif ($v) {
                $updates[] = $k;
            }
        }
        // 配置
        $config = array_merge([
            // 定时器
            "timer"   => 0.1,
            // 机器人标识
            "token"   => "",
            // 机器人token key
            "key"     => $key,
            // 信息数量
            "limit"   => 100,
            // update_id 保存方式 (file|redis)
            "cache"   => "",
            // update_id 文件绝对路径
            "file"    => "",
            // update_id redis key
            "redis"   => "",
            //接收信息类型
            "updates" => []
        ], $config);
        $config['token'] = !empty($config['token'] ?? '') ? $config['token'] : md5($config['key']);
        switch ($config['cache']) {
            case "redis":
                $redisKey = !empty($key = ($config["redis"] ?? "")) ? $key : "alone_bot_update_id_" . $config['token'];
                $get_update_id = function() use ($redisKey) {
                    $update_id = Redis::get($redisKey);
                    return !empty($update_id) ? $update_id : 0;
                };
                $set_update_id = function($update_id) use ($redisKey) {
                    Redis::set($redisKey, $update_id);
                };
                break;
            default:
                $file = !empty($file = ($config["file"] ?? "")) ? $file : run_path("runtime/alone_bot_update_id_" . $config['token'] . ".cache");
                Facade::mkDir(dirname($file));
                $get_update_id = function() use ($file) {
                    $update_id = @file_get_contents($file);
                    return !empty($update_id) ? $update_id : 0;
                };
                $set_update_id = function($update_id) use ($file) {
                    @file_put_contents($file, $update_id);
                };
                break;
        }
        $bot = alone_bot($config['key']);
        Facade::timer((float) $config['timer'], function() use ($callback, $config, $updates, $get_update_id, $set_update_id, $bot) {
            $bot->getUpdates($get_update_id(), $config['limit'], 0, $updates);
            $array = $bot->array();
            if (!empty($array)) {
                $ok = ($array['ok'] ?? '');
                if (!empty($ok)) {
                    $result = $array['result'] ?? [];
                    if (!empty($result)) {
                        $update_ids = array_column($result, 'update_id');
                        $update_id = (int) (max($update_ids) ?: 0);
                        $set_update_id($update_id + 1);
                        $callback($result);
                    }
                }
            }
        });
    }
}