<?php

$json = [
    'success'    => (bool) false,
    'page'       => (int) 1,
    'total'      => (int) 0,
    'message'    => (string) _('Internal server error!'),
    'moderators' => (array) [],
];

$page = isset($_GET['page']) && $_GET['page'] >= 1 ? (int) $_GET['page'] : 1;

if (isset($_GET['hash']) && $listingId = $modelListing->getListingIdByHash(sanitizeRequest($_GET['hash']))) {

    // Get currency rates
    $rates = [];
    foreach ($modelCurrency->getLastRates() as $rate) {
        $rates[$rate['code']] = [
            'type'   => $rate['type'],
            'rate'   => $rate['rate'],
            'symbol' => $rate['symbol'],
        ];
    }

    $moderators = [];
    if ($listingModerators = $modelListing->getListingModerators($listingId, ($page - 1) * LISTING_MODERATORS_LIMIT, LISTING_MODERATORS_LIMIT)) {
        foreach ($listingModerators as $value) {
            $online = (time() - $value['online'] < PROFILE_ONLINE_TIME);
            $moderators[] = [
                'peerId'           => (string) $value['peerId'],
                'available'        => (bool) $value['updated'],
                'avatar'           => (string) $value['avatarHashMedium'],
                'name'             => (string) formatText($value['name']),
                'feeType'          => (string) formatModeratorFeeType($value['moderatorFeeType']),
                'price'            => (string) formatModeratorPrice($rates, $value['moderatorAmount'], $value['moderatorCurrencyCode'], $value['moderatorPercentage'], $value['moderatorFeeType']),
                'shortDescription' => $value['updated'] ? formatText($value['shortDescription'], false, true) : _('Some info can\'t be included because it is not indexed...'),
                'online'           => [
                    'status' => $online ? 'online' : ($value['online'] ? 'active' : 'passive'),
                    'text'   => $online ? _('Online') : ($value['online'] ? sprintf(_('Active %s'),  timeLeft($value['online'])) : _('Passive'))
                ],
                'nsfw'            => [
                    'status'      => (bool) $value['nsfw'],
                    'text'        => $value['nsfw'] ? _('Content for adults or prohibited by law in your country') : _('Safe content'),
                ],
            ];
        }
    }

    $total = $modelListing->getTotalListingModerators($listingId);

    $json = [
        'success'    => (bool) true,
        'page'       => (int) $page,
        'total'      => (int) $total,
        'message'    => $moderators ? sprintf(_('%s %s'), $total, plural($total, [_('moderator found!'), _('moderators found!'), _('moderators found!')])) : _('Moderators not found!'),
        'moderators' => (array) $moderators,
    ];
} else {
    $json = [
        'success'    => (bool) false,
        'page'       => (int) $page,
        'total'      => (int) 0,
        'message'    => (string) _('Valid hash required!'),
        'moderators' => (array) [],
    ];
}

header('Content-Type: application/json');
echo json_encode($json);
