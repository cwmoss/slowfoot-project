<?php
$loader = require __DIR__ . '/vendor/autoload.php';

$client = new Predis\Client();

$data = file_get_contents(__DIR__ . '/../dataset-mumok.ndjson');
$data = trim($data);
$data = str_replace(["\n", "\r"], [',', ''], $data);
$data = '[' . $data . ']';
$ok = $client->executeRaw(
    ['JSETR', 'data', $data]
);
var_dump($ok);

/*
JPATH data $[*].[?(@.in_exhibition=='no')]._id
JPATH data $[*].[?(@._id=='w-111464')]
JPATH data $[*].title_de
JPATH data $.._id
JPATH data $[1000]
JGET data 1000._id
JGET data 1000
*/
exit;
foreach (file(__DIR__ . '/../dataset-mumok.ndjson') as $line) {
    $rec = json_decode($line, true);
    $key = $rec['_id'];

    $client->executeRaw(
        ['JSETR', $key, $line]
    );
    //print $key;
    //exit;
}

exit;

$client->set('foo', 'bar');
$value = $client->get('foo');

$a = ['_id' => 'a00', 'title' => 'hello! überraschung'];
// $client->hmset('a00', ['path' => '/a/a00']);
// $client->hset('a00', 'd', );
// 'd' => json_encode($a)
/*$client->rawCommand(sprintf(
    'JSETR %s `%s`',
    'a00',
    json_encode($a)
));*/
$ok = $client->executeRaw(
    ['JSETR', 'a00', json_encode($a)]
);
var_dump($ok);
print_r($client->executeRaw(['JGET', 'a00']));
