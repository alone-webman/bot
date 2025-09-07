<?php

use AloneWebMan\Bot\Facade;

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
    return Facade::command();
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