<?php

header('HTTP/1.0 500 Internal Server Error', true, 500);
$_title = sprintf(_('500 - Error - %s'), PROJECT_NAME);
$_scripts = [];
require(PROJECT_DIR . '/view/error/500.phtml');
