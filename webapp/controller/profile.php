<?php

$peerId = isset($_GET['peerId']) ? sanitizeRequest($_GET['peerId']) : '';

// Profile found
if ($profileInfo = $modelProfile->getProfileByPeer($peerId)) {

    // NSFW content
    if ($profileInfo['blocked'] || $profileInfo['nsfw']) {

        $_title = sprintf(_('%s - Profile - %s'), formatText($peerId), PROJECT_NAME);
        $_scripts = [];

        require(PROJECT_DIR . '/view/error/nsfw.phtml');

    // Regilar content
    } else {

        // Headers
        $_title = sprintf(_('%s - Profile - %s'), $profileInfo['name'] ? formatText($profileInfo['name']) : _('Waiting for'), PROJECT_NAME);
        $_scripts = [
            'js/profile.min.js'
        ];

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
        foreach ($modelProfile->getModeratorCurrencies($profileInfo['profileId']) as $currency) {
            if ($currency['name']) {
                $moderatorCurrencies[] = $currency['name'];
            } else {
                $moderatorCurrencies[] = $currency['code'];
            }
        }

        // Get moderator languages
        $moderatorLanguages = [];
        foreach ($modelProfile->getModeratorLanguages($profileInfo['profileId']) as $language) {
            if ($language['name']) {
                $moderatorLanguages[] = $language['name'];
            } else {
                $moderatorLanguages[] = $language['code'];
            }
        }

        // Profile info
        $vendor = (bool) $profileInfo['vendor'];
        $nsfw = (bool) $profileInfo['nsfw'];
        $name = formatText($profileInfo['name']);
        $shortDescription = formatText($profileInfo['shortDescription'], true);
        $about = formatText($profileInfo['about'], true);
        $moderator = (bool) $profileInfo['moderator'];
        $moderatorDescription = formatText($profileInfo['moderatorDescription'], true);
        $moderatorTerms = formatText($profileInfo['moderatorTerms'], true);
        $moderatorPrice = formatModeratorPrice($rates, $profileInfo['moderatorAmount'], $profileInfo['moderatorCurrencyCode'], $profileInfo['moderatorPercentage'], $profileInfo['moderatorFeeType']);
        $peerId = $profileInfo['peerId'];
        $bitcoinPubkey = $profileInfo['bitcoinPubkey'];
        $avatarHashMedium = $profileInfo['avatarHashMedium'];

        $online = (time() - $profileInfo['online'] < PROFILE_ONLINE_TIME);
        $online =  ['class' => $online ? 'text-success' : ($profileInfo['online'] ? 'text-warning' : 'text-danger'),
                    'text'  => $online ? _('Online') : ($profileInfo['online'] ? sprintf(_('Active %s'),  timeLeft($profileInfo['online'])) : _('Passive'))];

        $added    = timeLeft($profileInfo['added']);
        $updated  = $profileInfo['updated'] ? timeLeft($profileInfo['updated']) : _('Pending...');
        $indexed  = $profileInfo['indexed'] ? timeLeft($profileInfo['indexed']) : _('Pending...');

        // Tabs
        $listingsTotal    = $modelListing->getTotalProfileListings($profileInfo['profileId']);
        $followingTotal   = $modelProfile->getTotalProfileFollowing($profileInfo['profileId']);
        $followersTotal   = $modelProfile->getTotalProfileFollowers($profileInfo['profileId']);
        $connectionsTotal = $modelProfile->getDistinctTotalProfileConnections($profileInfo['profileId']);
        $ratingsTotal     = $modelProfile->getTotalProfileRatings($profileInfo['profileId']);

        $contactsTotal    = $modelProfile->getTotalProfileSocials($profileInfo['profileId']);
        if ($profileInfo['website']) {
            $contactsTotal++;
        }
        if ($profileInfo['phoneNumber']) {
            $contactsTotal++;
        }
        if ($profileInfo['email']) {
            $contactsTotal++;
        }

        // Uptime diagram
        $uptimeTimeline = [];
        $uptimeProfile  = [];
        $uptimeServer   = [];
        for ($i = 6; $i >= 0; $i--) {

            $timeFrom = strtotime(date('Y-m-d')) - (86400 * $i);
            $timeTo   = strtotime(date('Y-m-d')) - (86400 * ($i - 1));

            // Profile uptime
            $uptimeProfile[] = round(($modelProfile->getProfileUptime($profileInfo['profileId'],
                                                                      PROFILE_ONLINE_TIME,
                                                                      $timeFrom,
                                                                      $timeTo) / (60 / (PROFILE_ONLINE_TIME / 60))) * 100 / 24);

            // Server uptime
            $uptimeServer[] = round(($modelHealth->getServerUptime(SERVER_ONLINE_TIME,
                                                                   $timeFrom,
                                                                   $timeTo) / (60 / (PROFILE_ONLINE_TIME / 60))) * 100 / 24);

            // Generate timeline
            $uptimeTimeline[] = date("D, j M", strtotime(sprintf('-%s day', $i)));
        }

        $uptimeTimeline = implode('|', $uptimeTimeline);
        $uptimeProfile  = implode('|', $uptimeProfile);
        $uptimeServer   = implode('|', $uptimeServer);

        // Loat template
        require(PROJECT_DIR . '/view/profile.phtml');
    }

// Profile not found
} else {
    require(PROJECT_DIR . '/controller/error/404.php');
}
