<?php
$loader = require __DIR__ . '/vendor/autoload.php';

define('PATH_PREFIX', '');

$base = __DIR__;
$src = $base . '/mumok';
$dist = __DIR__ . '/dist/';

require_once 'helper.php';
include 'routing.php';

require_once 'slft_fun.php';
include 'template_helper.php';

$dataset = 'wp.json';
//$dataset = 'dataset-mumok.ndjson';

include $src . '/config.php';

$ds = load_data($dataset, $hooks);

$paths = array_reduce($templates, function ($res, $item) use ($ds) {
    return array_merge($res, array_map(function ($obj) use ($item) {
        //print_r($obj);
        return [$obj['_id'], $item['path']($obj)];
    }, query($ds, ['_type' => $item['type']])));
}, []);

$paths = array_combine(array_column($paths, 0), array_column($paths, 1));
//print_r($paths);
//print $requestpath;

$template_helper = load_template_helper($ds, $paths, $src);

$pages = glob($base . '/' . $pages . '/.html');
$pages = array_map(function ($p) {
    return '/' . basename($p, '.html');
}, $pages);

$obj_id = array_search($requestpath, $paths);
if ($obj_id) {
    $obj = get($ds, $obj_id);

    $template = $templates[$obj['_type']]['template'];

    $content = template($template, $obj, $template_helper, $src);
} else {
    list($dummy, $pagename, $pagenr) = explode('/', $requestpath);
    $pagename = '/' . $pagename;
    dbg('page...', $pagename, $pagenr, $requestpath);
    $obj_id = array_search($pagename, $pages);
    $paginate = check_pagination($pagename, $src);
    if ($paginate) {
        //var_dump($paginate);
        $coll = db_paginate($ds, $paginate, $pagenr);
        //print_r($coll);
        $content = page($pagename, ['paginated' => $coll], $template_helper, $src);
        $content = remove_tags($content);
    } else {
        $content = page($requestpath, [], $template_helper, $src);
    }
}

print $content;
