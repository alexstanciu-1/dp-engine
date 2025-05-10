<?php

if (file_exists(__DIR__."/config.php"))
	require_once __DIR__ . "/config.php";

# go into the app instance's dir
chdir(dirname(__DIR__));
require_once '../../libs/compiler/main.php';

