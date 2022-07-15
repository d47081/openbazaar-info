<?php

$json = [
    'success'  => (bool) false,
    'page'     => (int) 1,
    'total'    => (int) 0,
    'message'  => (string) _('Internal server error!'),
    'listings' => (array) [],
];

$page = isset($_GET['page']) && $_GET['page'] >= 1 ? (int) $_GET['page'] : 1;

if (isset($_GET['peerId']) && $profileId = $modelProfile->getProfileIdByPeerId(sanitizeRequest($_GET['peerId']))) {

    $listings = [];
    if ($profileListings = $modelListing->getProfileListings($profileId, ($page - 1) * PROFILE_LISTINGS_LIMIT, PROFILE_LISTINGS_LIMIT)) {

        $rates = [];
        foreach ($modelCurrency->getLastRates() as $rate) {
            $rates[$rate['code']] = [
                'type'   => $rate['type'],
                'rate'   => $rate['rate'],
                'symbol' => $rate['symbol'],
            ];
        }

        foreach ($profileListings as $value) {
            $listings[] = [
                'hash'            => (string) $value['hash'],
                'slug'            => (string) $value['slug'],
                'peerId'          => (string) $value['peerId'],
                'title'           => (string) formatText($value['title']),
                'image'           => (string) $value['image'],
                'available'       => (bool) ($value['updatedIpns'] || $value['updatedIpfs']),
                'description'     => $value['updatedIpns'] || $value['updatedIpfs'] ? formatText($value['description'], false, true) : _('Some info can\'t be included because it is not indexed...'),
                'price'           => (string) formatPrice($rates, $value['price'], $value['code']),
                'condition'       => (string) formatListingCondition($value['condition']),
                'contractType'    => (string) formatListingContractType($value['contractType']),
                'payment'         => (array) [
                    'security' => $value['ratingAverage'] > 0 && $value['ratingAverage'] < 3 ? 'danger' : ($value['moderators'] ? 'moderated' : 'direct'),
                    'text'     => $value['ratingAverage'] > 0 && $value['ratingAverage'] < 3 ? sprintf(_('Low rating (%s/%s)'), $value['ratingAverage'], $value['ratingCount'])
                                                                                             : ($value['moderators'] ? sprintf(_('Moderated payment (%s %s)'), (int) $value['moderators'], plural((int) $value['moderators'], ['variant', 'variants', 'variants']))
                                                                                                                     : _('Direct payment')),
                ],
                'nsfw'            => [
                    'status'      => (bool) $value['nsfw'],
                    'text'        => $value['nsfw'] ? _('Content for adults or prohibited by law in your country') : _('Safe content'),
                ],
            ];
        }
    }

    $total = $modelListing->getTotalProfileListings($profileId);

    $json  = [
        'success'  => (bool) true,
        'page'     => (int) $page,
        'total'    => (int) $total,
        'message'  => $listings ? sprintf(_('%s %s'), $total, plural($total, [_('listing found!'), _('listings found!'), _('listings found!')])) : _('Listings not found!'),
        'listings' => (array) $listings,
    ];

} else {
    $json = [
        'success'  => (bool) false,
        'page'     => (int) $page,
        'total'    => (int) 0,
        'message'  => (string) _('Valid PeerId required!'),
        'listings' => (array) [],
    ];
}

header('Content-Type: application/json');
echo json_encode($json);
