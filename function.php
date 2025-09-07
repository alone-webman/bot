<?php

use AloneWebMan\Bot\Facade;
use AloneWebMan\Bot\BotPlugin;

/**
 * 启动路由
 * @param string $plugin 插件名
 * @return void
 */
function alone_bot_route(string $plugin): void {
    Facade::route($plugin);
}

/**
 * 自定义进程
 * @param string $plugin 插件名
 * @return array
 */
function alone_bot_process(string $plugin): array {
    return Facade::process($plugin);
}

/**
 * 命令
 * @return array
 */
function alone_bot_command(): array {
    return BotPlugin::command();
}

/**
 * 实时信息
 * @param string $plugin 插件名
 * @param string $token
 * @param array  $post
 * @return void
 */
function alone_bot_exec(string $plugin, string $token, array $post): void {
    Facade::exec($plugin, $token, $post);
}

/**
 * 获取配置
 * @param string $plugin 插件名
 * @return array
 */
function alone_bot_config(string $plugin): array {
    return Facade::config($plugin);
}


/**
 * 机器人key转换成路由token
 * @param string $botToken 机器人Token
 * @param string $md5Key   md5key
 * @return string
 */
function get_bot_route_token(string $botToken, string $md5Key): string {
    return BotPlugin::getBotRouteToken($botToken, $md5Key);
}


/**
 * 路由token转换成头部token
 * @param string $routeToken 路由token
 * @param string $md5Key     md5key
 * @return string
 */
function get_bot_header_token(string $routeToken, string $md5Key): string {
    return BotPlugin::getBotHeaderToken($routeToken, $md5Key);
}