<?php

$json = [
    'success'   => (bool) false,
    'page'      => (int) 1,
    'total'     => (int) 0,
    'message'   => (string) _('Internal server error!'),
    'shippings' => (array) [],
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

    $total = 0;
    $shippings = [];
    if ($listingShippings = $modelListing->getListingShippings($listingId, ($page - 1) * LISTING_SHIPPINGS_LIMIT, LISTING_SHIPPINGS_LIMIT)) {
        foreach ($listingShippings as $shipping) {

            $services = [];
            foreach(json_decode($shipping['services'], true) as $service) {
                $services[] = [
                    'name'                => (string) formatText($service['name']),
                    'price'               => (string) formatPrice($rates, $service['price'], $shipping['code']),
                    'additionalItemPrice' => (string) sprintf('+ %s <sup>/item</sup>', formatPrice($rates, $service['additionalItemPrice'], $shipping['code'])),
                    'estimatedDelivery'   => (string) $service['estimatedDelivery'],
                ];
            }

            $countries = [];
            foreach(json_decode($shipping['countries'], true) as $countryId) {
                if ($country = $modelLocation->getCountry($countryId)) {
                    if ($country['display']) {

                        $flagFile      = strtolower($country['codeIso2']);
                        $flagExists    = file_exists(sprintf('%s/public/image/flag/4x3/%s.svg', PROJECT_DIR, $flagFile));

                        $countries[] = [
                            'name'     => (string) $country['name'],
                            'codeIso2' => (string) $country['codeIso2'],
                            'flag'     => $flagExists ? sprintf('image/flag/4x3/%s.svg', $flagFile) : false,
                        ];
                    }
                }
            }

            $shippings[] = [
                'name'      => (string) formatText($shipping['name']),
                'type'      => (string) formatShippingType($shipping['type']),
                'services'  => (array) $services,
                'countries' => (array) $countries,
            ];
            $total++;
        }
    }

    $total = $modelListing->getTotalListingShippings($listingId);

    $json  = [
        'success'   => (bool) true,
        'page'      => (int) $page,
        'total'     => (int) $total,
        'message'   => $shippings ? sprintf(_('%s %s'), $total, plural($total, [_('shipping found!'), _('shippings found!'), _('shippings found!')])) : _('Shipping not found!'),
        'shippings' => (array) $shippings,
    ];
} else {
    $json = [
        'success'   => (bool) false,
        'page'      => (int) $page,
        'total'     => (int) 0,
        'message'   => (string) _('Valid hash required!'),
        'shippings' => (array) [],
    ];
}

header('Content-Type: application/json');
echo json_encode($json);
