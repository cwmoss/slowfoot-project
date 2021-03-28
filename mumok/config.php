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
    'work' => '/works/:_id',
    'tag' => '/tag/:name'
];

$hooks = [
    'on_load' => function ($row, &$db) {
        if ($row['tags']) {
            $tags = split_tags($row['tags']);
            $refs = [];
            foreach ($tags as $t) {
                $name = URLify::filter($t, 60, 'de');
                $title = $t;
                $id = 't-' . $name;
                if ($db[$id]) {
                    $db[$id]['works'][] = ['_ref' => $row['_id']];
                } else {
                    $db[$id] = [
                        '_id' => $id,
                        '_type' => 'tag',
                        'name' => $name,
                        'title' => $t,
                        'works' => [
                            ['_ref' => $row['_id']]
                        ]
                    ];
                }
                $refs[] = ['_ref' => $id];
            }
            $row['tags'] = $refs;
        }
        return $row;
    }
];
