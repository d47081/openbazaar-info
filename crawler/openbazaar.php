<?php

/*
 * Check node online
 * Priority: 1
 */

require(__DIR__ . '/config.php');

require(__DIR__ . '/curl/curl.php');
require(__DIR__ . '/curl/profile.php');

$curlProfile  = new CurlProfile(OB_PROTOCOL, OB_HOST, OB_PORT, OB_USERNAME, OB_PASSWORD);

// Check node is UP
if (!$curlProfile->getConfig(PEER__CURL_TIMEOUT_LOCALHOST)) {
    exec(sprintf('%s run %s start', SYSTEM_GO_PATH, SYSTEM_OPENBAZAAR_PATH));
    echo "Node successfully started!\n";
} else {
    echo "Node already running!\n";
}
