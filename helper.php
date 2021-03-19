<?php

function query($q, $vars=[])
{
    $q = str_replace(array_map(function ($k) {
        return '$'.$k;
    }, array_keys($vars)), array_values($vars), $q);
    #print "-- Q: $q";
    return query_cmd($q);
}

function query_cmd($q)
{
    $dataset = "dataset.ndjson";

    $cmd = sprintf("cat %s | groq -i ndjson -o json '%s'", $dataset, $q);
    $res = `$cmd`;
    return json_decode($res, true);
}

function path($pdb, $oid)
{
    return $pdb[$oid];
}

function process_template($id, $path)
{
    global $templates;
    layout("-");
    $data = query('*[_id=="$id"][0]', ['id'=>$id]);
    process_template_data($data, $path);
}

function template($_template, $data, $_base)
{
    extract($data);
    ob_start();
    include($_base.'/templates/'.$_template.'.html');
    $content=ob_get_clean();
    $layout = layout();
    if ($layout) {
        ob_start();
        include($_base.'/templates/__'.$layout.'.html');
        $content=ob_get_clean();
    }
    return $content;
}


function page($_template, $data, $helper, $_base)
{
    extract($data);
    extract($helper);
    ob_start();
    include($_base.'/pages/'.$_template.'.html');

    $content=ob_get_clean();
    $layout = layout();
    if ($layout) {
        ob_start();
        include($_base.'/templates/__'.$layout.'.html');
        $content=ob_get_clean();
    }
    return $content;
}

function check_pagination($_template, $_base)
{
    ob_start();
    include($_base.'/pages/'.$_template.'.html');
    $content=ob_get_clean();
    $prule = paginate();
    paginate("-");
    return $prule;
}

function page_paginated($_template, $data, $_base)
{
    extract($data);
    ob_start();
    include($_base.'/pages/'.$_template.'.html');
    $content=ob_get_clean();
    $layout = layout();
    if ($layout) {
        ob_start();
        include($_base.'/templates/__'.$layout.'.html');
        $content=ob_get_clean();
    }
    return $content;
}

function db_paginate($db, $rule)
{
    $limit=20;
    $res = [];
    #print $rule;
    foreach ($db as $id=>$row) {
        # print $row['_type'];
        if ($row['_type']==$rule) {
            $res[] = $row;
        }
        if (sizeof($res)>=$limit) {
            break;
        }
    }
    return $res;
}

function paginate($how=null)
{
    static $rules;
    if (!is_null($how)) {
        // reset
        if ($how=='-') {
            $rules = null;
        }
        $rules = $how;
    }
    return $rules;
}

function process_template_data($data, $path)
{
    global $templates;
    $file_template = $templates[$data['_type']]['template'];
    extract($data);
    ob_start();
    include($file_template.'.html');
    $content=ob_get_clean();
    $layout = layout();
    if ($layout) {
        ob_start();
        include('templates/__'.$layout.'.html');
        $content=ob_get_clean();
    }
    write($content, $path);
}

function layout($name=null)
{
    static $layout=null;
    if (!is_null($name)) {
        // reset layout name
        if ($name=='-') {
            $layout = null;
        }
        $layout = $name;
    }
    return $layout;
}

function write($content, $path)
{
    $file = __DIR__.'/dist/'.$path.'/index.html';
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($file, $content);
}

function send_file($base, $file)
{
    $name = basename($file);
    $full = $base.'/'.$file;

    if (preg_match('/\.css$/', $name)) {
        header('Content-Type: text/css');
        $scss = $full.'.scss';
        if (file_exists($scss)) {
            # die(" sassc $scss $full");
            $ok = `sassc $scss $full`;
            #var_dump($ok);
        }
    } elseif (preg_match('/js$/', $name)) {
        header('Content-Type: text/javascript');
    } elseif (preg_match('/svg$/', $name)) {
        header('Content-Type: image/svg+xml');
    } elseif (preg_match('/html$/', $name)) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Content-Type: text/html');
    }
    // dbg('sending', $full);
    if (file_exists($full)) {
        readfile($full);
    } else {
        header('HTTP/1.1 404 Not Found');
    }
}

function dbg($txt, ...$vars)
{
    // im servermodus wird der zeitstempel automatisch gesetzt
    //	$log = [date('Y-m-d H:i:s')];
    $log = [];
    if (!is_string($txt)) {
        array_unshift($vars, $txt);
    } else {
        $log[] = $txt;
    }
    $log[] = join(' ', array_map('json_encode', $vars));
    error_log(join(' ', $log));
}

function markdown($text)
{
    $parser = new Parsedown();
    //$parser->setUrlsLinked(false);
    return $parser->text($text);
}
