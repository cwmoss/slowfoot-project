<?php
/*

lolql - lowlevel query language

make queries easy and keep it simple
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

    if ($skey) {
        dbg('... sorting..');
        usort($rs, build_sorter($skey));
    }
    return $rs;
}

function parse($string) {
    $string = trim(str_replace(["\r", "\n"], '', $string));
    if (!$string) {
        // no string, no data
        return [];
    }
    $parts = parse_parentheses($string);
    $parts = array_reduce(array_chunk($parts, 2), function ($res, $kv) {
        $res[trim($kv[0])] = $kv[1];
        return $res;
    }, []);
    return $parts;
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
