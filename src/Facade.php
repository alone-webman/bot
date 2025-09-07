<?php

namespace AloneWebMan\Bot;

use Exception;
use Throwable;
use Webman\Route;
use support\Request;
use Workerman\Timer;
use ReflectionFunction;
use Workerman\Coroutine;
use Workerman\Events\Fiber;
use AloneWebMan\Bot\process\DevBot;
use AloneWebMan\Bot\process\AsyncBot;
use AloneWebMan\Bot\process\RedisQueue;
use AloneWebMan\Bot\command\BotCommand;
use AloneWebMan\Bot\command\PluginCommand;
use Workerman\Connection\AsyncTcpConnection;

class Facade {
    /*
     * 配置
     */
    public static array $config = [];

    /**
     * 启动路由
     * @param string $plugin 插件名
     * @return void
     */
    public static function route(string $plugin): void {
        $config = static::config($plugin);
        $router = trim(str_replace('\\', '/', $config['router_path']), '/');
        if (!empty($router)) {
            Route::post('/' . $router . '[/{token}]', function(Request $req, mixed $token = '') use ($plugin) {
                if (empty($token)) {
                    return response("error1");
                }
                $post = $req->post();
                if (empty($post)) {
                    return response("error2");
                }
                if (empty(static::call($plugin, $token, "Common", "verifyRoute", $token, request()->header('x-telegram-bot-api-secret-token', "")))) {
                    return response("error3");
                }
                static::run($plugin, $token, $post);
                return response("success");
            })->name('plugin.' . $plugin . '.telegram');
        }
    }

    /**
     * 自定义进程
     * @param string $plugin 插件名
     * @return array
     */
    public static function process(string $plugin): array {
        $process = [];
        $config = static::config($plugin);
        if ($config['async_status'] && $config['async_listen']) {
            $process['AsyncBot'] = [
                "name"      => $plugin,
                'eventLoop' => Fiber::class,
                "handler"   => AsyncBot::class,
                'listen'    => 'frame://' . $config['async_listen'],
                'count'     => $config['async_count']
            ];
        }
        if ($config['dev_status']) {
            $process['DevBot'] = [
                "name"      => $plugin,
                'eventLoop' => Fiber::class,
                'handler'   => DevBot::class
            ];
        }
        if ($config['queue_status']) {
            $process['RedisQueue'] = [
                "name"      => $plugin,
                'eventLoop' => Fiber::class,
                'handler'   => RedisQueue::class,
                'count'     => $config['queue_count']
            ];
        }
        return $process;
    }

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
     * 运行路由
     * @param string $plugin 插件名
     * @param string $token
     * @param array  $post
     * @return void
     */
    public static function run(string $plugin, string $token, array $post): void {
        $type = static::call($plugin, $token, 'Common', 'getSendType', $token);
        if (!empty($type)) {
            switch ($type) {
                case 2:
                    // 协程
                    static::coroutine($plugin, $token, $post);
                    break;
                case 3:
                    // 队列
                    static::queue($plugin, $token, $post);
                    break;
                case 4:
                    // 异步
                    static::async($plugin, $token, $post);
                    break;
                default:
                    // 实时
                    static::exec($plugin, $token, $post);
                    break;
            }
        }
    }

