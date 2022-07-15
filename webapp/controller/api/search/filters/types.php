<?php

// Default response
$json = [
    'success' => (bool) false,
    'total'   => (int) 0,
    'types'   => (array) [],
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

if ($lf) {
    $filters['lf'] = explode('|', str_replace('-', '_', $lf));
}

if ($lc) {
    $filters['lc'] = explode('|', str_replace('-', '_', $lc));
}

if ($lt) {
    $filtersLt = explode('|', strtolower($lt));
} else {
    $filtersLt = [];
}

// Get types list
$typesList =  ['service' => [
                  'name'  => _('Service'),
                  'value' => 'service',
              ],
              'physical_good' => [
                  'name'  => _('Physical'),
                  'value' => 'physical-good',
              ],
              'digital_good' => [
                  'name'  => _('Digital'),
                  'value' => 'digital-good',
              ],
              'cryptocurrency' => [
                  'name'  => _('Crypto'),
                  'value' => 'cryptocurrency',
              ],
          ];

$typesExists = 0;
$typesActive = 0;
$typesTotal  = 0;
$types       = [];

foreach ($typesList as $key => $type) {

    $filters['lt'] = [$key];

    $total = 0;
    if ($results = $modelSphinx->search('listing', $q, 0, 1, $filters)) {
        $total = $modelSphinx->getTotalFound();
        $typesExists = $typesExists + $total;
    }

    if (in_array($type['value'], $filtersLt)) {
        $active = true;
        $typesActive++;
    } else {
        $active = false;
    }

    $typesTotal++;

    $types[] = [
        'name'   => (string) $type['name'],
        'value'  => (string) $type['value'],
        'total'  => (int) $total,
        'active' => (bool) $active,
    ];
}

$json = [
    'success'    => (bool) true,
    'exists'     => (int) $typesExists,
    'total'      => (int) $typesTotal,
    'active'     => (array) [
        'total'  => (int) $typesActive,
        'text'   => $typesActive ? $typesActive . ' ' . plural($typesActive, [_('type'), _('types'), _('types ')]) : _('Type'),
    ],
    'types' => (array) $types,
];

// Output
header('Content-Type: application/json');
echo json_encode($json);
