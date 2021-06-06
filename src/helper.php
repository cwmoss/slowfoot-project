<?php

use Sanity\Client as SanityClient;

if (!defined('SLOWFOOT_BASE')) {
    define('SLOWFOOT_BASE', __DIR__ . '/../');
}

function load_preview_object($id, $type = null, $config) {
    // print_r($config);
    //print_r(apache_request_headers());
    //print_r($_COOKIE);
    $client = sanity_client($config['preview']['sanity']);

    $document = $client->getDocument($id);
    //print_r($document);
    return $document;
    return [
        '_id' => $id,
        '_type' => 'artist',
        'title' => 'hoho',
        'firstname' => 'HEiko',
        'familyname' => 'van Gogh'
    ];
}

function load_sanity($opts, $config) {
    $client = sanity_client($opts);
    $res = $client->fetch(
        '*[_type == $type]',
        ['type' => 'newsletter']
    );
    return $res;
}

function sanity_client($config) {
    return new SanityClient($config);
}
function split_tags($tags) {
    return array_filter(array_map('trim', preg_split('/[,;]/', $tags)), 'trim');
}
