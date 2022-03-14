<?php

include('engine.php');

$Engine->serve('template/assets');
$Engine->serve('template/css');
$Engine->serve('template/js');

$path = $Engine->currentPath();

include("template/main.php");