<?php

if (!defined('SLOWFOOT_BASE')) {
    define('SLOWFOOT_BASE', __DIR__ . '/../');
}

function load_preview_object($id, $type = null, $config) {
    return [
        '_id' => $id,
        '_type' => 'artist',
        'title' => 'hoho',
        'firstname' => 'HEiko',
        'familyname' => 'van Gogh'
    ];
}

function split_tags($tags) {
    return array_filter(array_map('trim', preg_split('/[,;]/', $tags)), 'trim');
}
