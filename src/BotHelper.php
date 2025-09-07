<?php

namespace AloneWebMan\Bot;

use Exception;
use Throwable;
use AlonePhp\Telegram\Bot;

/**
 * 抽象类
 */
abstract class BotHelper {
    /*
     * 当前插件名
     */
    public string $plugin = "";
    /*
     * 原样post
     */
    public array $post = [];
    /*
     * 当前主体信息
     */
    public array $data = [];
    /*
     * 请求token
     */
    public string $token = "";
    /*
     * 机器人key
     */
    public string $key = "";
    /*
     * 机器人信息表单
     */
    public mixed $bot = null;
    /*
     * 请求信息
     */
    public BotReq|null $req = null;
    /*
     * 当前 update_id
     */
    public string|int $update_id = '';
    /*
     * 发送信息 带回复id
     */
    public Bot|null $res = null;
    /*
     * 发送信息 alone_bot(key)
     */
    public Bot|null $tel = null;

    /**
     * 程序报错回调
     * @param Exception|Throwable $error
     * @param array               $array
     * @return void
     */
    abstract public function error(Exception|Throwable $error, array $array = []): void;

    /**
     * 信息处理类型
     * @param string $token
     * @return string|int 1=实时,2=协程,3=队列,4=异步
     */
    abstract public function getSendType(string $token): string|int;

    /**
     * 单个机器人信息
     * @return mixed
     */
    abstract public function getBotInfo(): mixed;

    /**
     * 当前机器人Key
     * @return string
     */
    abstract public function getBotKey(): string;

    /**
     * 网页接收信息时验证头部信息
     * @param string $token
     * @param string $secret
     * @return bool true=验证通过
     */
    public function verifyRoute(string $token, string $secret): bool {
        if ($this->getConfig('token_verify')) {
            if (($secret && $secret == get_bot_header_token($token, $this->getConfig('md5_key')))) {
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * 处理信息入口
     * @param mixed $req
     * @return $this
     */
    public function handle(mixed $req): static {
        $this->req = $req;
        $this->post = $this->req->post ?? [];
        $this->data = $this->req->data ?? [];
        $this->update_id = $this->req->update_id ?? '';
        $this->bot = $this->getBotInfo();
        $this->key = $this->getBotKey();
        $this->tel = alone_bot($this->key);
        $this->res = $this->chat();
        if (method_exists($this, 'index')) {
            $index = call_user_func([$this, 'index']);
            if (empty($index)) {
                return $this;
            }
        }
        if (method_exists($this, 'main')) {
            call_user_func([$this, 'main']);
        }
        return $this;
    }

    /**
     * @param string $plugin
     * @param string $token
     */
    public function __construct(string $plugin, string $token) {
        $this->plugin = $plugin;
        $this->token = $token;
    }

    /**
     * 调试时发送
     * @return Bot
     */
    public function sendDev(): Bot {
        return alone_bot($this->getConfig('dev_bot_key', ''))->chat_id(explode(",", $this->getConfig('dev_chat_id', '')));
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
     * 聊天带id
     * @return Bot
     */
    public function chat(): Bot {
        $this->res = alone_bot($this->key)->chat_id($this->req->chat_id)->query_id($this->req->query_id)->message_id($this->req->msg_id);
        return $this->res;
    }

    /**
     * @param string|int $content
     * @param bool       $alert
     * @return Bot
     */
    public function callbackText(string|int $content, bool $alert = false): Bot {
        return $this->tel->query_id($this->req->query_id)->callbackText($content, $alert);
    }
}