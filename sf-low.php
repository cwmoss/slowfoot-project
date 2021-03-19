<?php
$loader = require(__DIR__.'/vendor/autoload.php');
require_once("helper.php");

$dataset = "dataset.ndjson";

print memory_get_usage()."\n";

#$db = json_decode('['.str_replace("\n", ",", trim(file_get_contents($dataset))).']', true);
$db = [];
foreach(file($dataset) as $line){
	$row = json_decode($line, true);
	$db[$row['_id']] = $row;
}


print memory_get_usage()."\n";

#   496072
# 13685128

use Spatie\Async\Pool;


$templates = [
	'article' => [
		'path' => function($obj){
			return "a/{$obj['_id']}";
		},
		'template' => 'templates/article',
		'type' => 'article'
	],
	'channel' => [
		'path' => function($obj){
			return "c/{$obj['_id']}";
		},
		'template' => 'templates/channel',
		'type' => 'channel'
	]
];


$paths = array_reduce($templates, function($res, $item){
	return array_merge($res, array_map(function($obj)use($item){
		#print_r($obj);
		return [$obj['_id'], $item['path']($obj)];
	}, query('*[_type=="$type"]{_id, title,slug,created_at}', ['type'=>$item['type']])));
}, []);

$paths = array_combine(array_column($paths, 0), array_column($paths, 1));
#print_r($paths);
path("", $paths);

print memory_get_usage()."\n";

foreach($db as $id=>$row){
	$type = $row['_type'];
	if(!$templates[$type]) continue;
	$conf = $templates[$type];
	process_template_data($row, path($row['_id']));
}

print memory_get_usage()."\n";

exit;

$pool = Pool::create()->concurrency(20);
foreach($templates as $type=>$conf){
	#$count = query('');
	//if($type=='article') continue;
	$bs = 100;
	$start = 0;
	while($rows=query('*[_type=="$type"][$start..$end]', ['type'=>$type, 'start'=>$start, 'end'=>$start + $bs])){
		#exit;
		foreach($rows as $row){
		//	process_template_data($row, path($row['_id']));
			$path = $paths[$row['_id']];
			$pool->add(function () use ($row, $type, $conf, $path, $paths) {
				require_once("helper.php");
				path("", $paths);
				$file_template = $conf['template'];
				extract($row);
				ob_start();
				include($file_template.'.html');
				$content=ob_get_clean();
				$layout = layout();
				if($layout){
					ob_start();
					include('templates/__'.$layout.'.html');
					$content=ob_get_clean();
				}
				write($content, $path);
				return "gut";
		    })->then(function ($output) {
		        print "OK";
		    })->catch(function (Throwable $exception) {
		        print "exception: ".$exception->getMessage()."\n";
		    });
		}

		$start += $bs;
	}
}

$pool->wait();

/*
foreach($paths as $id=>$path){
	if($id[0]=='a') continue;
	#if($id!='c100') continue;
	process_template($id, $path);
}
*/
