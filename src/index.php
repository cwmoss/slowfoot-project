<?php

/*
    this is the entry point for development
    & not using the php build in server
*/

define('SLOWFOOT_START', microtime(true));
define('SLOWFOOT_BASE', __DIR__ . '/../');

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../vendor/cwmoss/slowfoot-lib/src/dev-apache.php';
