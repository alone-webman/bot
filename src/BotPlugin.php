<?php

namespace AloneWebMan\Bot;

use AlonePhp\Telegram\Bot;

class BotPlugin {
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
     * 设置机器人网址
     * @param string $plugin   插件名
     * @param string $botToken 机器人token key
     * @param array  $conf
     * @return Bot
     */
    public static function setBotWeb(string $plugin, string $botToken, array $conf = []): Bot {
        $config = alone_bot_config($plugin);
        // 配置
        $conf['conf'] = array_merge(["drop_pending_updates" => true], $conf['conf'] ?? []);
        $conf = array_merge([
            // 域名
            "domain" => $config['domain'] ?? '',
            // 路径
            "path"   => $config['router_path'] ?? '',
            // 路由token
            "token"  => get_bot_route_token($botToken, $config['md5_key']),
            //false不设置,true=默认设置
            "secret" => false
        ], $conf);
        $type = array_merge(BotMsg::$updates, $conf["type"] ?? []);
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