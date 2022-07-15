<?php

// Default response
$json = [
    'success'   => (bool) false,
    'total'     => (int) 0,
    'countries' => (array) [],
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

if ($pr) {
    $filters['pr'] = explode('|', $pr);
}

if ($ps) {
    $filters['ps'] = explode('|', $ps);
}

if ($lt) {
    $filters['lt'] = explode('|', str_replace('-', '_', $lt));
}

if ($lc) {
    $filters['lc'] = explode('|', str_replace('-', '_', $lc));
}

if ($lf) {
    $filtersLf = explode('|', strtolower($lf));
} else {
    $filtersLf = [];
}

// Get countries list
$countriesExists = 0;
$countriesActive = 0;
$countriesTotal  = 0;
$countries       = [];

foreach ($modelLocation->getCountries() as $country) {

    $filters['lf'] = [$country['codeIso2']];

    $total = 0;
    if ($results = $modelSphinx->search($t, $q, 0, 1, $filters)) {
        $total = $modelSphinx->getTotalFound();
        $countriesExists = $countriesExists + $total;
    }

    $code = strtolower($country['codeIso2']);

    if (in_array($code, $filtersLf)) {
        $active = true;
        $countriesActive++;
    } else {
        $active = false;
    }

    $countriesTotal++;

    $countries[] = [
        'name'   => (string) $country['name'],
        'code'   => (string) $code,
        'flag'   => file_exists(sprintf('%s/public/image/flag/4x3/%s.svg', PROJECT_DIR, $code)) ? sprintf('image/flag/4x3/%s.svg', $code) : false,
        'total'  => (int) $total,
        'active' => (bool) $active,
    ];
}

$json = [
    'success'   => (bool) true,
    'exists'    => (int) $countriesExists,
    'total'     => (int) $countriesTotal,
    'tor'       => (string) $tor,
    'active'    => [
        'total' => (int) $countriesActive,
        'text'  => ($countriesActive ? $countriesActive . ' ' . plural($countriesActive, [_('country'), _('countries'), _('countries ')]) : _('All regions')) . ($tor == false ? false : _(' but TOR')),
    ],
    'countries' => (array) $countries,
];

// Output
header('Content-Type: application/json');
echo json_encode($json);
