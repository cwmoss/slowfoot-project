<?php
error_reporting(E_ALL);

$in = 'mumok-at.jsonld';
$out = 'dataset-mumok.ndjson';

$data = json_decode(file_get_contents($in), true);
$dataset = [];

foreach ($data['@set'] as $work) {
    $work = convert($work, 'work');
    $refs = [];
    foreach ($work['artists'] as $artist) {
        $art = convert($artist, 'artist');
        $art['works'][] = ['_ref' => $work['_id']];
        $refs[] = ['_ref' => $art['_id']];
        if (!isset($dataset[$art['_id']])) {
            $dataset[$art['_id']] = $art;
        } else {
            $dataset[$art['_id']]['works'][] = ['_ref' => $work['_id']];
        }
    }
    $work['artists'] = $refs;
    $dataset[$work['_id']] = $work;
}

$newd = array_map('json_encode', $dataset);

file_put_contents($out, join("\n", $newd));

function convert($obj, $type) {
    $id = $type[0] . '-' . basename($obj['@id']);
    return array_merge(['_id' => $id, '_type' => $type], $obj);
}
