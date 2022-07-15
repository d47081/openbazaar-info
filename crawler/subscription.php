<?php

/*
 * Subscription sender
 * Priority: free
 */

require(__DIR__ . '/config.php');

require(__DIR__ . '/model/model.php');
require(__DIR__ . '/model/subscription.php');

require(__DIR__ . '/curl/curl.php');
require(__DIR__ . '/curl/chat.php');

require(__DIR__ . '/helper/subscription.php');

// Use frontend side library to make a search possible
require('model/sphinx.php');

$totalSubscriptionsIndexed = 0;
$totalSubscriptionsUpdated = 0;
$totalSubscriptionsExpired = 0;

$time      = time();
$timeStart = microtime(true);

$modelSubscription   = new ModelSubscription(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$curlChat            = new CurlChat(OB_PROTOCOL, OB_HOST, OB_PORT, OB_USERNAME, OB_PASSWORD);

$modelSphinxFrontend = new ModelSphinx(SPHINX_HOST, SPHINX_PORT);

$totalSubscriptionsExpired = $totalSubscriptionsExpired + $modelSubscription->deleteExpiredSubscriptions();

foreach ($modelSubscription->getSubscriptionsQueue(SUBSCRIPTION__MODEL_SUBSCRIPTION_QUEUE) as $subscription) {

    // Generate search result hash
    $results = [];
    foreach ($modelSphinxFrontend->search($subscription['t'], $subscription['q'], 0, 20, convertSubscriptionDataToSearchFilters($subscription), $subscription['s'], $subscription['o']) as $value) {
        $results[] = $value['id'];
    }

    $hash = sha1(implode(',', $results) . $modelSphinxFrontend->getTotalFound());

    // If subscription hash differ
    if (!$modelSubscription->subscriptionHashExists($subscription['subscriptionId'], $hash)) {

        // Send notification to subscriber
        $curlChat->sendMessage($subscription['peerId'],
                               '',
                               sprintf(_("Subscription has been updated! \n\n%s \n\nSend <strong>#help</strong> command to view available options or request human <strong>#support</strong>"), convertSubscriptionDataToLink($subscription)),
                               CHATBOT__CURL_TIMEOUT_SEND_MESSAGE);

        // Update Subscription hash
        $modelSubscription->updateSubscriptionHash($subscription['subscriptionId'], $hash, $time);

        $totalSubscriptionsUpdated++;
    }

    // Update subscription indexed
    $modelSubscription->updateSubscriptionIndexed($subscription['subscriptionId'], $time);
    $totalSubscriptionsIndexed++;
}

$timeEnd = microtime(true);

// Debug output
echo sprintf("\nExecution time: %s\n\n", $timeEnd - $timeStart);
echo sprintf("Total subscriptions indexed: %s\n", $totalSubscriptionsIndexed);
echo sprintf("Total subscriptions updated: %s\n", $totalSubscriptionsUpdated);
echo sprintf("Total subscriptions expired: %s\n", $totalSubscriptionsExpired);
