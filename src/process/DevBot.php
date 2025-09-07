<?php

namespace AloneWebMan\Bot\process;

use Workerman\Coroutine;
use AloneWebMan\Bot\Facade;
use Workerman\Connection\AsyncTcpConnection;

/**
 * 开发调试,拉取信息扔到异步处理
 */
class DevBot extends Common {
    public function onWorkerStart(mixed $worker): void {
        $this->getPluginName($worker);
        $config = Facade::config($this->plugin_name);
        $bot_key = $config['dev_bot_key'];
        $token = get_bot_route_token($bot_key, $config['md5_key']);
        $connect = $config['async_connect'];
        $message = $config['message'] ?? [];
        $updates = [];
        foreach ($message as $k => $v) {
            if ($v) {
                $updates[] = $k;
            }
        }
        $plugin_arr = Facade::getPluginArr($worker);
        $eventLoop = $plugin_arr['config']['eventLoop'] ?? '';
        $async_status = $config['async_status'];
        $file = run_path("runtime/" . $this->plugin_name . "_update_id_" . $token . ".cache");
        Facade::timer($config['dev_timer'], function() use ($config, $eventLoop, $token, $bot_key, $async_status, $connect, $file, $updates) {
            if ($token && $bot_key) {
                $bot = alone_bot($bot_key);
                $update_id = @file_get_contents($file);
                $bot->getUpdates(($update_id ?: 0), 100, 0, $updates);
                if (!empty($array = $bot->array())) {
                    if (!empty(($array['ok'] ?? '')) && !empty($result = $array['result'] ?? [])) {
                        $update_ids = array_column($result, 'update_id');
                        $update_id = (max($update_ids) ?: 0);
                        @file_put_contents($file, $update_id + 1);
                        if ($eventLoop) {
                            Coroutine::create(function() use ($result, $token) {
                                foreach ($result as $post) {
                                    Facade::exec($this->plugin_name, $token, $post);
                                }
                            });
                        } elseif ($async_status && $connect) {
                            $async = new AsyncTcpConnection("frame://" . $connect);
                            $async->onConnect = function(AsyncTcpConnection $connection) use ($result, $token) {
                                foreach ($result as $post) {
                                    $connection->send(json_encode(['plugin' => $this->plugin_name, 'token' => $token, 'post' => $post]));
                                }
                                $connection->close();
                            };
                            $async->connect();
                        } elseif ($config['queue_status']) {
                            foreach ($result as $post) {
                                Facade::queue($this->plugin_name, $token, $post);
                            }
                        } else {
                            foreach ($result as $post) {
                                Facade::exec($this->plugin_name, $token, $post);
                            }
                        }
                    }
                }
            }
        });
    }
}