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
return ;
*/

$templates = [
    'artist' => [
        'path' => function ($obj) use ($slugger) {
            $title = $obj['firstname'] . ' ' . $obj['familyname'];
            return '/artist/' . URLify::filter($title, 60, 'de');
            return slugify($slugger, $obj['firstname'] . ' ' . $obj['familyname']);
            return "/a/{$obj['_id']}";
        },
        'template' => 'artist',
        'type' => 'artist'
    ],
    'work' => [
        'path' => function ($obj) use ($slugger) {
            $title = $obj['title_de'] ?? $obj['title_en'] ?? $obj['_id'];
            return '/work/' . URLify::filter($title, 60, 'de');
            return slugify($slugger, $obj['title_de'] ?? $obj['_id']);
            return "/w/{$obj['_id']}";
        },
        'template' => 'work',
        'type' => 'work'
    ]
];
