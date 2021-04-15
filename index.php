<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require DIR . '/vendor/autoload.php';
require DIR . '/vendor/yiisoft/yii2/Yii.php';

$config = require DIR . '/config/web.php';

(new yii\web\Application($config))->run(); 