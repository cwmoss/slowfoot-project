<?php

function dev() {
    print '+++ including dev';
    include 'dev.php';
}

function build() {
    include 'command_build.php';
}

// dev();
build();
