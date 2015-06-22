<?php

$test = array("","","","","あああ");

var_dump($test);

if (empty($test)) {
    echo "空だよ";
}

if (!$test) {
    echo "空だよ2";
}

if (!$test[0]) {
    echo "空だよ3";
}

if ($test[0] = "") {
    echo "空だよ4";
}
