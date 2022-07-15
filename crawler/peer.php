<?php

/*
 * Discover online peers
 * Priority: Every 15min!
 */

require(__DIR__ . '/config.php');

require(__DIR__ . '/model/sphinx.php');
require(__DIR__ . '/model/model.php');
require(__DIR__ . '/model/profile.php');
require(__DIR__ . '/model/listing.php');
require(__DIR__ . '/model/ip.php');

require(__DIR__ . '/curl/curl.php');
require(__DIR__ . '/curl/profile.php');

$totalProfilesInPeers = 0;
$totalProfilesAdded   = 0;
$time = time();
$timeStart = microtime(true);

$modelSphinx  = new ModelSphinx(SPHINX_HOST, SPHINX_PORT);
$modelProfile = new ModelProfile(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelIp      = new ModelIp(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelListing = new ModelListing(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$curlProfile  = new CurlProfile(OB_PROTOCOL, OB_HOST, OB_PORT, OB_USERNAME, OB_PASSWORD);

// Find new profiles
if ($curlProfilePeers = $curlProfile->getPeers(PEER__CURL_TIMEOUT_GET_PEERS)) {

    // Add this peer
    $curlProfilePeers[] = OB_PEER_ID;

    foreach ($curlProfilePeers as $peerId) {

        $torTime = 0;

        // Sanitize curl response
        $peerId = $curlProfile->sanitize($peerId);

        // Add new peers if not exits
        if (!$profileId = $modelProfile->profileExists($peerId)) {
             $profileId = $modelProfile->addProfile($peerId, $time);
             $totalProfilesAdded++;
        }

        // Create queue if online peer still not indexed
        if ($modelProfile->isWaiting($profileId)) {
            $modelProfile->updateProfileIndexed($profileId, 0);
        }

        // Self IP could not be retreived using API @TODO
        if ($peerId == OB_PEER_ID) {

            // Add new ip if not exits
            if (!$ipId = $modelIp->ipExists(OB_PEER_IP, OB_PEER_IP_VERSION)) {
                 $ipId = $modelIp->addIp(OB_PEER_IP, OB_PEER_IP_VERSION);
            }

            // Add profile connection
            $modelProfile->addProfileConnection( $profileId,
                                                 $ipId,
                                                 $ip['protocol'], // @TODO optimize field datatype
                                                 $time);

        // Update peers IP
        } else {

            if ($peerIps = $curlProfile->getPeerIps($peerId, PEER__CURL_TIMEOUT_GET_PEER_IPS)) {

                $ips = [];
                foreach ($peerIps as $peerIp) {
                    $ips[$peerIp['protocol'].$peerIp['ip'].$peerIp['version']] = [
                        'ip'       => $curlProfile->sanitize($peerIp['ip']),
                        'protocol' => $curlProfile->sanitize($peerIp['protocol']),
                        'version'  => $peerIp['version']== 'ip6' ? 6 : 4,
                    ];
                }

                foreach ($ips as $ip) {

                    // Add new ip if not exits
                    if (!$ipId = $modelIp->ipExists($ip['ip'], $ip['version'])) {
                         $ipId = $modelIp->addIp($ip['ip'], $ip['version']);

                    // Check existing IP is TOR
                    } else {
                        $torTime = $modelIp->getTorTime($ipId);
                    }

                    // Add profile connection
                    $modelProfile->addProfileConnection( $profileId,
                                                         $ipId,
                                                         $ip['protocol'], // @TODO optimize field datatype
                                                         $time);
                }
            }
        }

        // Update profile online
        $modelProfile->updateProfileOnline($profileId, $time);
        $modelSphinx->updateProfileOnline($profileId, $time);
        $modelSphinx->updateProfilePs($profileId, 'online');

        // Update profile tor
        $modelProfile->updateProfileTor($profileId, $torTime);
        $modelSphinx->updateProfileTor($profileId, $torTime);

        // Update profile listings index
        foreach ($modelListing->getProfileListings($profileId) as $listing) {

            // Update listings online
            $modelSphinx->updateListingOnline($listing['listingId'], $time);

            // Update listing tor
            $modelSphinx->updateListingTor($listing['listingId'], $torTime);
        }

        $totalProfilesInPeers++;
    }
}

// Update ps online if expired
foreach ($modelProfile->getProfilesOnlineExpired(PROFILE_ONLINE_TIME) as $profile) {
    $modelSphinx->updateProfilePs($profile['profileId'], 'active');
}

$timeEnd = microtime(true);

// Debug output
echo sprintf("\nExecution time: %s\n\n", $timeEnd - $timeStart);
echo sprintf("Peers available: %s\n", $totalProfilesInPeers);
echo sprintf("Profiles added: %s\n\n", $totalProfilesAdded);
