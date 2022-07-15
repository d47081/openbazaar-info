<?php

$json = [
    'name'  => PROJECT_NAME,
    'logo'  => PROJECT_LOGO,
    'links' => [
        'self' => PROJECT_HOST,
        'search' => PROJECT_HOST,
        'listings' => PROJECT_HOST,
        //'listings' => PROJECT_HOST,
        //'vendors' => PROJECT_HOST,
    ],
    'sortBy' => [
        'relevance' => [
            'label'   => _('Relevance'),
            'selected' => false,
            'default'  => false,
        ],
        'price-asc' => [
            'label'   => _('Price up'),
            'selected' => false,
            'default'  => false,
        ],
        'price-desc' => [
            'label'   => _('Price down'),
            'selected' => false,
            'default'  => false,
        ],
        'added-desc' => [
            'label'   => _('Newest'),
            'selected' => true,
            'default'  => true,
        ],
        'added-asc' => [
            'label'   => _('Oldest'),
            'selected' => false,
            'default'  => false,
        ],
        'online-desc' => [
            'label'   => _('Online'),
            'selected' => false,
            'default'  => false,
        ],
    ],
    'results' => [
        'total'     => (int) $modelListing->getTotalListings(),
        'morePages' => false,
        'results'   => [],
    ],
];

// Prepare request
$q  = isset($_GET['q'])  ? sanitizeSearchQuery($_GET['q']) : '';
$p  = isset($_GET['p']) && $_GET['p'] >= 0 ? (int) $_GET['p'] : 0;

$lf = isset($_GET['lf']) ? sanitizeRequest($_GET['lf']) : '';
$pr = isset($_GET['pr']) ? sanitizeRequest($_GET['pr']) : '';
$ps = isset($_GET['ps']) ? sanitizeRequest($_GET['ps']) : '';
$lt = isset($_GET['lt']) ? sanitizeRequest($_GET['lt']) : '';
$lc = isset($_GET['lc']) ? sanitizeRequest($_GET['lc']) : '';
$id = isset($_GET['id']) ? sanitizeRequest($_GET['id']) : '';

$m  = isset($_GET['m']) && in_array($_GET['m'], ['true','false']) ? sanitizeRequest($_GET['m']) : '';

$tor = isset($_GET['tor']) && in_array($_GET['tor'], ['true','false']) ? sanitizeRequest($_GET['tor']) : '';

$s  = isset($_GET['s']) && in_array($_GET['s'], ['online','added','price']) ? sanitizeRequest($_GET['s']) : '';
$o  = isset($_GET['o']) && in_array($_GET['o'], ['asc','desc']) ? sanitizeRequest($_GET['o']) : '';


if (isset($_GET['sortBy']) && in_array($_GET['sortBy'], ['relevance',
                                                         'price-asc',
                                                         'price-desc',
                                                         'added-asc',
                                                         'added-desc',
                                                         'online-desc'])) {

     switch ($_GET['sortBy']) {
        case 'price-asc':
            $s = 'price';
            $o = 'asc';
            $json['sortBy']['price-asc']['selected'] = true;
        break;
        case 'price-desc':
            $s = 'price';
            $o = 'desc';
            $json['sortBy']['price-desc']['selected'] = true;
        break;
        case 'added-asc':
            $s = 'added';
            $o = 'asc';
            $json['sortBy']['added-asc']['selected'] = true;
        break;
        case 'added-desc':
            $s = 'added';
            $o = 'desc';
            $json['sortBy']['price-desc']['selected'] = true;
        break;
        case 'online-desc':
            $s = 'online';
            $o = 'desc';
            $json['sortBy']['online-desc']['selected'] = true;
        break;
        default:
            $s = 'added';
            $o = 'desc';
            $json['sortBy']['relevance']['selected'] = true;
     }
}

// If search query and sort order not provided sort by newest
if (!$q && !$s && !$o) {
     $s = 'added';
     $o = 'desc';
}

$resultsTotal = 0;
$results = [];

// Set location filter
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

foreach ($modelSphinx->search('listing', $q, $p * SEARCH_LISTINGS_LIMIT, SEARCH_LISTINGS_LIMIT, $filters, $s, $o) as $value) {

    $results[] = [
        'type' => 'listing',
        'relationships' => [
            'vendor' => [
                'data' => [
                    'peerID'       => $value['peerid'],
                    'handle'       => $value['handle'],
                    'name'         => $value['name'],
                    'avatarHashes' => [
                        'tiny'     => $value['avatarhashtiny'],
                        'small'    => $value['avatarhashsmall'],
                        'medium'   => $value['avatarhashmedium'],
                        'large'    => $value['avatarhashlarge'],
                        'original' => $value['avatarhashoriginal'],
                    ],
                ],
            ],
            'moderators' => explode(',', $value['moderators']),
        ],
        'data' => [
            'hash'         => $value['hash'],
            'slug'         => $value['slug'],
            'title'        => $value['title'],
            'tags'         => $value['tags'] ? explode(',', $value['tags']) : [],
            'categories'   => $value['categories'] ? explode(',', $value['categories']) : [],
            'contractType' => $value['lt'],
            'description'  => $value['description'],
            'thumbnail'    => [
                'tiny'     => $value['image'],
                'small'    => $value['image'],
                'medium'   => $value['image'],
                'large'    => $value['image'],
                'original' => $value['image'],
            ],
            //'language'     => '',
            'amount'       => (string) $value['price'],
            'price'        => [ // @TODO deprecated
                'currencyCode' => (string) $value['code'],
                'amount'       => (string) $value['price'],
                'modifier'     => (float) $value['pricemodifier'],
            ],
            'bigPrice'        => [
                'currencyCode' => (string) $value['code'],
                'amount'       => (string) $value['price'],
                'modifier'     => (float) $value['pricemodifier'],
                'divisibility' => (int) $value['divisibility'],
            ],
            'currency'        => [
                'code'         => (string) $value['code'],
                'divisibility' => (int) $value['divisibility'],
            ],
            'nsfw'          => $value['blocked'] ? true : (bool) $value['nsfw'],
            'averageRating' => (float) $value['ratingaverage'],
            'ratingCount'   => (float) $value['ratingcount'],
        ],
    ];

    $resultsTotal++;
}

$total = $modelSphinx->getTotalFound();

$json['results']['total']     = (int) $total;
$json['results']['results']   = $results;
$json['results']['morePages'] = $p * SEARCH_LISTINGS_LIMIT + $resultsTotal < $total ? true : false;


header('Content-Type: application/json');
echo json_encode($json);
