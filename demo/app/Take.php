<?php

namespace demo\app;

use AlonePhp\Telegram\Bot;
use AloneWebMan\Bot\BotReq;

/**
 * 入口类
 */
class Take {
    use Common;

    // 原样post
    public array $post = [];
    // 当前主体信息
    public array $data = [];
    // 机器人key
    public string $key = "";
    // 机器人信息表单
    public mixed $bot = null;
    // 请求信息
    public BotReq|null $req = null;
    // 当前 update_id
    public string|int $update_id = '';
    // 发送信息 带回复id
    public Bot|null $res = null;
    // 发送信息 alone_bot(key)
    public Bot|null $tel = null;

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
        call_user_func([$this, 'main']);
        return $this;
    }

    /**
     * 单个机器人信息
     * @return mixed
     */
    public function getBotInfo(): mixed {
        return ["bot_ket" => $this->getConfig('dev_bot_key')];
    }

    /**
     * 当前机器人Key
     * @return string
     */
    public function getBotKey(): string {
        return ($this->bot['bot_ket'] ?? $this->getConfig('dev_bot_key'));
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