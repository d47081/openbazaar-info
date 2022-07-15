<?php

$json = [
    'success' => false,
    'message' => _('Internal server error!'),
    'data'    => [],
];

if (isset($_GET['hash']) && $listing = $modelListing->getListingByHash(sanitizeRequest($_GET['hash']))) {

    // Get currency rates
    $rates = [];
    foreach ($modelCurrency->getLastRates() as $rate) {
        $rates[$rate['code']] = [
            'type'   => $rate['type'],
            'rate'   => $rate['rate'],
            'symbol' => $rate['symbol'],
        ];
    }

    // Get listing images
    $images = [];
    foreach ($modelListing->getListingImages($listing['listingId']) as $image) {
        $images[] = [
            'tiny'     => (string) $image['tiny'],
            'small'    => (string) $image['small'],
            'medium'   => (string) $image['medium'],
            'large'    => (string) $image['large'],
            'original' => (string) $image['original'],
        ];
    }

    // Get listing currencies
    $currencies = [];
    foreach ($modelListing->getListingCurrencies($listing['listingId']) as $currency) {
        if ($currency['name']) {
            $listingCurrencies[] = (string) $currency['name'];
        } else {
            $listingCurrencies[] = (string) $currency['code'];
        }
    }

    // Get listing options
    $options = [];
    foreach ($modelListing->getListingOptions($listing['listingId']) as $option) {

        $variants = [];
        foreach (json_decode($option['variants'], true) as $variant) {
            $variants[] = (string) $variant['name'];
        }

        $options[$option['name']] = $variants;
    }

    // Detect online
    $online = (time() - $listing['online'] < PROFILE_ONLINE_TIME);

    $json = [
        'success' => true,
        'message' => _('Listing data successfully received!'),
        'data'    => [

            'hash'             => (string) $listing['hash'],
            'slug'             => (string) $listing['slug'],

            'profile' => [
                'peerId' => (string) $listing['peerId'],
                'name'   => $listing['name'] ? (string) formatText($listing['name']) : (string) $listing['peerId'],
                'online' => [
                    'status' => (string) $online ? 'online' : ($listing['online'] ? 'active' : 'passive'),
                    'text'   => (string) $online ? _('Online') : ($listing['online'] ? sprintf(_('Active %s'),  timeLeft($listing['online'])) : _('Passive')),
                ],
            ],

            'nsfw'         => [
                'status' => (bool) $listing['nsfw'],
                'text'   => $listing['nsfw'] ? _('Content for adults or prohibited by law in your country') : _('Safe content'),
            ],

            'added'        => (string) timeLeft($listing['added']),
            'updatedIpns'  => $listing['updatedIpns'] ? (string) timeLeft($listing['updatedIpns']) : _('Pending...'),
            'updatedIpfs'  => $listing['updatedIpfs'] ? (string) timeLeft($listing['updatedIpfs']) : _('Pending...'),
            'indexed'      => $listing['indexed'] ? (string) timeLeft($listing['indexed']) : _('Pending...'),

            'price'        => (string) formatPrice($rates, $listing['price'], $listing['code']),
            'condition'    => (string) formatListingCondition($listing['condition']),
            'contractType' => (string) formatListingContractType($listing['contractType']),

            'payment'      => (array) [
                'security' => $listing['ratingAverage'] > 0 && $listing['ratingAverage'] < 3 ? 'danger' : ($listing['moderators'] ? 'moderated' : 'direct'),
                'text'     => $listing['ratingAverage'] > 0 && $listing['ratingAverage'] < 3 ? sprintf(_('Low rating (%s/%s)'), $listing['ratingAverage'], $listing['ratingCount'])
                                                                                                     : ($listing['moderators'] ? sprintf(_('Moderated payment (%s %s)'), (int) $listing['moderators'], plural((int) $listing['moderators'], ['variant', 'variants', 'variants'])) : _('Direct payment')),
            ],

            'title'              => (string) formatText($listing['title']),
            'description'        => (string) formatText($listing['description'], true),
            'termsAndConditions' => (string) formatText($listing['termsAndConditions']),
            'refundPolicy'       => (string) formatText($listing['refundPolicy']),

            'tags'         => $listing['tags'] ? (array) explode(',', formatText($listing['tags'])) : [],
            'categories'   => $listing['categories'] ? (array) explode(',', formatText($listing['categories'])) : [],

            'images'       => (array) $images,
            'currencies'   => (array) $currencies,
            'options'      => (array) $options,
        ],
    ];
} else {
    $json = [
        'success' => (bool) false,
        'message' => (string) _('Valid hash required!'),
        'data'    => (array) [],
    ];
}

header('Content-Type: application/json');
echo json_encode($json);
