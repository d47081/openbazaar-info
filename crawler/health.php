<?php

/*
 * Health monitor
 * Priority: Every 15min (related to preer connection monitoring)
 */

require(__DIR__ . '/config.php');

require(__DIR__ . '/curl/curl.php');
require(__DIR__ . '/curl/profile.php');

require(__DIR__ . '/model/model.php');
require(__DIR__ . '/model/health.php');

$curlProfile   = new CurlProfile(OB_PROTOCOL, OB_HOST, OB_PORT, OB_USERNAME, OB_PASSWORD);
$modelHealth   = new ModelHealth(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);

$openbazaard   = false;
$online        = false;

if ($curlProfile->getConfig(PEER__CURL_TIMEOUT_LOCALHOST)) {
    $openbazaard = true;
}

exec(sprintf('/usr/bin/curl -Is %s | head -n 1', PROJECT_HOST), $response);
if (isset($response[0]) && $response[0] == 'HTTP/1.1 200 OK') {
    $online = true;
} else {
    $online = false;
}

$modelHealth->add(time(), $openbazaard, $online);

echo sprintf("\nopenbazaard: %s\n", $openbazaard ? 'OK' : 'FAIL');
echo sprintf("online: %s\n", $online ? 'OK' : 'FAIL');
