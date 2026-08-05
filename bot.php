<?php

require __DIR__ . '/vendor/autoload.php';

use Chernegasergiy\TelegramCountryBot\Core\Application;

$token = 'YOUR_BOT_TOKEN';

$app = new Application($token);
$app->run();
