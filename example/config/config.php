<?php

declare(strict_types=1);

use function Imi\env;
use Imi\Util\Imi;

return [
    // 项目根命名空间
    'namespace'    => 'app',

    // 配置文件
    'configs'    => [
        'beans'        => __DIR__ . '/beans.php',
    ],

    'ignorePaths' => [
        \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'public',
    ],

    // 组件命名空间
    'components'    => [
        'opentracing'    => 'Imi\OpenTracing',
    ],

    // 主服务器配置
    'mainServer'    => [
        'namespace'    => 'app\ApiServer',
        'type'         => \Imi\Swoole\Server\Type::HTTP,
        'host'         => '0.0.0.0',
        'port'         => 8080,
        'configs'      => [
            'worker_num'        => 1,
        ],
    ],

    // 子服务器（端口监听）配置
    'subServers'        => [
    ],

    // Workerman 服务器配置
    'workermanServer' => [
        'http' => [
            'namespace'    => 'app\ApiServer',
            'type'         => \Imi\Workerman\Server\Type::HTTP,
            'host'         => '0.0.0.0',
            'port'         => 8080,
            'configs'      => [
            ],
        ],
        'channel' => [
            'namespace'   => '',
            'type'        => \Imi\Workerman\Server\Type::CHANNEL,
            'host'        => '0.0.0.0',
            'port'        => 13005,
            'configs'     => [
            ],
        ],
    ],

    'workerman' => [
        // 多进程通讯组件配置
        'channel' => [
            'host' => '127.0.0.1',
            'port' => 13005,
        ],
    ],

    // fpm 服务器配置
    'fpm' => [
        'serverPath' => \dirname(__DIR__) . '/ApiServer',
    ],

    'imi' => [
        'beans' => Imi::checkAppType('workerman') ? [
            'ServerUtil' => 'ChannelServerUtil',
        ] : [],
    ],

    // 连接池配置
    'pools'    => [
        // 主数据库
        'maindb'    => [
            'pool'    => [
                // 协程池类名
                'class'    => \Imi\Db\Pool\SyncDbPool::class,
                // 连接池配置
                'config'        => [
                    'maxResources'              => 10,
                    'minResources'              => 1,
                    'checkStateWhenGetResource' => false,
                ],
            ],
            // 连接池资源配置
            'resource'    => [
                'host'        => env('MYSQL_SERVER_HOST', '127.0.0.1'),
                'port'        => env('MYSQL_SERVER_PORT', 3306),
                'username'    => env('MYSQL_SERVER_USERNAME', 'root'),
                'password'    => env('MYSQL_SERVER_PASSWORD', 'root'),
                'database'    => 'mysql',
                'charset'     => 'utf8mb4',
                'initSqls'    => [
                    'SET @__pool_name="maindb"',
                ],
            ],
        ],
        'redis'    => [
            'pool'    => [
                'class'        => \Imi\Redis\SyncRedisPool::class,
                'config'       => [
                    'maxResources'    => 10,
                    'minResources'    => 1,
                ],
            ],
            'resource'    => [
                'host'      => env('REDIS_SERVER_HOST', '127.0.0.1'),
                'port'      => env('REDIS_SERVER_PORT', 6379),
                'password'  => env('REDIS_SERVER_PASSWORD'),
            ],
        ],
    ],
    // db 配置
    'db' => [
        // 默认连接池名
        'defaultPool' => 'maindb',
        'connections' => [
            'tradition' => [
                'dbClass'  => 'PdoMysqlDriver',
                'host'     => env('MYSQL_SERVER_HOST', '127.0.0.1'),
                'port'     => env('MYSQL_SERVER_PORT', 3306),
                'username' => env('MYSQL_SERVER_USERNAME', 'root'),
                'password' => env('MYSQL_SERVER_PASSWORD', 'root'),
                'database' => 'mysql',
                'charset'  => 'utf8mb4',
            ],
        ],
    ],
    // redis 配置
    'redis' => [
        // 默认连接池名
        'defaultPool'   => 'redis',
        'connections'   => [
            'tradition' => [
                'host'        => env('REDIS_SERVER_HOST', '127.0.0.1'),
                'port'        => env('REDIS_SERVER_PORT', 6379),
                'password'    => env('REDIS_SERVER_PASSWORD'),
                'serialize'   => false,
            ],
        ],
    ],

    // 日志配置
    'logger' => [
        'channels' => [
            'imi' => [
                'handlers' => [
                    [
                        'class'     => \Imi\Log\Handler\ConsoleHandler::class,
                        'formatter' => [
                            'class'     => \Imi\Log\Formatter\ConsoleLineFormatter::class,
                            'construct' => [
                                'format'                     => null,
                                'dateFormat'                 => 'Y-m-d H:i:s',
                                'allowInlineLineBreaks'      => true,
                                'ignoreEmptyContextAndExtra' => true,
                            ],
                        ],
                    ],
                    [
                        'class'     => \Monolog\Handler\RotatingFileHandler::class,
                        'construct' => [
                            'filename' => \dirname(__DIR__) . '/.runtime/logs/log.log',
                        ],
                        'formatter' => [
                            'class'     => \Monolog\Formatter\LineFormatter::class,
                            'construct' => [
                                'dateFormat'                 => 'Y-m-d H:i:s',
                                'allowInlineLineBreaks'      => true,
                                'ignoreEmptyContextAndExtra' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
