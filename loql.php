<?php
/*

loql - low query language

make queries easy and keep it simple
*/

function logl_query($ds, $filter) {
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
