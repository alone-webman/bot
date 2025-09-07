<?php

namespace AloneWebMan\Bot;

use AlonePhp\Telegram\Bot;
use AloneWebMan\Bot\command\BotCommand;
use AloneWebMan\Bot\command\PluginCommand;

class BotPlugin {
    /**
     * 命令
     * @return array
     */
    public static function command(): array {
        return [
            BotCommand::class,
            PluginCommand::class
        ];
    }

    /**
     * 机器人key转换成路由token
     * @param string $botToken 机器人Token
     * @param string $md5Key   md5key
     * @return string
     */
    public static function getBotRouteToken(string $botToken, string $md5Key): string {
        return md5($botToken . $md5Key);
    }


    /**
     * 路由token转换成头部token
     * @param string $routeToken 路由token
     * @param string $md5Key     md5key
     * @return string
     */
    public static function getBotHeaderToken(string $routeToken, string $md5Key): string {
        return md5($md5Key . md5($routeToken . $md5Key));
    }


    /**
     * 设置网址
     * @param string $plugin   插件名
     * @param string $botToken 机器人token key
     * @param array  $conf
     * @return Bot
     */
    public static function setBotWeb(string $plugin, string $botToken, array $conf = []): Bot {
        $config = alone_bot_config($plugin);
        $conf = array_merge([
            // 域名
            "domain" => $config['domain'] ?? '',
            // 路径
            "path"   => $config['router_path'] ?? '',
            // 路由token
            "token"  => get_bot_route_token($botToken, $config['md5_key']),
            //false不设置,true=默认设置
            "secret" => false,
            // 配置
            "conf"   => []
        ], $conf);
        $type = array_merge([
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
        ], $conf["type"] ?? []);
        $pull_type = [];
        foreach ($type as $key => $value) {
            if (is_numeric($key)) {
                $pull_type[] = $value;
            } elseif ($value === true) {
                $pull_type[] = $key;
            }
        }
        $url = trim($conf['domain'], '/') . "/" . trim($conf['path'], '/') . "/" . $conf['token'];
        $secret_token = $conf['secret'] === true ? static::getBotHeaderToken($conf['token'], $config['md5_key']) : "";
        return alone_bot($botToken)->setWebhook($url, $pull_type, $secret_token, $conf["conf"]);
    }
}