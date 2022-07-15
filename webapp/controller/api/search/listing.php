<?php

// Default response
$json = [
    'success'  => (bool) false,
    'page'     => (int) 1,
    'total'    => (int) 0,
    'message'  => (string) _('Internal server error!'),
    'listings' => (array) [],
];

// Prepare request
$q  = isset($_GET['q'])  ? sanitizeSearchQuery($_GET['q']) : '';
$p  = isset($_GET['p']) && $_GET['p'] >= 1 ? (int) $_GET['p'] : 1;

$lf = isset($_GET['lf']) ? sanitizeRequest($_GET['lf']) : '';
$pr = isset($_GET['pr']) ? sanitizeRequest($_GET['pr']) : '';
$ps = isset($_GET['ps']) ? sanitizeRequest($_GET['ps']) : '';
$lt = isset($_GET['lt']) ? sanitizeRequest($_GET['lt']) : '';
$lc = isset($_GET['lc']) ? sanitizeRequest($_GET['lc']) : '';
$id = isset($_GET['id']) ? sanitizeRequest($_GET['id']) : '';

$m  = isset($_GET['m']) && in_array($_GET['m'], ['true','false']) ? sanitizeRequest($_GET['m']) : '';

$s  = isset($_GET['s']) && in_array($_GET['s'], ['online','added','price']) ? sanitizeRequest($_GET['s']) : '';
$o  = isset($_GET['o']) && in_array($_GET['o'], ['asc','desc']) ? sanitizeRequest($_GET['o']) : '';

$tor = isset($_GET['tor']) && in_array($_GET['tor'], ['true','false']) ? sanitizeRequest($_GET['tor']) : '';

// Preset defaults
$total         = 0;
$listings      = [];

// If search query and sort order not provided sort by newest
if (!$q && !$s && !$o) {
     $s = 'added';
     $o = 'desc';
}

// Get rates
$rates = [];
foreach ($modelCurrency->getLastRates() as $rate) {
    $rates[$rate['code']] = [
        'type'   => $rate['type'],
        'rate'   => $rate['rate'],
        'symbol' => $rate['symbol'],
    ];
}

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

if ($lf) {
    $filters['lf'] = explode('|', $lf);
}

if ($ps) {
    $filters['ps'] = explode('|', $ps);
}

if ($pr) {
    $filters['pr'] = explode('|', $pr);
}

if ($lt) {
    $filters['lt'] = explode('|', str_replace('-', '_', $lt));
}

if ($lc) {
    $filters['lc'] = explode('|', str_replace('-', '_', $lc));
}

// Begin search
foreach ($modelSphinx->search('listing', $q, ($p - 1) * SEARCH_LISTINGS_LIMIT, SEARCH_LISTINGS_LIMIT, $filters, $s, $o) as $value) {
    $online = (time() - $value['online'] < PROFILE_ONLINE_TIME);
    $listings[] = [
        'hash'            => (string) $value['hash'],
        'slug'            => (string) $value['slug'],
        'peerId'          => (string) $value['peerid'],
        'title'           => (string) formatText($value['title']),
        'image'           => (string) $value['image'],
        'available'       => (bool) ($value['updatedipns'] || $value['updatedipfs']),
        'description'     => $value['updatedipns'] || $value['updatedipfs'] ? formatText($value['description'], false, true) : _('Some info can\'t be included because it is not indexed...'),
        'price'           => (string) formatPrice($rates, $value['price'], $value['code']),
        'condition'       => (string) formatListingCondition($value['lc']),
        'contractType'    => isset($value['lt']) ? formatListingContractType($value['lt']) : '',
        'payment'         => (array) [
            'security' => $value['ratingaverage'] > 0 && $value['ratingaverage'] < 3 ? 'danger' : ($value['moderators'] ? 'moderated' : 'direct'),
            'text'     => $value['ratingaverage'] > 0 && $value['ratingaverage'] < 3 ? sprintf(_('Low rating (%s/%s)'), $value['ratingaverage'], $value['ratingcount'])
                                                                                         : ($value['moderators'] ? sprintf(_('Moderated payment (%s %s)'), (int) $value['moderators'], plural((int) $value['moderators'], ['variant', 'variants', 'variants']))
                                                                                                                   : _('Direct payment')),
        ],
        'profile'         => [
            'peerId' => (string) $value['peerid'],
            'name'   => (string) $value['name'],
            'rating' => (array) [
                'average' => (int) $value['ratingaverage'],
                'count'   => (int) $value['ratingcount'],
            ],
            'online' => [
                'status' => $online ? 'online' : ($value['online'] ? 'active' : 'passive'),
                'text'   => $online ? _('Online') : ($value['online'] ? sprintf(_('Active %s'),  timeLeft($value['online'])) : _('Passive'))
            ],
        ],
        'nsfw'            => [
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
    'message'  => $listings ? sprintf(_('%s %s'), $total, plural($total, [_('listing found!'), _('listings found!'), _('listings found!')])) : _('Listings not found!'),
    'listings' => (array) $listings,
];

header('Content-Type: application/json');
echo json_encode($json);
