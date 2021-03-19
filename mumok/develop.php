<?php
$loader = require(__DIR__.'/../vendor/autoload.php');
$requestpath = $_SERVER['SCRIPT_NAME'];
if ($requestpath=='/') {
    $requestpath='/index';
}

// /a/9990
if ($requestpath=='/phrwatcher.php') {
    include('phrwatcher.php');
    exit;
}

$base = __DIR__;

require_once("helper.php");

if (preg_match("/\./", $requestpath)) {
    send_file(__DIR__, $requestpath);
    exit;
}

$dataset = "dataset.ndjson";

$db = [];
foreach (file($dataset) as $line) {
    $row = json_decode($line, true);
    $db[$row['_id']] = $row;
}

//phpinfo();

$templates = [
    'article' => [
        'path' => function ($obj) {
            return "/a/{$obj['_id']}";
        },
        'template' => 'article',
        'type' => 'article'
    ],
    'channel' => [
        'path' => function ($obj) {
            return "/c/{$obj['_id']}";
        },
        'template' => 'channel',
        'type' => 'channel'
    ]
];

$paths = array_reduce($templates, function ($res, $item) {
    return array_merge($res, array_map(function ($obj) use ($item) {
        #print_r($obj);
        return [$obj['_id'], $item['path']($obj)];
    }, query('*[_type=="$type"]{_id, title,slug,created_at}', ['type'=>$item['type']])));
}, []);

$paths = array_combine(array_column($paths, 0), array_column($paths, 1));
//print_r($paths);

$path = function ($oid) use ($paths) {
    #print "-- $oid";
    return path($paths, $oid);
};

$pages = glob($base."/".$pages."/.html");
$pages = array_map(function ($p) {
    return '/'.basename($p, '.html');
}, $pages);

$obj_id = array_search($requestpath, $paths);
if ($obj_id) {
    $obj = $db[$obj_id];

    $template = $templates[$obj['_type']]['template'];
    
    $res = template($template, $obj, __DIR__);
} else {
    $obj_id = array_search($requestpath, $pages);
    $paginate = check_pagination($requestpath, __DIR__);
    if ($paginate) {
        #var_dump($paginate);
        $coll = db_paginate($db, $paginate);
        #print_r($coll);
        $res = page($requestpath, ['articles'=>$coll], ['path'=>$path], __DIR__);
    } else {
        $res = page($requestpath, [], ['path'=>$path], __DIR__);
    }
}


print $res;
