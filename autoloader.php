<?php

$fnc = function ($fullClassName) {
    require str_replace('\\', '/', $fullClassName).'.php';
};

spl_autoload_register($fnc);

