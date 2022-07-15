<?php

$json = [
    'success' => (bool) false,
    'message' => (string) _('Internal server error!'),
    'data'    => (array) [],
];

if (isset($_GET['peerId']) && $profile = $modelProfile->getProfileByPeer(sanitizeRequest($_GET['peerId']))) {

    // Get currency rates
    $rates = [];
    foreach ($modelCurrency->getLastRates() as $rate) {
        $rates[$rate['code']] = [
            'type'   => $rate['type'],
            'rate'   => $rate['rate'],
            'symbol' => $rate['symbol'],
        ];
    }

    // Get moderator currencies
    $moderatorCurrencies = [];
    foreach ($modelProfile->getModeratorCurrencies($profile['profileId']) as $currency) {
        if ($currency['name']) {
            $moderatorCurrencies[] = $currency['name'];
        } else {
            $moderatorCurrencies[] = $currency['code'];
        }
    }

    // Get moderator languages
    $moderatorLanguages = [];
    foreach ($modelProfile->getModeratorLanguages($profile['profileId']) as $language) {
        if ($language['name']) {
            $moderatorLanguages[] = $language['name'];
        } else {
            $moderatorLanguages[] = $language['code'];
        }
    }

    // Detect online
    $online = (time() - $profile['online'] < PROFILE_ONLINE_TIME);

    $json = [
        'success' => true,
        'message' => _('Profile data successfully received!'),
        'data'    => [

            'peerId'           => (string) $profile['peerId'],
            'handle'           => (string) $profile['handle'],
            'name'             => (string) $profile['name'],
            'bitcoinPubkey'    => (string) $profile['bitcoinPubkey'],

            'name'             => (string) formatText($profile['name']),
            'location'         => (string) $profile['location'],
            'shortDescription' => (string) formatText($profile['shortDescription'], true),
            'about'            => (string) formatText($profile['about'], true),

            'rating'           => [
                'average' => (float) $profile['ratingAverage'],
                'count'   => (int) $profile['ratingCount'],
            ],

            'avatar'           => [
                'tiny'     => (string) $profile['avatarHashTiny'],
                'small'    => (string) $profile['avatarHashSmall'],
                'medium'   => (string) $profile['avatarHashMedium'],
                'large'    => (string) $profile['avatarHashLarge'],
                'original' => (string) $profile['avatarHashOriginal'],
            ],

            'moderator'       => [
                'status'      => (bool) $profile['moderator'],
                'description' => (string) $profile['moderatorDescription'],
                'terms'       => (string) $profile['moderatorTerms'],
                'price'       => (array) [
                    'amount'     => (float) $profile['moderatorAmount'],
                    'currency'   => (string) $profile['moderatorCurrencyCode'],
                    'percentage' => (int) $profile['moderatorPercentage'],
                    'feeType'    => (string) $profile['moderatorFeeType'],
                    'value'      => (string) formatModeratorPrice($rates, $profile['moderatorAmount'], $profile['moderatorCurrencyCode'], $profile['moderatorPercentage'], $profile['moderatorFeeType']),
                ],
                'languages'  => (array) $moderatorLanguages,
                'currencies' => (array) $moderatorCurrencies,
            ],

            'vendor'           => (bool) $profile['vendor'],

            'nsfw'            => [
                'status'      => (bool) $profile['nsfw'],
                'text'        => $profile['nsfw'] ? _('Content for adults or prohibited by law in your country') : _('Safe content'),
            ],

            'added'            => (string) timeLeft($profile['added']),
            'updated'          => (string) $profile['updated'] ? timeLeft($profile['updated']) : _('Pending...'),
            'indexed'          => (string) $profile['indexed'] ? timeLeft($profile['indexed']) : _('Pending...'),

            'online'           => [
                'status' => (string) $online ? 'online' : ($profile['online'] ? 'active' : 'passive'),
                'text'   => (string) $online ? _('Online') : ($profile['online'] ? sprintf(_('Active %s'),  timeLeft($profile['online'])) : _('Passive'))
            ],

            'total'           => [
                'listings'    => (int) $modelListing->getTotalProfileListings($profile['profileId']),
                'following'   => (int) $modelProfile->getTotalProfileFollowing($profile['profileId']),
                'followers'   => (int) $modelProfile->getTotalProfileFollowers($profile['profileId']),
                'connections' => (int) $modelProfile->getDistinctTotalProfileConnections($profile['profileId']),
                'ratings'     => (int) $modelProfile->getTotalProfileRatings($profile['profileId']),
                'contacts'    => (int) $modelProfile->getTotalProfileSocials($profile['profileId']),
            ],
        ],
    ];
} else {
    $json = [
        'success' => (bool) false,
        'message' => (string) _('Valid PeerId required!'),
        'data'    => (array) [],
    ];
}

header('Content-Type: application/json');
echo json_encode($json);
