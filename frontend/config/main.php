<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'name' => 'Офис on-line',
    'language' => 'ru',
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'formatter' => [
            'dateFormat' => 'dd.MM.yyyy',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
            'currencyCode' => 'UAH',
        ],
        'request' => [
            'baseUrl' => '', // Убедитесь, что этот параметр установлен правильно для вашего приложения
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                '/homezatraty' => 'homezatraty/index',
                '/declaration/index' => 'declaration/index',
                '/client/index' => 'client/index',
                '/invoice' => 'invoice/index',
                '/cabinet' => 'cabinet/index',
                '/upload' => 'upload/index',
                '/aquaizol' => 'aquaizol/index',
                '/flex' => 'flex/index',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>', // Уберите лишний слэш в конце
            ],
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // Название куки для сессии
            'name' => 'advanced-frontend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'logFile' => '@runtime/logs/info.log', // Путь для логов уровня info
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.ukr.net', // SMTP сервер почтовика
                'username' => 'sferaved@ukr.net', // Логин (адрес электронной почты)
                'password' => 'nMCiWCvys5fPZVqA', // Пароль (рекомендуется использовать переменные окружения для безопасности)
                'port' => '2525', // Порт
                'encryption' => 'ssl', // Шифрование
            ],
        ],
    ],
    'params' => $params,
];
