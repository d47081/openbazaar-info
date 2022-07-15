<?php

/*
 * Discover online peers
 * Priority: Every 15min!
 */

require(__DIR__ . '/config.php');

require(__DIR__ . '/model/model.php');
require(__DIR__ . '/model/profile.php');
require(__DIR__ . '/model/listing.php');

require(__DIR__ . '/library/ReSearch.php');

$nsfwListingsTotal   = 0;
$nsfwListingsUpdated = 0;
$nsfwProfilesTotal   = 0;
$nsfwProfilesUpdated = 0;
$timeStart           = microtime(true);

$reSearch      = new ReSearch(DB_INDEX_DATABASE, DB_INDEX_HOSTNAME, DB_INDEX_PORT, DB_INDEX_USERNAME, DB_INDEX_PASSWORD);
$modelProfile  = new ModelProfile(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelListing  = new ModelListing(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);

$reSearch->setWordsMode($reSearch::MATCH_ANY);
$reSearch->setNsfw(1);

foreach ($reSearch->get('listing', 0, 1000000) as $match) {
    $nsfwListingsUpdated = $nsfwListingsUpdated + $modelListing->updateListingNsfw($match['id'], 1);
    $nsfwListingsTotal++;
}

foreach ($reSearch->get('profile', 0, 1000000) as $match) {
    $nsfwProfilesUpdated = $nsfwProfilesUpdated + $modelProfile->updateProfileNsfw($match['id'], 1);
    $nsfwProfilesTotal++;
}

$timeEnd = microtime(true);

// Debug output
echo sprintf("\nExecution time: %s\n\n", $timeEnd - $timeStart);
echo sprintf("\nNSFW Listings total/updated: %s/%s", $nsfwListingsTotal, $nsfwListingsUpdated);
echo sprintf("\nNSFW Profiles total/updated: %s/%s\n\n", $nsfwProfilesTotal, $nsfwProfilesUpdated);
