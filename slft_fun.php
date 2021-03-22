<?php

function load_data($dataset, $hooks) {
    $db = [];
    foreach (file($dataset) as $line) {
        $row = json_decode($line, true);
        if ($hooks['on_load']) {
            $row = $hooks['on_load']($row);
        }
        $db[$row['_id']] = $row;
    }
    return $db;
}

function query($ds, $filter) {
    if (is_string($filter)) {
        $filter = ['_type' => $filter];
    }
    $rs = array_filter($ds, function ($row) use ($filter) {
        return evaluate($filter, $row);
    });

    if ($filter['_type'] == 'artist') {
        $skey = 'firstname';
    }

    if ($skey) {
        dbg('... sorting..');
        usort($rs, build_sorter($skey));
    }
    return $rs;
}

function build_sorter($key) {
    return function ($a, $b) use ($key) {
        return strnatcmp($a[$key], $b[$key]);
    };
}

function evaluate($cond, $data) {
    foreach ($cond as $k => $v) {
        $ok = evaluate_single($k, $v, $data);
        if (!$ok) {
            return false;
        }
    }
    return true;
}
function evaluate_single($key, $value, $data) {
    $nested = explode('.', $key);
    $current = array_shift($nested);

    if ($nested) {
        return evaluate_single(join('.', $nested), $value, $data[$current]);
    }

    if (!$data) {
        return false;
    }

    if (!is_assoc($data)) {
        return array_find($data, $value, $current);
    } else {
        return $data[$current] == $value;
    }
}

function array_find($haystack, $needle, $prop) {
    foreach ($haystack as $val) {
        if ($val[$prop] == $needle) {
            return true;
        }
    }
    return false;
}

function is_assoc(array $arr) {
    if ([] === $arr) {
        return false;
    }
    return array_keys($arr) !== range(0, count($arr) - 1);
}

function slow_query($q, $vars = []) {
    $q = str_replace(array_map(function ($k) {
        return '$' . $k;
    }, array_keys($vars)), array_values($vars), $q);
    //print "-- Q: $q";
    return query_cmd($q);
}

function slow_query_cmd($q) {
    $dataset = 'dataset-mumok.ndjson';

    $cmd = sprintf("cat %s | groq -i ndjson -o json '%s'", $dataset, $q);
    $res = `$cmd`;
    return json_decode($res, true);
}

function path($pdb, $oid) {
    if (is_array($oid)) {
        $oid = $oid['_id'];
    }
    return PATH_PREFIX . $pdb[$oid];
}

// file path = path without prefix
function fpath($pdb, $oid) {
    if (is_array($oid)) {
        $oid = $oid['_id'];
    }
    return $pdb[$oid];
}

function get($ds, $oid) {
    if (is_array($oid)) {
        $oid = $oid['_id'];
    }
    return $ds[$oid];
}

function ref($ds, $oid) {
    if (is_array($oid)) {
        $oid = $oid['_ref'];
    }
    return $ds[$oid];
}

function process_template($id, $path) {
    global $templates;
    layout('-');
    $data = query('*[_id=="$id"][0]', ['id' => $id]);
    process_template_data($data, $path);
}

function partial($base, $template, $data, $helper) {
    extract($data);
    extract($helper);
    ob_start();
    include $base . '/partials/' . $template . '.html';
    $content = ob_get_clean();
    return $content;
}

function remove_tags($content) {
    //dbg('remove...');
    $content = preg_replace('!<page>.*?</page>!ism', '', $content);
    return $content;
}

function template($_template, $data, $helper, $_base) {
    extract($data);
    extract($helper);
    ob_start();
    include $_base . '/templates/' . $_template . '.html';
    $content = ob_get_clean();
    $layout = layout();
    if ($layout) {
        ob_start();
        include $_base . '/layout/' . $layout . '.html';
        $content = ob_get_clean();
    }
    return $content;
}

