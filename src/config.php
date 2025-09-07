<?php
return [
    /*
     * ==========================程序设置==========================
     */
    // 程序域名,带http
    "domain"          => "",
    // 开发目录
    'app_path'        => "plugin/alone/app",
    // 机器人路由/token
    "router_path"     => "telegram/alone/api",
    //是否验证token
    "token_verify"    => false,
    //md5 key
    "md5_key"         => "",

    /*
     * ==========================开发设置==========================
     */
    // 是否开启调试
    "dev_status"      => false,
    // 定时器
    "dev_timer"       => 0.1,
    // 机器人key
    "dev_bot_key"     => "",

    /*
     * ==========================队列设置==========================
     */
    // 队列自定义进程开关
    "queue_status"    => false,
    // 队列进程数量
    "queue_count"     => 30,
    // 队列定时器
    "queue_timer"     => 0.2,
    // 队列任务数
    "queue_task"      => 3,
    // 队列redis,key名称
    "queue_redis_key" => "alone_bot_queue_message",

    /*
     * ==========================异步设置==========================
     */
    // 异步自定义进程开关
    "async_status"    => false,
    // 启动异步ip端口
    "async_listen"    => "0.0.0.0:12722",
    // 连接异步ip端口
    "async_connect"   => "127.0.0.1:12722",
    //异步进程数量
    "async_count"     => 30,

    /*
     * ==========================信息设置==========================
     */
    // 信息分类,是否接收信息
    "message"         => [
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
    ],
    // 信息类型,是否接收信息
    "msg"             => [
        //文本
        'text'      => true,
        //图片
        'photo'     => false,
        //视频
        'video'     => false,
        //动画
        'animation' => false,
        //音频
        'audio'     => false,
        //语音
        'voice'     => false,
        //文档
        'document'  => false
    ]
];