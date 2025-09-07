<?php

namespace AloneWebMan\Bot;

/**
 * 请求信息处理
 */
class BotReq {
    /*
     * 原POST
     */
    public array $post = [];
    /*
     * 当前信息
     */
    public array $data = [];
    /*
     * 当前 update_id
     */
    public string|int $update_id = '';
    /*
     * 新入员入群
     */
    public bool $group_new = false;
    /*
     * 信息来源是否机器人
     */
    public bool $is_bot = false;
    /*
     * 聊天类型 group=群,bot=机器人,channel=频道
     */
    public string $chat_type = "";
    /*
     * 来源名称,如群名
     */
    public string $chat_title = "";
    /*
     * 接收信息类型,message=普通消息
     */
    public string $message_type = "";
    /*
     * 信息类型,text=文本
     */
    public string $msg_type = "text";
    /*
     * 当前命令,不为空为命令名称
     */
    public string|int $command = "";
    /*
     * 当前命令参数
     */
    public string|int $command_param = "";
    /*
     * 用户ID  唯一标识
     */
    public string|int $user_id = "";
    /*
     * 用户帐号
     */
    public string|int $user_name = "";
    /*
     * 用户姓
     */
    public string|int $first_name = "";
    /*
     * 用户名
     */
    public string|int $last_name = "";
    /*
     * 用户姓名
     */
    public string|int $full_name = "";
    /*
     * 飞机语言
     */
    public string $language = "";
    /*
     * 当前回复ID(机器人=用户ID,群ID,频道ID)
     */
    public string|int $chat_id = "";
    /*
     * 当前信息ID
     */
    public string|int|array $msg_id = "";
    /*
     * 收到文本信息
     */
    public string|int $text = "";
    /*
     * 回调id
     */
    public string|int $query_id = "";
    /*
     * 回调游戏名称
     */
    public string|int $query_game = "";
    /*
     * 回调data
     */
    public string|int $query_data = "";
    /*
     * 是否处理信息
     * @var bool
     */
    public bool $allow = false;
    /*
     * 配置
     */
    public array $config = [];
    /*
     * 接收信息类型列表
     */
    public static array $messageTypeList = [
        'message'              => '普通消息',
        'edited_message'       => '编辑过的普通消息',
        'callback_query'       => '回调查询',
        'poll'                 => '匿名投票',
        'poll_answer'          => '实名投票',
        'channel_post'         => '频道消息',
        'edited_channel_post'  => '编辑过的频道消息',
        'inline_query'         => '内联查询',
        'chosen_inline_result' => '选择的内联结果',
        'shipping_query'       => '运输查询',
        'pre_checkout_query'   => '预检查查询'
    ];
    /*
     * 信息类型列表
     */
    public static array $msgTypeList = [
        'photo'     => '图片',
        'video'     => '视频',
        'animation' => '动画',
        'audio'     => '音频',
        'voice'     => '语音',
        'document'  => '文档',
        'text'      => '文本'
    ];

    /**
     * 信息处理
     * @param array $post 原请求POST
     * @param array $config
     * @return static
     */
    public static function handle(array $post, array $config): static {
        $static = new static();
        $static->config = $config;
        $static->post = $post;
        $static->process();
        return $static;
    }

    /**
     * @return void
     */
    protected function process(): void {
        // 更新id
        $this->update_id = $this->post['update_id'] ?? '';
        // 配置开关
        $message = $this->config['message'] ?? [];
        // 处理信息分类
        foreach (static::$messageTypeList as $k => $v) {
            // 是否存在
            if (isset($this->post[$k])) {
                //不存在或者开启了
                $this->allow = !isset($message[$k]) || $message[$k];
                if ($this->allow) {
                    // 保存类型
                    $this->message_type = $k;
                    // 当前信息
                    $this->data = $this->post[$k];
                    // 执行处理
                    call_user_func([$this, $this->message_type]);
                }
                break;
            }
        }
    }

