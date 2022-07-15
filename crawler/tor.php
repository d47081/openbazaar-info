<?php

/*
 * Update ip tor exit node status
 * Priority: low
 */

require(__DIR__ . '/config.php');

require(__DIR__ . '/model/model.php');
require(__DIR__ . '/model/ip.php');

require(__DIR__ . '/curl/curl.php');
require(__DIR__ . '/curl/tor.php');

$totalTorExitNodes = 0;
$totalIpExpired    = 0;
$totalIpUpdated    = 0;

$time      = time();
$timeStart = microtime(true);

$modelIp = new ModelIp(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$curlTor = new CurlTor(TOR_EXIT_NODES_PROTOCOL, TOR_EXIT_NODES_HOST, TOR_EXIT_NODES_PORT);

// Get tor exit nodes list
if ($torExitNodes = $curlTor->getExitNodes(TOR_CURL_EXIT_NODES_TIMEOUT)) {

    // Reset tor statuses
    $totalIpExpired = $totalIpExpired + $modelIp->expireTor(TOR_EXIT_NODE_EXPIRED);
    foreach ($torExitNodes as $ip) {

        $totalTorExitNodes++;

        // Add tor statuses if ip exists
        if ($ipId = $modelIp->ipExists($ip, 4)) {
            $totalIpUpdated = $totalIpUpdated + $modelIp->updateTor($ipId, $time);
        }
    }
}

$timeEnd = microtime(true);

// Debug output
echo sprintf("\nExecution time: %s\n\n", $timeEnd - $timeStart);
echo sprintf("Total exit nodes received: %s\n", $totalTorExitNodes);
echo sprintf("Total exit node IP updated: %s\n", $totalIpUpdated);
echo sprintf("Total exit node IP expired: %s\n", $totalIpExpired);
