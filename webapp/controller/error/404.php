<?php

header('HTTP/1.0 404 Not Found', true, 500);
$_title = sprintf(_('404 - Not Found - %s'), PROJECT_NAME);
$_scripts = [];
require(PROJECT_DIR . '/view/error/404.phtml');
