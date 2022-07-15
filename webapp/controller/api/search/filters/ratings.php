<?php

// Default response
$json = [
    'success' => (bool) false,
    'total'   => (int) 0,
    'ratings' => (array) [],
];

// Prepare request
$q  = isset($_GET['q'])  ? sanitizeSearchQuery($_GET['q']) : '';

$lf = isset($_GET['lf']) ? sanitizeRequest($_GET['lf']) : '';
$pr = isset($_GET['pr']) ? sanitizeRequest($_GET['pr']) : '';
$ps = isset($_GET['ps']) ? sanitizeRequest($_GET['ps']) : '';
$lt = isset($_GET['lt']) ? sanitizeRequest($_GET['lt']) : '';
$lc = isset($_GET['lc']) ? sanitizeRequest($_GET['lc']) : '';
$id = isset($_GET['id']) ? sanitizeRequest($_GET['id']) : '';

$m  = isset($_GET['m']) && in_array($_GET['m'], ['true','false']) ? sanitizeRequest($_GET['m']) : '';
$t  = isset($_GET['t']) && in_array($_GET['t'], ['listing','profile']) ? sanitizeRequest($_GET['t']) : '';

$tor = isset($_GET['tor']) && in_array($_GET['tor'], ['true','false']) ? sanitizeRequest($_GET['tor']) : '';

// Set filters
$filters = [];

if ($m) {
    $filters['m'] = $m;
}

if ($tor) {
    $filters['tor'] = $tor;
}

if ($id) {
    $filters['id'] = explode('|', $id);
}

if ($ps) {
    $filters['ps'] = explode('|', $ps);
}

if ($lf) {
    $filters['lf'] = explode('|', str_replace('-', '_', $lf));
}

if ($lc) {
    $filters['lc'] = explode('|', str_replace('-', '_', $lc));
}

if ($lt) {
    $filters['lt'] = explode('|', str_replace('-', '_', $lt));
}

if ($pr) {
    $filtersPr = explode('|', strtolower($pr));
} else {
    $filtersPr = [];
}

// Get ratings list
$ratingsList = [5, 4, 3, 2, 1];

$ratingsExists = 0;
$ratingsActive = 0;
$ratingsTotal  = 0;
$ratings       = [];

foreach ($ratingsList as $rating) {

    $filters['pr'] = [$rating];

    $total = 0;
    if ($results = $modelSphinx->search($t, $q, 0, 1, $filters)) {
        $total = $modelSphinx->getTotalFound();
        $ratingsExists = $ratingsExists + $total;
    }

    if (in_array($rating, $filtersPr)) {
        $active = true;
        $ratingsActive++;
    } else {
        $active = false;
    }

    $ratingsTotal++;

    $ratings[] = [
        'value'  => (int) $rating,
        'total'  => (int) $total,
        'active' => (bool) $active,
    ];

}

$json = [
    'success'    => (bool) true,
    'exists'     => (int) $ratingsExists,
    'total'      => (int) $ratingsTotal,
    'active'     => (array) [
        'total'  => (int) $ratingsActive,
        'text'   => $ratingsActive ? $ratingsActive . ' ' . plural($ratingsActive, [_('rating'), _('ratings'), _('ratings ')]) : _('Rating'),
    ],
    'ratings'    => (array) $ratings,
];

// Output
header('Content-Type: application/json');
echo json_encode($json);