function page($_template, $data, $helper, $_base) {
    extract($data);
    extract($helper);
    ob_start();
    include $_base . '/pages/' . $_template . '.html';

    $content = ob_get_clean();
    $layout = layout();
    if ($layout) {
        ob_start();
        include $_base . '/layout/' . $layout . '.html';
        $content = ob_get_clean();
    }
    return $content;
}

function check_pagination($_template, $_base) {
    ob_start();
    $content = file_get_contents($_base . '/pages/' . $_template . '.html');
    $prule = preg_match('!<page>(.*?)</page>!ism', $content, $mat);
    if ($prule) {
        $prule = trim($mat[1]);
        dbg('++ prule', $prule);
    }
    return $prule;

    $content = ob_get_clean();
    $prule = paginate();
    paginate('-');
    return $prule;
}

function page_paginated($_template, $data, $_base) {
    extract($data);
    ob_start();
    include $_base . '/pages/' . $_template . '.html';
    $content = ob_get_clean();
    $layout = layout();
    if ($layout) {
        ob_start();
        include $_base . '/layout/' . $layout . '.html';
        $content = ob_get_clean();
    }
    return $content;
}

function chunked_paginate($ds, $rule) {
    $limit = 20;
    if (!$page) {
        $page = 1;
    }
    $all = query($ds, $rule);
    $total = count($all);
    $totalpages = ceil($total / $limit);
    foreach (range(1, $totalpages) as $page) {
        $offset = ($page - 1) * $limit;
        $res = array_slice($all, $offset, $limit);
        $info = ['total' => $total, 'totalpages' => $totalpages, 'page' => $page,
            'limit' => $limit, 'real' => count($res),
            'prev' => ($page - 1) ?: null,
            'next' => (($page + 1) <= $totalpages) ?: null
        ];
        yield [$res, $info];
    }
}
function db_paginate($db, $rule, $page = 1) {
    $limit = 20;
    $all = [];
    if (!$page) {
        $page = 1;
    }
    //print $rule;
    foreach ($db as $id => $row) {
        // print $row['_type'];
        if ($row['_type'] == $rule) {
            $all[] = $row;
        }
    }

    $total = count($all);
    $totalpages = ceil($total / $limit);
    $offset = ($page - 1) * $limit;
    dbg('paginate', $page, $offset);
    $res = array_slice($all, $offset, $limit);
    $info = ['total' => $total, 'totalpages' => $totalpages, 'page' => $page,
        'limit' => $limit, 'real' => count($res),
        'prev' => ($page - 1) ?: null,
        'next' => (($page + 1) <= $totalpages) ?: null
    ];
    return [$res, $info];
}

function paginate($how = null) {
    static $rules;
    if (!is_null($how)) {
        // reset
        if ($how == '-') {
            $rules = null;
        }
        $rules = $how;
    }
    return $rules;
}

function process_template_data($data, $path) {
    global $templates;
    $file_template = $templates[$data['_type']]['template'];
    extract($data);
    ob_start();
    include $file_template . '.html';
    $content = ob_get_clean();
    $layout = layout();
    if ($layout) {
        ob_start();
        include 'templates/__' . $layout . '.html';
        $content = ob_get_clean();
    }
    write($content, $path);
}

function layout($name = null) {
    static $layout = null;
    if (!is_null($name)) {
        // reset layout name
        if ($name == '-') {
            $layout = null;
        }
        $layout = $name;
    }
    return $layout;
}

function write($content, $path) {
    $file = __DIR__ . '/dist/' . $path . '/index.html';
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($file, $content);
}

function slugify($gen, $text, $remove_stop_words = false) {
    if ($remove_stop_words) {
        $text = remove_stop_words($text);
    }

    $text = $gen->generate($text);
    $text = substr($text, 0, 60);
    return $text;
}
function remove_stop_words($text) {
    $stops = ['a', 'the', 'and', 'ein', 'eine', 'der', 'die', 'das', 'und'];
    return preg_replace('/\b(' . join('|', $stops) . ')\b/', '', $text);
}
