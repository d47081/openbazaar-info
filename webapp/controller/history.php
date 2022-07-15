<?php

$_title   = sprintf(_('History - %s'), PROJECT_NAME);

$log = [];

for ($i = 0; $i <= 10; $i++) {

    $timeFrom = strtotime(date('Y-m-d')) - (86400 * $i);
    $timeTo   = strtotime(date('Y-m-d')) - (86400 * ($i - 1));

    $log[date("D, j M", strtotime(sprintf('-%s day', $i)))] = [
        'profiles' => $modelProfile->getNewProfiles($timeFrom, $timeTo),
        'listings' => $modelListing->getNewListings($timeFrom, $timeTo),
        'online'   => $modelProfile->getPeersOnline($timeFrom, $timeTo),
    ];

}

require(PROJECT_DIR . '/view/history.phtml');
