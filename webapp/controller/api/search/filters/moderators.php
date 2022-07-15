<?php

// Default response
$json = [
    'success' => (bool) false,
    'active'  => (bool) false,
    'total'   => (int) 0,
    'text'    => (string) _('Internal server error!'),
];

// Prepare request
$q  = isset($_GET['q']) ? sanitizeSearchQuery($_GET['q']) : '';

$lf = isset($_GET['lf']) ? sanitizeRequest($_GET['lf']) : '';
$pr = isset($_GET['pr']) ? sanitizeRequest($_GET['pr']) : '';
$ps = isset($_GET['ps']) ? sanitizeRequest($_GET['ps']) : '';
$id = isset($_GET['id']) ? sanitizeRequest($_GET['id']) : '';

$m  = isset($_GET['m']) && in_array($_GET['m'], ['true','false']) ? sanitizeRequest($_GET['m']) : '';

$tor = isset($_GET['tor']) && in_array($_GET['tor'], ['true','false']) ? sanitizeRequest($_GET['tor']) : '';

// Set filters
$filters = [];

if ($tor) {
    $filters['tor'] = $tor;
}

if ($id) {
    $filters['id'] = explode('|', $id);
}

if ($ps) {
    $filters['ps'] = explode('|', $ps);
}

if ($pr) {
    $filters['pr'] = explode('|', $pr);
}

if ($lf) {
    $filters['lf'] = explode('|', str_replace('-', '_', $lf));
}

$filters['m'] = 'true';

if ($results = $modelSphinx->search('profile', $q, 0, 1, $filters)) {
    $total   = $modelSphinx->getTotalFound();
} else {
    $total = 0;
}

$json = [
    'success' => (bool) true,
    'total'   => (int) $total,
    'active'  => $m == 'true' ? true : false,
    'text'    => (string) $total . ' ' . plural($total, [_('moderator'), _('moderators'), _('moderators ')]),
];

// Output
header('Content-Type: application/json');
echo json_encode($json);
