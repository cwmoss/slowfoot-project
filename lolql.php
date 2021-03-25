<?php
/*

lolql - lowlevel query language

make queries easy & keep it simple
*/

namespace lolql;

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
    $s_fn = build_order_fun('firstname, familyname desc');

    if ($s_fn) {
        dbg('... query sorting..');
        // usort($rs, build_sorter($skey));
        usort($rs, $s_fn);
    }
    return $rs;
}

function parse($string) {
    $string = normalize($string);
    if (!$string) {
        // no string, no data
        return [];
    }
    $parts = parse_parentheses($string);
    $parts = array_reduce(array_chunk($parts, 2), function ($res, $kv) {
        $res[trim($kv[0])] = $kv[1];
        return $res;
    }, []);
    $qk = array_key_first($parts);
    print_r($parts);
    print_r(parse_condition($parts[$qk][1][0]));
    // \dbg('first key', $qk, '😂');
    $q = array_map_recursive(fn ($it) => parse_condition($it), $parts[$qk]);
    //array_walk_recursive($parts[$qk], function (&$val, $idx) {
    //    $val = parse_condition($val);
    //});
    //print_r($parts[$qk]);

    //print_r($q);
    if (!($qk == '*' || $qk == '😂')) {
        array_unshift(
            $q,
            ['l' => ['t' => 'n', 'v' => '_type'],
                'o' => '==',
                'r' => ['t' => 'v', 'v' => $qk],
                'x' => '&&'
            ]
        );
    }
    $order = build_order_fun($parts['order'][0]);
    return ['q' => $q, 'order' => $order, 'limit' => $parts['limit'][0]];
}

function build_order_fun($order) {
    $orders = parse_order($order);
    if (!$order) {
        return null;
    }
    $os = [];
    foreach ($orders as $k => $o) {
        //$key = $dir = $cmp = null;
        // keys must start with 0, 1, 2...
        list($key, $dir, $cmp) = array_merge($o);
        //print "key, $key";
        if ($dir && ($dir != 'asc' && $dir != 'desc')) {
            $cmp = $dir;
            $dir = 'asc';
        } elseif (!$dir) {
            $dir = 'asc';
        }
        $os[] = [
            'k' => $key,
            'd' => $dir,
            'c' => $cmp
        ];
    }
    $coll = collator_create('de_DE');
    return function ($a, $b) use ($os, $coll) {
        foreach ($os as $order) {
            //$cmp = 'strnatcasecmp';
            $cmp = 'collator_compare';
            $r = $cmp($coll, $a[$order['k']], $b[$order['k']]);
            if ($r) {
                return $order['d'] == 'desc' ? (-1 * $r) : $r;
            }
        }
        return 0;
        //return strnatcmp($a[$key], $b[$key]);
    };
}

function xxxbuild_sorter($key) {
    return function ($a, $b) use ($key) {
        return strnatcmp($a[$key], $b[$key]);
    };
}

function parse_order($order) {
    if (!$order) {
        return [];
    }
    return array_map('\lolql\words', explode(',', $order));
}
function words($string) {
    return array_filter(explode(' ', $string), 'trim');
}
/**
     * Parse a string into an array.
     *
*/
// https://stackoverflow.com/questions/196520/php-best-way-to-extract-text-within-parenthesis
// https://stackoverflow.com/questions/2650414/php-curly-braces-into-array

// @rodneyrehm
// http://stackoverflow.com/a/7917979/99923

function parse_parentheses($string) {
    if ($string[0] == '(') {
        // killer outer parens, as they're unnecessary
        $string = substr($string, 1, -1);
    }

    $buffer_start = null;
    $position = null;
    $current = [];
    $stack = [];

    $push = function (&$current, $string, &$buffer_start, $position) {
        if ($buffer_start === null) {
            return;
        }
        $buffer = substr($string, $buffer_start, $position - $buffer_start);
        $buffer_start = null;
        $current[] = $buffer;
    };

    for ($position = 0; $position < strlen($string); $position++) {
        switch ($string[$position]) {
            case '(':
                $push($current, $string, $buffer_start, $position);
                // push current scope to the stack an begin a new scope
                array_push($stack, $current);
                $current = [];
                break;

            case ')':
                $push($current, $string, $buffer_start, $position);
                // save current scope
                $t = $current;
                // get the last scope from stack
                $current = array_pop($stack);
                // add just saved scope to current scope
                $current[] = $t;
                break;
           /*
            case ' ':
                // make each word its own token
                $this->push();
                break;
            */
            default:
                // remember the offset to do a string capture later
                // could've also done $buffer .= $string[$position]
                // but that would just be wasting resources…
                if ($buffer_start === null) {
                    $buffer_start = $position;
                }
        }
    }
    // catch any trailing text
    if ($buffer_start < $position) {
        $push($current, $string, $buffer_start, $position);
    }
    return $current;
}

function normalize($string) {
    return join(' ', array_filter(
        explode("\n", $string),
        fn ($line) => trim($line)[0] != '#'
    ));
}

function parse_condition($string) {
    $t = token_get_all('<?' . $string . '?>');
    $t = compact_tokens($t);
    //print_r($t);
    $t = combine_tokens($t);
    //print_r($t);
    return $t;
}

function combine_tokens($tokens) {
    $buffer = ['l' => ['t' => null, 'v' => []], 'o' => null, 'r' => ['t' => null, 'v' => []], 'x' => null];
    $lr = 'l';
    $res = [];
    foreach ($tokens as $item) {
        if ($item == '&&' || $item == '||') {
            $buffer['x'] = $item;
            $res[] = $buffer;
            $buffer = ['l' => ['t' => null, 'v' => []], 'o' => null, 'r' => ['t' => null, 'v' => []], 'x' => null];
            $lr = 'l';
            continue;
        }
        if (in_array($item, ['==', 'in', '!=', '>', '<', '<=', '>='])) {
            $buffer['o'] = $item;
            $lr = 'r';
        } elseif ($item[0] == '"') {
            $buffer[$lr]['v'][] = trim($item, '"');
            if (!$buffer[$lr]['t']) {
                $buffer[$lr]['t'] = 'v';
            }
        } elseif (!in_array($item, ['[', ']', '.', ','])) {
            $buffer[$lr]['v'][] = $item;
            if (!$buffer[$lr]['t']) {
                $buffer[$lr]['t'] = 'n';
            }
        }
    }
    if ($buffer && $buffer['o']) {
        $res[] = $buffer;
    }
    return $res;
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

function yyarray_map_recursive($fn, $arr) {
    return array_map(function ($item) use ($fn) {
        return is_array($item) ? array_map($fn, $item) : $fn($item);
    }, $arr);
}

function array_map_recursive($callback, $array) {
    $func = function ($item) use (&$func, &$callback) {
        return is_array($item) ? array_map($func, $item) : call_user_func($callback, $item);
    };

    return array_map($func, $array);
}
