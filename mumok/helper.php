<?php

function split_tags($tags) {
    return array_filter(explode(',', $tags), 'trim');
}
