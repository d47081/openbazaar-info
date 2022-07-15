<?php

/*
 * Update accessible profiles
 * Priority: 2
 */

require(__DIR__ . '/config.php');

require(__DIR__ . '/model/model.php');
require(__DIR__ . '/model/profile.php');
require(__DIR__ . '/model/listing.php');
require(__DIR__ . '/model/currency.php');
require(__DIR__ . '/model/language.php');
require(__DIR__ . '/model/word.php');
require(__DIR__ . '/model/sphinx.php');

require(__DIR__ . '/curl/curl.php');
require(__DIR__ . '/curl/profile.php');
require(__DIR__ . '/curl/listing.php');
require(__DIR__ . '/curl/rating.php');

$totalProfilesAdded    = 0;
$totalProfilesIndexed  = 0;
$totalProfilesUpdated  = 0;
$totalProfilesFollowed = 0;
$nsfwProfilesAdded     = 0;
$nsfwProfilesRemoved   = 0;

$totalListingsAdded    = 0;
$totalListingsRemoved  = 0;

$peerIdProcessed = [];
$timeStart = microtime(true);

$modelSphinx   = new ModelSphinx(SPHINX_HOST, SPHINX_PORT);
$modelProfile  = new ModelProfile(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelListing  = new ModelListing(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelCurrency = new ModelCurrency(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelLanguage = new ModelLanguage(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelWord     = new ModelWord(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$curlRating    = new CurlRating(OB_PROTOCOL, OB_HOST, OB_PORT, OB_USERNAME, OB_PASSWORD);
$curlProfile   = new CurlProfile(OB_PROTOCOL, OB_HOST, OB_PORT, OB_USERNAME, OB_PASSWORD);
$curlListing   = new CurlListing(OB_PROTOCOL, OB_HOST, OB_PORT, OB_USERNAME, OB_PASSWORD);

// Get nsfw words
$nsfwWords = [];
foreach ($modelWord->getNsfwFords(1) as $word) {
    $nsfwWords[] = mb_strtolower($word['name'], 'UTF-8');
}

// Update index
if ($modelIndexQueue = $modelProfile->getIndexQueue(PROFILE__MODEL_PROFILE_INDEX_QUEUE)) {

    foreach ($modelIndexQueue as $profile) {

        // Define variables
        $profileIndexContent = [];
        $time = time();

        // Follow profile
        if (!$curlProfile->isFollowing($profile['peerId'], PROFILE__CURL_TIMEOUT_FOLLOW)) {
             $curlProfile->follow($profile['peerId'], PROFILE__CURL_TIMEOUT_FOLLOW);
             $totalProfilesFollowed++;
        }

        // Update connection info
        if ($lastProfileConnection = $modelProfile->getLastProfileConnection($profile['profileId'])) {

            // Update profile online time
            if ($lastProfileConnection['time']) {
                $modelProfile->updateProfileOnline($profile['profileId'], $lastProfileConnection['time']);
                $modelSphinx->updateProfileOnline($profile['profileId'], $lastProfileConnection['time']);
            }

            // Update profile tor time
            if ($lastProfileConnection['tor']) {
                $modelProfile->updateProfileTor($profile['profileId'], $lastProfileConnection['tor']);
                $modelSphinx->updateProfileTor($profile['profileId'], $lastProfileConnection['tor']);
            }

            // Update profile countryId is provided
            if ($lastProfileConnection['countryId'] && $lastProfileConnection['codeIso2']) {
                $modelProfile->updateProfileCountryId($profile['profileId'], $lastProfileConnection['countryId']);
                $modelSphinx->updateProfileLf($profile['profileId'], $lastProfileConnection['codeIso2']);
            }

            // Update listings online
            /*
            foreach ($modelListing->getProfileListings($profile['profileId']) as $listing) {

                if ($lastProfileConnection['time']) {
                    $modelSphinx->updateListingOnline($listing['listingId'], $lastProfileConnection['time']);
                }

                if ($lastProfileConnection['countryId'] && $lastProfileConnection['codeIso2']) {
                    $modelSphinx->updateListingCode($listing['listingId'], $lastProfileConnection['codeIso2']);
                }
            }
            */
        }

        // Update processed peerId list
        $peerIdProcessed[] = $profile['profileId'] . '/' . $profile['peerId'] ;

        // Node info is available
        if ($curlProfileInfo = $curlProfile->getProfile($profile['peerId'], PROFILE__CURL_TIMEOUT_GET_PROFILE)) {

            // Get ratings
            if (false !== $curlProfileRatings = $curlRating->getRatings($profile['peerId'], PROFILE__CURL_TIMEOUT_GET_PROFILE_RATINGS)) {

                // Flush following list
                $modelProfile->flushProfileRatings($profile['profileId']);
                foreach ($curlProfileRatings['ratings'] as $ratingHash) {

                    if ($curlProfileRating = $curlRating->getRating($ratingHash, PROFILE__CURL_TIMEOUT_GET_PROFILE_RATING)) {

                        // Add profile if not exists
                        if (!$profileId = $modelProfile->profileExists($curlProfileRating['ratingData']['buyerID']['peerID'])) {
                             $profileId = $modelProfile->addProfile($curlProfileRating['ratingData']['buyerID']['peerID'], time());
                             $totalProfilesAdded++;
                        }

                        // Add rating
                        $modelProfile->addProfileRating($profile['profileId'],
                                                        $profileId,
                                                        $ratingHash,
                                                        $curlProfile->sanitize($curlProfileRating['ratingData']['timestamp']['seconds']),
                                                        $curlProfile->sanitize($curlProfileRating['ratingData']['customerService']),
                                                        $curlProfile->sanitize($curlProfileRating['ratingData']['deliverySpeed']),
                                                        $curlProfile->sanitize($curlProfileRating['ratingData']['description']),
                                                        $curlProfile->sanitize($curlProfileRating['ratingData']['overall']),
                                                        $curlProfile->sanitize($curlProfileRating['ratingData']['quality']),
                                                        isset($curlProfileRating['ratingData']['review']) ? $curlProfile->sanitize($curlProfileRating['ratingData']['review']) : '');
                    }
                }
            }

            // Get following
            if (false !== $curlProfileFollowing = $curlProfile->getProfileFollowing($profile['peerId'], PROFILE__CURL_TIMEOUT_GET_PROFILE_FOLLOWING)) {

                // Flush following list
                $modelProfile->flushProfileFollowing($profile['profileId']);
                foreach ($curlProfileFollowing as $peerId) {

                    // Sanitize curl response
                    $peerId = $curlProfile->sanitize($peerId);

                    // Add new profile if not exits
                    if (!$followingProfileId = $modelProfile->profileExists($peerId)) {
                         $followingProfileId = $modelProfile->addProfile($peerId, time());
                         $totalProfilesAdded++;
                    }

                    // Add following
                    $modelProfile->addProfileFollowing($profile['profileId'], $followingProfileId);
                }
            }

            // Get followers
            if (false !== $curlProfileFollowers = $curlProfile->getProfileFollowers($profile['peerId'], PROFILE__CURL_TIMEOUT_GET_PROFILE_FOLLOWERS)) {

                // Flush followers list
                $modelProfile->flushProfileFollowers($profile['profileId']);
                foreach ($curlProfileFollowers as $peerId) {

                    // Sanitize curl response
                    $peerId = $curlProfile->sanitize($peerId);

                    // Add new profile if not exits
                    if (!$followerProfileId = $modelProfile->profileExists($peerId)) {
                         $followerProfileId = $modelProfile->addProfile($peerId, time());
                         $totalProfilesAdded++;
                    }

                    // Add follower
                    $modelProfile->addProfileFollower($profile['profileId'], $followerProfileId);
                }
            }

            // Get listings
            if (false !== $curlListings = $curlListing->getListings($profile['peerId'], PROFILE__CURL_TIMEOUT_GET_LISTINGS)) {

                // Set removed time for all profile listings
                $modelListing->updateListingsRemoved($profile['profileId'], time());

                foreach ($curlListings as $listing) {

                    // Add new listing if not exits
                    if (!$listingId = $modelListing->listingExists($listing['hash'])) {
                         $listingId = $modelListing->addListing($profile['profileId'],
                                                                $curlListing->sanitize($listing['slug']),
                                                                $curlListing->sanitize($listing['hash']),
                                                                time());

                         $totalListingsAdded++;
                    }

                    // Drop removed time if listing exists
                    $modelListing->updateListingRemoved($listingId, 0);
                }
            }

            // Update ratings
            if ($curlRatings = $curlProfile->getProfileRating($profile['peerId'], PROFILE__CURL_TIMEOUT_GET_RATINGS)) {

                $businessLevel = $curlRatings['average'] * $curlRatings['count'] * 10;

                $modelProfile->updateProfileRating($profile['profileId'], $curlRatings['average'], $curlRatings['count'], $businessLevel);
                $modelSphinx->updateProfileRatingAverage($profile['profileId'], $curlRatings['average']);
                $modelSphinx->updateProfileRatingCount($profile['profileId'], $curlRatings['count']);
            }

            // lastModified is different
            //if ($curlProfileInfo['lastModified'] != $profile['lastModified']) {

                $curlProfileInfoSanitized = [];
                foreach ($curlProfileInfo as $key => $value) {
                    if (!is_array($value)) {
                        $curlProfileInfoSanitized[$key] = $curlProfile->sanitize($value);
                    }
                }

                // If profile not indexed - create temporary content registry for NSFW check
                if (NULL == $profile['indexed']) {
                     $profileIndexContent = explode(' ', mb_strtolower($curlProfileInfoSanitized['name'], 'UTF-8') . ' ' .
                                                         mb_strtolower($curlProfileInfoSanitized['location'], 'UTF-8') . ' ' .
                                                         mb_strtolower($curlProfileInfoSanitized['shortDescription'], 'UTF-8') . ' ' .
                                                         mb_strtolower($curlProfileInfoSanitized['about'], 'UTF-8'));
                }

                // Update profile data
                $modelProfile->updateProfile( $profile['profileId'],
                                              $curlProfileInfoSanitized['version'],
                                              $curlProfileInfoSanitized['handle'],
                                              $curlProfileInfoSanitized['bitcoinPubkey'],
                                              $curlProfileInfoSanitized['lastModified'],
                                              $time,
                                              (int) $curlProfileInfoSanitized['vendor'],
                                              isset($curlProfileInfo['contactInfo']['website']) ? $curlProfile->sanitize($curlProfileInfo['contactInfo']['website']) : 0,
                                              isset($curlProfileInfo['contactInfo']['email']) ? $curlProfile->sanitize($curlProfileInfo['contactInfo']['email']) : 0,
                                              isset($curlProfileInfo['contactInfo']['phoneNumber']) ? $curlProfile->sanitize($curlProfileInfo['contactInfo']['phoneNumber']) : 0,
                                              (isset($curlProfileInfo['colors']['primary']) ? $curlProfile->sanitize($curlProfileInfo['colors']['primary']) : ''),
                                              (isset($curlProfileInfo['colors']['secondary']) ? $curlProfile->sanitize($curlProfileInfo['colors']['secondary']) : ''),
                                              (isset($curlProfileInfo['colors']['text']) ? $curlProfile->sanitize($curlProfileInfo['colors']['text']) : ''),
                                              (isset($curlProfileInfo['colors']['highlight']) ? $curlProfile->sanitize($curlProfileInfo['colors']['highlight']) : ''),
                                              (isset($curlProfileInfo['colors']['highlightText']) ? $curlProfile->sanitize($curlProfileInfo['colors']['highlightText']) : ''),
                                              (isset($curlProfileInfo['avatarHashes']['tiny']) ? $curlProfile->sanitize($curlProfileInfo['avatarHashes']['tiny']) : ''),
                                              (isset($curlProfileInfo['avatarHashes']['small']) ? $curlProfile->sanitize($curlProfileInfo['avatarHashes']['small']) : ''),
                                              (isset($curlProfileInfo['avatarHashes']['medium']) ? $curlProfile->sanitize($curlProfileInfo['avatarHashes']['medium']) : ''),
                                              (isset($curlProfileInfo['avatarHashes']['large']) ? $curlProfile->sanitize($curlProfileInfo['avatarHashes']['large']) : ''),
                                              (isset($curlProfileInfo['avatarHashes']['original']) ? $curlProfile->sanitize($curlProfileInfo['avatarHashes']['original']) : ''),
                                              (isset($curlProfileInfo['headerHashes']['tiny']) ? $curlProfile->sanitize($curlProfileInfo['headerHashes']['tiny']) : ''),
                                              (isset($curlProfileInfo['headerHashes']['small']) ? $curlProfile->sanitize($curlProfileInfo['headerHashes']['small']) : ''),
                                              (isset($curlProfileInfo['headerHashes']['medium']) ? $curlProfile->sanitize($curlProfileInfo['headerHashes']['medium']) : ''),
                                              (isset($curlProfileInfo['headerHashes']['large']) ? $curlProfile->sanitize($curlProfileInfo['headerHashes']['large']) : ''),
                                              (isset($curlProfileInfo['headerHashes']['original']) ? $curlProfile->sanitize($curlProfileInfo['headerHashes']['original']) : ''),
                                              $curlProfileInfoSanitized['name'],
                                              $curlProfileInfoSanitized['location'],
                                              $curlProfileInfoSanitized['shortDescription'],
                                              $curlProfileInfoSanitized['about'],
                                              (int) $curlProfileInfoSanitized['moderator']);

                $modelSphinx->updateProfileModerator($profile['profileId'], (int) $curlProfileInfoSanitized['moderator']);
                $modelSphinx->updateProfileUpdated($profile['profileId'], $time);

                // Update profile currencies
                $modelProfile->flushProfileCurrency($profile['profileId']);
                if (isset($curlProfileInfo['currencies']) && $curlProfileInfo['currencies']) {
                    foreach ($curlProfileInfo['currencies'] as $currency) {

                        // Add currency if not exists
                        if (!$profileCurrencyId = $modelCurrency->currencyExists($curlListing->sanitize($currency))) {
                             $profileCurrencyId = $modelCurrency->addCurrency($curlListing->sanitize($currency));
                        }

                        // Add profile currency
                        if (!$modelProfile->profileCurrencyExists($profile['profileId'], $profileCurrencyId)) {
                             $modelProfile->addProfileCurrency($profile['profileId'], $profileCurrencyId);
                        }
                    }
                }

                // Update profile social
                $modelProfile->flushProfileSocial($profile['profileId']);
                if (isset($curlProfileInfo['contactInfo']['social']) && is_array($curlProfileInfo['contactInfo']['social']) && $curlProfileInfo['contactInfo']['social']) {
                    foreach ($curlProfileInfo['contactInfo']['social'] as $social) {
                        if (isset($social['type']) && isset($social['username']) && isset($social['proof'])) {
                            $modelProfile->addProfileSocial($profile['profileId'],
                                                            $curlProfile->sanitize($social['type']),
                                                            $curlProfile->sanitize($social['username']),
                                                            $curlProfile->sanitize($social['proof']));
                        }
                    }
                }

                // Update moderator info if exists
                if (isset($curlProfileInfo['moderatorInfo']) &&
                    isset($curlProfileInfo['moderatorInfo']['description']) &&
                    isset($curlProfileInfo['moderatorInfo']['termsAndConditions']) &&
                    isset($curlProfileInfo['moderatorInfo']['fee']['fixedFee']['currencyCode']) &&
                    isset($curlProfileInfo['moderatorInfo']['fee']['fixedFee']['amount']) &&
                    isset($curlProfileInfo['moderatorInfo']['fee']['percentage']) &&
                    isset($curlProfileInfo['moderatorInfo']['fee']['feeType']) &&
                    isset($curlProfileInfo['moderatorInfo']['acceptedCurrencies']) && is_array($curlProfileInfo['moderatorInfo']['acceptedCurrencies']) &&
                    isset($curlProfileInfo['moderatorInfo']['languages']) && is_array($curlProfileInfo['moderatorInfo']['languages'])) {

                        // Update moderator languages
                        $modelProfile->flushProfileModeratorLanguage($profile['profileId']);
                        if ($curlProfileInfo['moderatorInfo']['languages']) {
                            foreach ($curlProfileInfo['moderatorInfo']['languages'] as $language) {

                                // Add language if not exists
                                $language = $curlListing->sanitize($language);
                                if (!$profileModeratorLanguageId = $modelLanguage->languageExists($language)) {
                                     $profileModeratorLanguageId = $modelLanguage->addLanguage($language);
                                }

                                // Add profile language
                                $modelProfile->addProfileModeratorLanguage($profile['profileId'], $profileModeratorLanguageId);
                            }
                        }

                        // Update moderator currencies
                        $modelProfile->flushProfileModeratorCurrency($profile['profileId']);
                        if ($curlProfileInfo['moderatorInfo']['acceptedCurrencies']) {
                            foreach ($curlProfileInfo['moderatorInfo']['acceptedCurrencies'] as $currency) {

                                // Add currency if not exists
                                $currency = $curlListing->sanitize($currency);
                                if (!$profileModeratorCurrencyId = $modelCurrency->currencyExists($currency)) {
                                     $profileModeratorCurrencyId = $modelCurrency->addCurrency($currency);
                                }

                                // Add profile currency
                                $modelProfile->addProfileModeratorCurrency($profile['profileId'], $profileModeratorCurrencyId);
                            }
                        }

                       // Update moderator info
                       // Add currency if not exists
                       $feeCurrency = $curlListing->sanitize($curlProfileInfo['moderatorInfo']['fee']['fixedFee']['currencyCode']);
                       if (!$currencyId = $modelCurrency->currencyExists($feeCurrency)) {
                            $currencyId = $modelCurrency->addCurrency($feeCurrency);
                       }

                       $modelProfile->updateProfileModerator( $profile['profileId'],
                                                              $curlProfile->sanitize($curlProfileInfo['moderatorInfo']['fee']['feeType']),
                                                              $curlProfile->sanitize($curlProfileInfo['moderatorInfo']['fee']['fixedFee']['amount']),
                                                              $curlProfile->sanitize($curlProfileInfo['moderatorInfo']['fee']['percentage']),
                                                              $currencyId,
                                                              $curlProfile->sanitize($curlProfileInfo['moderatorInfo']['description']),
                                                              $curlProfile->sanitize($curlProfileInfo['moderatorInfo']['termsAndConditions']));
                }

                $totalProfilesUpdated++;
            //}
        } else {
            $modelProfile->updateProfileError($profile['profileId'], time());
        }

        // Update nsfw
        if ($nsfwWords) {
            $nsfwWordsTotal = 0;

            foreach ($nsfwWords as $nsfwWord) {

                // If profile was not indexed
                if ($profileIndexContent) {
                    if (in_array($nsfwWord, $profileIndexContent)) {
                        $nsfwWordsTotal++;
                    }

                // Profile indexed
                } else {
                    if ($modelSphinx->wordExists('profile', $nsfwWord, $profile['profileId'])) {
                        $nsfwWordsTotal++;
                    }
                }
            }

            if ($nsfwWordsTotal) {
                $nsfwProfilesAdded = $nsfwProfilesAdded + $modelProfile->updateProfileNsfw($profile['profileId'], 1);
                $modelSphinx->updateProfileNsfw($profile['profileId'], 1);
            } else {
                $nsfwProfilesRemoved = $nsfwProfilesRemoved + $modelProfile->updateProfileNsfw($profile['profileId'], 0);
                $modelSphinx->updateProfileNsfw($profile['profileId'], 0);
            }
        }

        // Update index date
        $modelProfile->updateProfileIndexed($profile['profileId'], $time);
        $modelSphinx->updateProfileIndexed($profile['profileId'], $time);
        $totalProfilesIndexed++;
    }
}

$timeEnd = microtime(true);

// Debug output
echo sprintf("\nExecution time: %s\n\n", $timeEnd - $timeStart);
echo sprintf("Profiles added: %s\n", $totalProfilesAdded);
echo sprintf("Profiles indexed: %s\n", $totalProfilesIndexed);
echo sprintf("Profiles updated: %s\n", $totalProfilesUpdated);
echo sprintf("Profiles followed: %s\n\n", $totalProfilesFollowed);
echo sprintf("NSFW Profiles added: %s\n", $nsfwProfilesAdded);
echo sprintf("NSFW Profiles removed: %s\n", $nsfwProfilesRemoved);
echo sprintf("Listings added: %s\n", $totalListingsAdded);
echo sprintf("Listings removed: %s\n\n", $totalListingsRemoved);

if ($peerIdProcessed) {
    print("ProfileId/PeerId processed:\n\n");
    foreach ($peerIdProcessed as $value) {
        print($value) . "\n";
    }
    print("\n");
}
