<?php

require 'helper.php';
require_once 'lolql.php';
use function lolql\parse;
use function lolql\eval_cond;

$db = [
    ['_id' => 'a1', 'title' => 'hey', 'status' => 'draft', 'authors' => [['_ref' => '2'], ['_ref' => '4']]],
    ['_id' => 'a2', 'title' => 'hello', 'status' => 'published'],
    ['_id' => 'a3', 'title' => 'world', 'status' => 'published'],
    ['_id' => 'a4', 'title' => 'hello world', 'status' => 'published', '_type' => 'article'],
    ['_id' => 'a5', 'title' => 'world is caos', 'status' => 'draft', '_type' => 'article'],
    ['_id' => 'a6', 'title' => 'yourworld is caos', 'status' => 'published'],
];

$tests = [
    '*(title == "hello")',
    '*(title matches "world*" || status == "draft")',
    '*(authors._ref == "4")',
    '*(title matches "hello" || (status == "draft" && _id == "a5"))',
    'article()',
    'article( status=="draft" )'
];

foreach ($tests as $t) {
    print "\n\n$t\n";
    $t = parse($t);
    dbg('parsed..', $t);
    //print_r($t);
    print_r(eval_cond($db, $t['q']));
}