    /**
     * 普通消息
     * @return void
     */
    protected function message(): void {
        // 文体信息
        $this->text = $this->data['text'] ?? '';
        // 信息id
        $this->msg_id = $this->data['message_id'] ?? '';
        // 聊天类型
        $this->chat_type = $this->data['chat']['type'] ?? '';
        // 来源名称,如群名
        $this->chat_title = $this->data['chat']['title'] ?? 'bot';
        // 回复id|群id|频道id
        $this->chat_id = $this->data['chat']['id'] ?? '';
        // 语言
        $this->language = $this->data['from']['language_code'] ?? '';
        // 群是否新成员
        $new_chat = $this->data['new_chat_participant'] ?? [];
        // 聊天类型变换
        $this->chatType();
        // 新成员入群
        if ($this->chat_type == 'group' && !empty($new_chat['id'] ?? '')) {
            // 是否成员入群
            $this->group_new = true;
            $this->getFrom($new_chat);
        } else {
            $this->group_new = false;
            $this->getFrom($this->data['from'] ?? []);
            // 配置开关
            $msg = $this->config['message'] ?? [];
            foreach (static::$msgTypeList as $k => $v) {
                if (isset($this->data[$k])) {
                    $this->allow = !isset($msg[$k]) || $msg[$k];
                    if ($this->allow) {
                        $this->msg_type = $k;
                        $this->text = $this->data['caption'] ?? $this->text;
                    }
                    break;
                }
            }
        }
        if (!empty($this->allow)) {
            // 全名
            $this->fullName();
            // 命令
            if (str_starts_with($this->text, '/')) {
                $this->command = substr($this->text, 1);
                $spacePos = strpos($this->command, ' ');
                if ($spacePos !== false) {
                    $this->command_param = trim(substr($this->command, $spacePos + 1));
                    $this->command = trim(substr($this->command, 0, $spacePos));
                }
            }
        }
    }

    /**
     * 回调查询
     * @return void
     */
    protected function callback_query(): void {
        // 回调data
        $this->query_data = $this->data['data'] ?? '';
        // 回调游戏名称
        $this->query_game = $this->data['game_short_name'] ?? '';
        // 回调id
        $this->query_id = $this->data['id'] ?? '';
        // 用户ID
        $this->user_id = $this->data['from']['id'] ?? '';
        // 信息ID
        $this->msg_id = $this->data['message']['message_id'] ?? '';
        // 聊天类型
        $this->chat_type = $this->data['message']['chat']['type'] ?? '';
        // 当前回复ID
        $this->chat_id = $this->data['message']['chat']['id'] ?? '';
        // 信息来源是否机器人
        $this->is_bot = $this->data['from']['is_bot'] ?? '';
        // 用户帐号
        $this->user_name = $this->data['from']['username'] ?? '';
        // 姓
        $this->first_name = $this->data['from']['first_name'] ?? '';
        // 名
        $this->last_name = $this->data['from']['last_name'] ?? '';
        // 语言
        $this->language = $this->data['from']['language_code'] ?? '';
        // 来源名称,如群名
        $this->chat_title = $this->data['chat']['title'] ?? '';
        // 聊天类型变换
        $this->chatType();
    }

    /**
     * 编辑过的普通消息
     * @return void
     */
    protected function edited_message(): void {
        $this->message();
    }

    /**
     * 匿名投票
     * @return void
     */
    protected function poll(): void {}

    /**
     * 实名投票
     * @return void
     */
    protected function poll_answer(): void {}

    /**
     * 频道消息
     * @return void
     */
    protected function channel_post(): void {}

    /**
     * 编辑过的频道消息
     * @return void
     */
    protected function edited_channel_post(): void {}

    /**
     * 内联查询
     * @return void
     */
    protected function inline_query(): void {}

    /**
     * 选择的内联结果
     * @return void
     */
    protected function chosen_inline_result(): void {}

    /**
     * 运输查询
     * @return void
     */
    protected function shipping_query(): void {}

    /**
     * 预检查查询
     * @return void
     */
    protected function pre_checkout_query(): void {}

    /**
     * 类型转换
     * @return void
     */
    protected function chatType(): void {
        switch ($this->chat_type) {
            case 'supergroup':
                $this->chat_type = 'group';
                break;
            case 'private':
                $this->chat_type = 'bot';
                break;
        }
    }

    /**
     * 全名
     * @return void
     */
    protected function fullName(): void {
        if (!empty($this->first_name ?? '') && empty($this->last_name ?? '')) {
            $this->full_name = $this->first_name;
        } elseif (empty($this->first_name ?? '') && !empty($this->last_name ?? '')) {
            $this->full_name = $this->last_name;
        } elseif (!empty($this->first_name ?? '') && !empty($this->last_name ?? '')) {
            $this->full_name = $this->first_name . ' ' . $this->last_name;
        }
    }

    /**
     * 获取资料
     * @param array $data
     * @return void
     */
    protected function getFrom(array $data): void {
        // 用户id
        $this->user_id = $data['id'] ?? $this->user_id;
        // 是否机器人
        $this->is_bot = $data['is_bot'] ?? $this->is_bot;
        // 帐号
        $this->user_name = $data['username'] ?? $this->user_name;
        // 姓
        $this->first_name = $data['first_name'] ?? $this->first_name;
        //名
        $this->last_name = $data['last_name'] ?? $this->last_name;
    }
}