<?php
$loader = require __DIR__ . '/vendor/autoload.php';

define('PATH_PREFIX', '/dev/slowfoot/dist/');

$base = __DIR__;
$src = $base . '/mumok';
$dist = __DIR__ . '/dist/';

require_once 'helper.php';
require_once 'slft_fun.php';
include 'template_helper.php';

$dataset = 'dataset-mumok.ndjson';
//$dataset = 'wp.json';

print memory_get_usage() . "\n";

include $src . '/config.php';

$ds = load_data($dataset, $hooks);

print memory_get_usage() . " load ok\n";

$paths = array_reduce($templates, function ($res, $item) use ($ds) {
    return array_merge($res, array_map(function ($obj) use ($item) {
        //print_r($obj);
        return [$obj['_id'], $item['path']($obj)];
    }, query($ds, ['_type' => $item['type']])));
}, []);

$paths = array_combine(array_column($paths, 0), array_column($paths, 1));
//print_r($paths);

print memory_get_usage() . " paths ok\n";

$template_helper = load_template_helper($ds, $paths, $src);

print memory_get_usage() . " helper ok \n";

//print_r(get($ds, 'p-20604'));
//exit;

$pages = glob($src . '/pages/*.html');
$pages = array_map(function ($p) {
    return '/' . basename($p, '.html');
}, $pages);

//print 'clean up dist/';
//`rm -rf $dist`;

print_r($ds['_info']);
exit;

foreach ($templates as $type => $conf) {
    //$count = query('');
    //if($type=='article') continue;
    $bs = 100;
    $start = 0;

    foreach (query($ds, $type) as $row) {
        //	process_template_data($row, path($row['_id']));
        $path = fpath($paths, $row['_id']);
        $content = template($conf['template'], $row, $template_helper, $src);
        write($content, $path);
    }
}

print memory_get_usage() . " templates ok\n";

foreach ($pages as $pagename) {
    //dbg('page... ', $pagename);
    $paginate = check_pagination($pagename, $src);
    $pagepath = $pagename;
    if ($pagepath == '/index') {
        $pagepath = '/';
    }
    if ($paginate) {
        $pagenr = 1;
        $path = $pagepath;
        foreach (chunked_paginate($ds, $paginate) as $res) {
            //dbg('page', $pagenr);
            $content = page($pagename, ['paginated' => $res], $template_helper, $src);
            $content = remove_tags($content);
            write($content, $pagepath);
            $pagenr++;
            $pagepath = $path . '/' . $pagenr;
        }
    } else {
        $content = page($pagename, [], $template_helper, $src);
        write($content, $pagepath);
    }
}

print memory_get_usage() . " pages ok\n";

`cp -R $src/css $dist/`;

print "finished\n";
