<?php
/*
$templates = [
    'post' => [
        'path' => function ($obj) {
            return '/' . $obj['slug'];
        },
        'template' => 'post',
        'type' => 'post'
    ]
];

$hooks = [
    'on_load' => function ($row) {
        $row['text'] = str_replace(['\\n', '\\r'], ["\n", "\r"], $row['text']);
        return $row;
    }
];

$title = $obj['title_de'] ?? $obj['title_en'] ?? $obj['_id'];
            return '/work/' . URLify::filter($title, 60, 'de');
            return slugify($slugger, $obj['title_de'] ?? $obj['_id']);

return ;
*/

$templates = [
    'artist' => function ($obj) {
        return '/artist/' . URLify::filter($obj['firstname'] . ' ' . $obj['familyname'], 60, 'de');
    },
    'work' => '/works/:_id'
];

$hooks = [
    'on_load' => function ($row) {
        if ($row['tags']) {
            $row['tags'] = split_tags($row['tags']);
        }
        return $row;
    }
];
