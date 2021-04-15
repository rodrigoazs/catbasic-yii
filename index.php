<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require DIR . '/vendor/autoload.php';
require DIR . '/vendor/yiisoft/yii2/Yii.php';

$config = require <strong>DIR</strong> . '/config/web.php';

(new yii\web\Application($config))-&gt;run(); 