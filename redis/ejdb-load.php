<?php
$loader = require __DIR__ . '/vendor/autoload.php';
require_once '../helper.php';

$base = 'http://localhost:9191/mumok';

foreach (file(__DIR__ . '/../dataset-mumok.ndjson') as $line) {
    $res = fetch($base, $line);
    //var_dump($res);
    //exit;
}
