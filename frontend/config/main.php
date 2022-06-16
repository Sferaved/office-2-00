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
    'name'=>'Офис on-line',
    'language'=>'ru',
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'formatter' => [
            'dateFormat' => 'dd.MM.yyyy',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
            'currencyCode' => 'UAH',
       ],
        'request' => [
            'baseUrl' => '',
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
                '<controller:\w+>/<action:\w+>/' => '<controller>/<action>',
            ],
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
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
                'password' => 'pdnjd3AaL4dFH6Sc', // Пароль
                'port' => '2525', // Порт
                'encryption' => 'ssl', // Шифрование
		    ],
        ],
    ],
    'params' => $params,
];
