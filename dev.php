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

include $src . '/helper.php';

//$dataset = 'wp.json';
$dataset = 'dataset-mumok.ndjson';

$config = load_config($src);
//print_r($config);
[$templates, $hooks] = $config;

//var_dump($hooks);
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

//print_r($ds['_info']);

$template_helper = load_template_helper($ds, $paths, $src);

$pages = glob($base . '/' . $pages . '/.html');
$pages = array_map(function ($p) {
    return '/' . basename($p, '.html');
}, $pages);

$obj_id = array_search($requestpath, $paths);
if ($obj_id) {
    $obj = get($ds, $obj_id);

    $template = $templates[$obj['_type']]['template'];
    dbg('template', $template, $obj);
    $content = template($template, ['page' => $obj], $template_helper, $src);
} else {
    list($dummy, $pagename, $pagenr) = explode('/', $requestpath);
    $pagename = '/' . $pagename;
    dbg('page...', $pagename, $pagenr, $requestpath);
    $obj_id = array_search($pagename, $pages);
    $pagination_query = check_pagination($pagename, $src);
    dbg('page query', $pagination_query);
    if ($pagination_query) {
        //var_dump($paginate);
        $coll = query_page($ds, $pagination_query, $pagenr);
        //print_r($coll);
        $content = page($pagename, ['collection' => $coll], $template_helper, $src);
        $content = remove_tags($content);
    } else {
        $content = page($requestpath, [], $template_helper, $src);
    }
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: text/html');

print $content;
