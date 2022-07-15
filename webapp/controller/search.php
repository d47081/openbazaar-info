<?php

if (isset($_GET['q']) && $_GET['q']) {
  $_title = sprintf(_('%s - Search - %s'), formatText((sanitizeRequest($_GET['q']))), PROJECT_NAME);
} else {
  $_title = sprintf(_('Search - %s'), PROJECT_NAME);
}

$_styles = [
    'css/bootstrap-toggle.min.css',
];

$_scripts = [
    'js/search.min.js',
    'js/bootstrap-toggle.min.js',
];

switch (isset($_GET['t']) ? (sanitizeRequest($_GET['t'])) : '') {
    case 'profile':
        require(PROJECT_DIR . '/view/search/profile.phtml');
    break;
    default:
        require(PROJECT_DIR . '/view/search/listing.phtml');
}
