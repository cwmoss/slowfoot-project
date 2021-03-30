<?php

function split_tags($tags) {
    return array_filter(array_map('trim', preg_split('/[,;]/', $tags)), 'trim');
}
