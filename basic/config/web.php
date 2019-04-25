<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'li4M3FKEATBlHgyrH_yZDzJm9aq570eK',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
//                [
//                    'class' => 'yii\log\FileTarget',
//                    'levels' => ['error', 'warning'],
//                ],
                [
                    // 日志处理类
                    'class' => 'net263\log\KafkaTarget',
                    // 处理日志的级别，目前只支持这四种级别
                    'levels' => ['warning','error','info','trace'],
                    // 上下文消息，为空则不添加
                    'logVars' => [],
                    // 服务名称，必须设置
                    'serviceName' => 'TEST',
                    // 服务版本号，必须设置
                    'serviceVersion' => 'v4.5.1',
                    // kafka 消息主题
                    'topic' => 'tv_server_logstash_log',
                    // kafka 服务器地址 必须配置，可配置多个，逗号隔开 例如："127.0.0.1:9092,127.0.0.1:9093"
                    //'brokers' => '127.0.0.1',
                    'brokers' => '211.100.75.227:9092',
                    'enabled' => true,
                ],
            ],
        ],
        'db' => $db,
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
