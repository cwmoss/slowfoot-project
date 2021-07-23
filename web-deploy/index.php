<?php

define('SLOWFOOT_START', microtime(true));
define('SLOWFOOT_BASE', dirname(__DIR__));

require SLOWFOOT_BASE . '/vendor/autoload.php';

require SLOWFOOT_BASE . '/vendor/cwmoss/slowfoot-lib/src/webdeploy.php';