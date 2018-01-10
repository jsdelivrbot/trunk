<?php

use PetrKnapCz\RemoteContent\RemoteContentAccessor;
use PetrKnap\Php\ServiceManager\ServiceManager;

require_once __DIR__ . '/../vendor/autoload.php';

PetrKnapCz\container()
    ->get(RemoteContentAccessor::class)
    ->getResponse('https://petrknap.github.io/index_cz.html')
    ->send();
