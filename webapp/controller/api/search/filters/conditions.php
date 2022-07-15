<?php

// Default response
$json = [
    'success'    => (bool) false,
    'total'      => (int) 0,
    'conditions' => (array) [],
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

if ($lt) {
    $filters['lt'] = explode('|', str_replace('-', '_', $lt));
}

if ($lf) {
    $filters['lf'] = explode('|', str_replace('-', '_', $lf));
}

if ($lc) {
    $filtersLc = explode('|', strtolower($lc));
} else {
    $filtersLc = [];
}

// Get conditions list
$conditionsList = [ 'new' => [
                        'name' => _('New'),
                        'value' => 'new',
                    ],
                    'used' => [
                        'name' => _('Used'),
                        'value' => 'used',
                    ],
                    'used_good' => [
                        'name' => _('Used, good'),
                        'value' => 'used-good',
                    ],
                    'used_excelent' => [
                        'name' => _('Used, excelent'),
                        'value' => 'used-excelent',
                    ],
                    'used_poor' => [
                        'name' => _('Used, poor'),
                        'value' => 'used-poor',
                    ],
                    'refurbished' => [
                        'name' => _('Refurbished'),
                        'value' => 'refurbished',
                    ],
                ];

$conditionsExists = 0;
$conditionsActive = 0;
$conditionsTotal  = 0;
$conditions       = [];

foreach ($conditionsList as $key => $condition) {

    $filters['lc'] = [$key];

    $total = 0;
    if ($results = $modelSphinx->search('listing', $q, 0, 1, $filters)) {
        $total = $modelSphinx->getTotalFound();
        $conditionsExists = $conditionsExists + $total;
    }

    if (in_array($condition['value'], $filtersLc)) {
        $active = true;
        $conditionsActive++;
    } else {
        $active = false;
    }

    $conditionsTotal++;

    $conditions[] = [
        'name'   => (string) $condition['name'],
        'value'  => (string) $condition['value'],
        'total'  => (int) $total,
        'active' => (bool) $active,
    ];
}

$json = [
    'success'    => (bool) true,
    'exists'     => (int) $conditionsExists,
    'total'      => (int) $conditionsTotal,
    'active'     => (array) [
        'total'  => (int) $conditionsActive,
        'text'   => $conditionsActive ? $conditionsActive . ' ' . plural($conditionsActive, [_('condition'), _('conditions'), _('conditions ')]) : _('Condition'),
    ],
    'conditions' => (array) $conditions,
];

// Output
header('Content-Type: application/json');
echo json_encode($json);
