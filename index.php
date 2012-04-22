<?php

$start = microtime(TRUE);

require('app/webroot/index.php');

$end = microtime(TRUE);
$time = $end - $start;

echo 'Rendering time: ' . sprintf('%.4f', $time) . ' s<br />';
