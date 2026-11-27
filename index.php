<?php

$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$base   = rtrim(dirname($script), '/');
header('Location: ' . $base . '/member/index.php');
exit;