    /**
     * 实时
     * @param string $plugin 插件名
     * @param string $token
     * @param array  $post
     * @return void
     */
    public static function exec(string $plugin, string $token, array $post): void {
        try {
            $config = static::config($plugin);
            /*
             * 请求信息处理
             */
            $req = BotReq::handle($post, $config);
            if (!empty($req->allow)) {
                /*
                 * 信息分类处理
                 */
                switch ($req->chat_type) {
                    case 'bot':
                        /*
                         * 机器人信息
                         */
                        static::call($plugin, $token, 'Bot', 'handle', $req);
                        break;
                    case 'group':
                        /*
                         * 群组信息
                         */
                        static::call($plugin, $token, 'Group', 'handle', $req);
                        break;
                    case 'channel':
                        /*
                         * 频道信息
                         */
                        static::call($plugin, $token, 'Channel', 'handle', $req);
                        break;
                }
            }
        } catch (Exception|Throwable $exception) {
            static::call($plugin, $token, 'Common', 'error', $exception, [
                'plugin' => $plugin,
                'token'  => $token,
                'post'   => $post,
                'code'   => $exception->getCode(),
                'msg'    => $exception->getMessage(),
                'file'   => $exception->getFile(),
                'line'   => $exception->getLine(),
                'date'   => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * 协程
     * @param string $plugin 插件名
     * @param string $token
     * @param array  $post
     * @return void
     */
    public static function coroutine(string $plugin, string $token, array $post): void {
        Coroutine::create(fn() => static::exec($plugin, $token, $post));
    }

    /**
     * 队列
     * @param string $plugin 插件名
     * @param string $token
     * @param array  $post
     * @return void
     */
    public static function queue(string $plugin, string $token, array $post): void {
        $config = static::config($plugin);
        if ($config['queue_status']) {
            alone_redis_set($config['queue_redis_key'], ['plugin' => $plugin, 'token' => $token, 'post' => $post]);
        } else {
            static::exec($plugin, $token, $post);
        }
    }

    /**
     * 异步
     * @param string $plugin 插件名
     * @param string $token
     * @param array  $post
     * @return void
     */
    public static function async(string $plugin, string $token, array $post): void {
        $config = static::config($plugin);
        if ($config['async_status'] && $config['async_connect']) {
            $async = new AsyncTcpConnection("frame://" . $config['async_connect']);
            $async->onConnect = function(AsyncTcpConnection $connection) use ($plugin, $token, $post) {
                $connection->send(json_encode(['plugin' => $plugin, 'token' => $token, 'post' => $post]));
                $connection->close();
            };
            $async->connect();
        } else {
            static::exec($plugin, $token, $post);
        }
    }

    /**
     * 获取配置
     * @param string $plugin 插件名
     * @return array
     */
    public static function config(string $plugin): array {
        if (empty(isset(static::$config[$plugin]))) {
            $confFile = __DIR__ . '/config.php';
            $conf = is_file($confFile) ? include $confFile : [];
            $configFile = run_path('plugin/' . $plugin . '/config/telegram.php');
            $config = is_file($configFile) ? include $configFile : [];
            $config['message'] = array_merge($conf['message'] ?? [], $config['message'] ?? []);
            $config['msg'] = array_merge($conf['msg'] ?? [], $config['msg'] ?? []);
            static::$config[$plugin] = array_merge($conf, $config);
        }
        return static::$config[$plugin];
    }

    /**
     * 执行方法
     * @param string $plugin 插件名
     * @param string $token
     * @param string $name   文件名
     * @param string $method 方法名
     * @param        ...$parameter
     * @return mixed
     */
    public static function call(string $plugin, string $token, string $name, string $method, ...$parameter): mixed {
        $config = static::config($plugin);
        $className = "\\" . trim(str_replace('/', '\\', $config['app_path']), '\\') . "\\" . $name;
        return call_user_func_array([new $className($plugin, $token), $method], $parameter);
    }

    /**
     * @param mixed $json
     * @param bool  $associative
     * @param int   $depth
     * @param int   $flags
     * @return mixed
     */
    public static function isJson(mixed $json, bool $associative = true, int $depth = 512, int $flags = 0): mixed {
        $json = json_decode((is_string($json) ? ($json ?: '') : ''), $associative, $depth, $flags);
        return (($json && is_object($json)) || (is_array($json) && $json)) ? $json : [];
    }

    /**
     * 数组转Json 格式化
     * @param array|object $array
     * @param bool         $int 是否数字检查
     * @return bool|string
     */
    public static function json(array|object $array, bool $int = true): bool|string {
        return $int ? json_encode($array, JSON_NUMERIC_CHECK + JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES + JSON_PRETTY_PRINT) : json_encode($array, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES + JSON_PRETTY_PRINT);
    }

    /**
     * 文件夹不存在创建文件夹(无限级)
     * @param $dir
     * @return bool
     */
    public static function mkDir($dir): bool {
        return (!empty(is_dir($dir)) || @mkdir($dir, 0777, true));
    }

    /**
     * 替换内容
     * @param string|null $string 要替换的string
     * @param array       $array  ['key'=>'要替换的内容']
     * @param string      $symbol key前台符号
     * @return string
     */
    public static function tag(string|null $string, array $array = [], string $symbol = '%'): string {
        if (!empty($string)) {
            $array = array_combine(array_map(fn($key) => ($symbol . $key . $symbol), array_keys($array)), array_values($array));
            $result = strtr($string, $array);
            $string = trim(preg_replace("/" . $symbol . "[^" . $symbol . "]+" . $symbol . "/", '', $result));
        }
        return $string ?? '';
    }

    /**
     * 定时器
     * @param int|float $interval
     * @param callable  $callable
     * @return bool|int
     */
    public static function timer(int|float $interval, callable $callable): bool|int {
        return Timer::add($interval, function() use ($interval, $callable) {
            $callable();
            static::timer($interval, $callable);
        }, [], false);
    }

    /**
     * 自定进程中获取名称
     * @param mixed $worker
     * @return string
     */
    public static function getPluginName(mixed $worker): string {
        $staticProperties = static::getPluginArr($worker);
        $plugin_name = $staticProperties['config']['name'] ?? '';
        if (empty($plugin_name)) {
            $arr = explode('.', $worker->name);
            $plugin_name = $arr[1] ?? '';
        }
        return $plugin_name;
    }

    /**
     * 自定进程中获取配置
     * @param mixed $worker
     * @return array
     */
    public static function getPluginArr(mixed $worker): array {
        $staticProperties = [];
        if ($worker && ($worker->onWorkerStart ?? '')) {
            $reflection = new ReflectionFunction($worker->onWorkerStart);
            $staticProperties = $reflection->getStaticVariables();
        }
        return $staticProperties;
    }
}