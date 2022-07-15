<?php

$hash = isset($_GET['hash']) ? sanitizeRequest($_GET['hash']) : '';

// Profile found
if ($listingInfo = $modelListing->getListingByHash($hash)) {

    // NSFW content
    if ($listingInfo['blocked'] || $listingInfo['nsfw']) {

        $_title = sprintf(_('%s - Listing - %s'), formatText($hash), PROJECT_NAME);
        $_scripts = [];

        require(PROJECT_DIR . '/view/error/nsfw.phtml');

    // Regular content
    } else {

        // Headers
        $_title = sprintf(_('%s by %s - Listing - %s'), formatText($listingInfo['title']), $listingInfo['name'] ? formatText($listingInfo['name']) : $listingInfo['peerId'], PROJECT_NAME);
        $_scripts = [
            'js/listing.min.js'
        ];

        // Get rates
        $rates = [];
        foreach ($modelCurrency->getLastRates() as $rate) {
            $rates[$rate['code']] = [
                'type'   => $rate['type'],
                'rate'   => $rate['rate'],
                'symbol' => $rate['symbol'],
            ];
        }

        // Get listing images
        $listingImagesTotal = 0;
        $listingImages = [];
        foreach ($modelListing->getListingImages($listingInfo['listingId']) as $image) {
            $listingImagesTotal++;
            $listingImages[] = [
                'active'   => ($listingImagesTotal == 1),
                'original' => $image['original'],
                'large'    => $image['large'],
                'medium'   => $image['medium'],
                'small'    => $image['small'],
                'tiny'     => $image['tiny'],
            ];
        }

        // Get listing currencies
        $listingCurrencies = [];
        foreach ($modelListing->getListingCurrencies($listingInfo['listingId']) as $currency) {
            if ($currency['name']) {
                $listingCurrencies[] = $currency['name'];
            } else {
                $listingCurrencies[] = $currency['code'];
            }
        }

        // Get listing options
        $listingOptions = [];
        foreach ($modelListing->getListingOptions($listingInfo['listingId']) as $option) {

            $variants = [];
            foreach (json_decode($option['variants'], true) as $variant) {
                $variants[] = $variant['name'];
            }

            $listingOptions[$option['name']] = $variants;
        }

        // Listing info
        $nsfw         = (bool) $listingInfo['nsfw'];
        $title        = formatText($listingInfo['title']);
        $description  = formatText($listingInfo['description'], true);
        $signature    = formatText($listingInfo['signature']);
        $hash         = formatText($listingInfo['hash']);
        $slug         = formatText($listingInfo['slug']);
        $identityPublicKey  = $listingInfo['identityPublicKey'];
        $bitcoinPublicKey   = $listingInfo['bitcoinPublicKey'];
        $bitcoinSig         = $listingInfo['bitcoinSig'];
        $expiry             = $listingInfo['expiry'];
        $termsAndConditions = formatText($listingInfo['termsAndConditions']);
        $refundPolicy       = formatText($listingInfo['refundPolicy']);
        $price        = formatPrice($rates, $listingInfo['price'], $listingInfo['code']);
        $condition    = formatListingCondition($listingInfo['condition']);
        $contractType = formatListingContractType($listingInfo['contractType']);

        $tags         = $listingInfo['tags'] ? explode(',', formatText($listingInfo['tags'])) : [];
        $categories   = $listingInfo['categories'] ? explode(',', formatText($listingInfo['categories'])) : [];

        $updatedIpns  = $listingInfo['updatedIpns'] ? timeLeft($listingInfo['updatedIpns']) : 0;
        $updatedIpfs  = $listingInfo['updatedIpfs'] ? timeLeft($listingInfo['updatedIpfs']) : 0;
        $indexed      = $listingInfo['indexed'] ? timeLeft($listingInfo['indexed']) : _('Pending...');
        $added        = timeLeft($listingInfo['added']);

        $peerId       = $listingInfo['peerId'];
        $name         = $listingInfo['name'] ? formatText($listingInfo['name']) : $listingInfo['peerId'];

        $online       = (time() - $listingInfo['online'] < PROFILE_ONLINE_TIME);
        $online       = [
            'class' => $online ? 'text-success' : ($listingInfo['online'] ? 'text-warning' : 'text-danger'),
            'text'  => $online ? _('Online') : ($listingInfo['online'] ? sprintf(_('Active %s'),  timeLeft($listingInfo['online'])) : _('Passive'))
        ];

        $href         = [
            'listing' => 'listing/' . formatURL($listingInfo['hash']),
            'ob'      => 'ob://' . formatURL($listingInfo['peerId']) . '/store/' . formatURL($listingInfo['slug']),
        ];

        $payment      = [
            'class' => $listingInfo['ratingAverage'] > 0 && $listingInfo['ratingAverage'] < 3 ? 'btn-danger' : ($listingInfo['moderators'] ? 'btn-primary' : 'btn-success'),
            'title' => $listingInfo['ratingAverage'] > 0 && $listingInfo['ratingAverage'] < 3 ? sprintf(_('Low rating (%s/%s)'), $listingInfo['ratingAverage'], $listingInfo['ratingCount'])
                                                                                              : ($listingInfo['moderators'] ? sprintf(_('Moderated payment (%s %s)'), (int) $listingInfo['moderators'], plural((int) $listingInfo['moderators'], ['variant', 'variants', 'variants']))
                                                                                                                            : _('Direct payment')),
        ];

        $shippingsTotal  = $modelListing->getTotalListingShippings($listingInfo['listingId']);
        $moderatorsTotal = $modelListing->getTotalListingModerators($listingInfo['listingId']);

        $otherTotal      = $modelListing->getTotalProfileListings($listingInfo['profileId']);
        if ($otherTotal > 0) {
            $otherTotal = $otherTotal - 1;
        }

        // Loat template
        require(PROJECT_DIR . '/view/listing.phtml');
    }


// Profile not found
} else {
    require(PROJECT_DIR . '/controller/error/404.php');
}
