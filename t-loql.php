<?php

require_once 'lolql.php';
use function lolql\{parse};

$test[] = '(TOP (S (NP (PRP I)) (VP (VBP love) (NP (NP (DT a) (JJ big) (NN bed)) (PP (IN of) (NP (NNS roses))))) (. .)))';
$test[] = '😂(_type == "article" && (status != "draft" || posted_by == "importer"))  order(created_at desc) limit(11) ok ';
$test[] = 'article() order (created_at)';
$test[] = '() order (created_at)';
$test[] = '*() order (created_at)';

$test[] = ' order (created_at)';

//$p = new ParensParser();
//$result = $p->parse($test1);
//print_r($result);
//print_r(parse($test1));

//$p = new ParensParser();
//$result = $p->parse($test2);
//print_r($result);
//print_r(parse($test2));

//print_r(parse($test3));

foreach ($test as $t) {
    print "\n==\n$t";
    print_r(parse($t));
}

$t = parse_condition('status.update != "draft" ||    posted_by == "importer 03 ok"');

print_r($t);

$t = parse_condition('_type ==  "article"  && tag in ["huhu", "haha"] && ');

print_r($t);

function parse_condition($string) {
    $t = token_get_all('<?' . $string . '?>');
    $t = compact_tokens($t);
    $t = divide_tokens($t);
    return $t;
}

function divide_tokens($t) {
    $buffer = ['k' => [], 'c' => null, 'v' => [], 'next' => null];
    $t = array_reduce($t, function ($res, $item) use (&$buffer) {
        if ($item == '&&' || $item == '||') {
            $buffer['next'] = $item;
            $res[] = $buffer;
            $buffer = ['k' => [], 'c' => null, 'v' => [], 'next' => null];
            return $res;
        }
        if (in_array($item, ['==', 'in', '!=', '>', '<', '<=', '>='])) {
            $buffer['c'] = $item;
        } elseif ($item[0] == '"') {
            $buffer['v'][] = $item;
        } elseif (!in_array($item, ['[', ']', '.', ','])) {
            $buffer['k'][] = $item;
        }
    }, []);
    if ($buffer && $buffer['c']) {
        $t[] = $buffer;
    }
    return $t;
}

function compact_tokens($t) {
    $t = array_map(function ($tok) {
        if (is_array($tok)) {
            return $tok[1] == '<?' || $tok[1] == '?>' ? '' : $tok[1];
        }
        return $tok;
    }, $t);
    $t = array_filter($t, 'trim');
    return $t;
}
