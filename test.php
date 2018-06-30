<?php

require_once __DIR__ . "/MP4Resolve.php";

use VideoResolve\MP4Resolve;

$m = new MP4Resolve(__DIR__ . "/test.mp4");

var_dump($m->getExt());
