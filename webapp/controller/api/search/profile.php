<?php

$json = [
    'success'  => (bool) false,
    'total'    => (int) 0,
    'page'     => (int) 1,
    'message'  => (string) _('Internal server error!'),
    'profiles' => (array) [],
];

// Prepare request
$q  = isset($_GET['q']) ? sanitizeSearchQuery($_GET['q']) : '';
$p  = isset($_GET['p']) && $_GET['p'] >= 1 ? (int) $_GET['p'] : 1;

$lf = isset($_GET['lf']) ? sanitizeRequest($_GET['lf']) : '';
$pr = isset($_GET['pr']) ? sanitizeRequest($_GET['pr']) : '';
$ps = isset($_GET['ps']) ? sanitizeRequest($_GET['ps']) : '';

$s  = isset($_GET['s']) && in_array($_GET['s'], ['online','added']) ? sanitizeRequest($_GET['s']) : '';
$o  = isset($_GET['o']) && in_array($_GET['o'], ['asc','desc']) ? sanitizeRequest($_GET['o']) : '';
$m  = isset($_GET['m']) && in_array($_GET['m'], ['true','false']) ? sanitizeRequest($_GET['m']) : '';

$tor = isset($_GET['tor']) && in_array($_GET['tor'], ['true','false']) ? sanitizeRequest($_GET['tor']) : '';

// Preset defaults
$total         = 0;
$profiles      = [];

// If search query and sort order not provided sort by newest
if (!$q && !$s && !$o) {
     $s = 'added';
     $o = 'desc';
}

// Set location filter
$filters = [];

if ($m) {
    $filters['m'] = $m;
}

if ($tor) {
    $filters['tor'] = $tor;
}

if ($lf) {
    $filters['lf'] = explode('|', $lf);
}

if ($ps) {
    $filters['ps'] = explode('|', $ps);
}

if ($pr) {
    $filters['pr'] = explode('|', $pr);
}

foreach ($modelSphinx->search('profile', $q, ($p - 1) * SEARCH_PROFILES_LIMIT, SEARCH_PROFILES_LIMIT, $filters, $s, $o) as $value) {
    $online = (time() - $value['online'] < PROFILE_ONLINE_TIME);
    $profiles[] = [
        'peerId'           => (string) $value['peerid'],
        'avatar'           => (string) $value['avatarhashmedium'],
        'name'             => (string) formatText($value['name']),
        'available'        => (bool) $value['updated'],
        'shortDescription' => $value['updated'] ? formatText($value['shortdescription'], false, true) : _('Some info can\'t be included because it is not indexed...'),
        'online'           => (array) [
            'status' => $online ? 'online' : ($value['online'] ? 'active' : 'passive'),
            'text'   => $online ? _('Online') : ($value['online'] ? sprintf(_('Active %s'),  timeLeft($value['online'])) : _('Passive'))
        ],
        'nsfw'            => (array) [
            'status'      => $value['blocked'] ? true : (bool) $value['nsfw'],
            'text'        => $value['blocked'] || $value['nsfw'] ? _('Content for adults or prohibited by law in your country') : _('Safe content'),
        ],
    ];
}

$total = $modelSphinx->getTotalFound();

$json = [
    'success'  => (bool) true,
    'page'     => (int) $p,
    'total'    => (int) $total,
    'message'  => $profiles ? sprintf(_('%s %s'), $total, plural($total, [_('profile found!'), _('profiles found!'), _('profiles found!')])) : _('Profiles not found!'),
    'profiles' => (array) $profiles,
];

header('Content-Type: application/json');
echo json_encode($json);
