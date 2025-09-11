<?php

namespace demo\app;

/**
 * 公共类
 */
trait Common {
    // 当前插件名
    public string $plugin = "";
    // 请求token
    public string $token = "";

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