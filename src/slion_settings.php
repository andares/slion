<?php
return [
    'php_ini'   => [
//        'setup' => [
//            'assert.exception'  => 1,
//        ],
//        'check' => [
//            'zend.assertions'   => -1,
//        ],
    ],
    'config'    => [
        'scene'     => 'dev',
        'default'   => 'default', // 默认scene
    ],
    'logger'    => [
        'dir'   => __DIR__ . '/../logs',
        'email' => '', // 发邮件
    ],
    'lang'      => 'zh_CN.utf8',
    'pack'      => [], // pack 配置

    'debug'     => [
        'debug_in_web'      => true,        // 是否在http返回中使用tracy调试
        'slow_log'          => 20,          // 慢请求记录，设0不限，单位毫秒，不计输出
        'error_handler'     => ['\\Slion\\Debugger', 'errorHandler'],
        'exception_handler' => ['\\Slion\\Debugger', 'exceptionHandler'],
    ],
    'tracy'     => [
        'is_prod'       => null, // 设置 null 时跟 displayErrorDetails 配置走
        'strict_mode'   => true, // 严格模式强烈建议打开
        'scream'        => true, // 设error_reporting E_ALL
        'max_depth'     => 8,
        'max_length'    => 160,
    ],
];
