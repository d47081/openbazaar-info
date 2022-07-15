<?php

/*
 * OB chat bot
 * Priority: every minute
 */

require(__DIR__ . '/config.php');

require(__DIR__ . '/model/model.php');
require(__DIR__ . '/model/profile.php');
require(__DIR__ . '/model/listing.php');
require(__DIR__ . '/model/subscription.php');

require(__DIR__ . '/curl/curl.php');
require(__DIR__ . '/curl/chat.php');

require(__DIR__ . '/helper/subscription.php');

// Use frontend side library to make a search possible
require('model/sphinx.php');

function chatbotProcessSubscription($modelSubscription, $modelSphinxFrontend, $message, $data, $time, $expired, &$totalSubscriptionAdded, &$totalSubscriptionUpdated) {

    // Set defaults
    $responses = [];

    // Subsciption exists
    if ($subscriptionId = $modelSubscription->subscriptionExists($message['profileId'],
                                                                 $data['protocol'],
                                                                 $data['t'],
                                                                 $data['q'],
                                                                 $data['s'],
                                                                 $data['o'],
                                                                 $data['m'],
                                                                 $data['lf'],
                                                                 $data['lc'],
                                                                 $data['lt'],
                                                                 $data['pr'],
                                                                 $data['ps'],
                                                                 $data['id'])) {

        // Update subscription
        if ($modelSubscription->updateSubscription($subscriptionId, $time, $expired)) {

            $totalSubscriptionUpdated++;

            // Response
            $responses[] = sprintf(_("Subscription %s successfully updated! \n\nExpiration date: \n%s GMT \n\nSend <strong>#help</strong> command to view available options or request human <strong>#support</strong>"),
                                   convertSubscriptionDataToLink($data),
                                   gmdate('Y-m-d H:i', $expired));
        } else {

            // Response
            $responses[] = sprintf(_("Subscription %s could not be updated! \n\nSend <strong>#help</strong> command to view available options or request human <strong>#support</strong>"),
                                   convertSubscriptionDataToLink($data));
        }

    // Subsciption not exists
    } else {

        // Check profile subscriptions limit
        if (PROFILE_MAX_SUBSCRIPRIONS > $modelSubscription->getTotalSubscriptions($message['profileId'])) {

          // Generate search result hash
          $results = [];
          foreach ($modelSphinxFrontend->search($data['t'], $data['q'], 0, 20, convertSubscriptionDataToSearchFilters($data), $data['s'], $data['o']) as $value) {
              $results[] = $value['id'];
          }

          $hash = sha1(implode(',', $results) . $modelSphinxFrontend->getTotalFound());

          // Add new subscription
          if ($subscriptionId = $modelSubscription->addSubscription( $message['profileId'],
                                                                     $time,
                                                                     $expired,
                                                                     $hash,
                                                                     $data['protocol'],
                                                                     $data['t'],
                                                                     $data['q'],
                                                                     $data['s'],
                                                                     $data['o'],
                                                                     $data['m'],
                                                                     $data['lf'],
                                                                     $data['lc'],
                                                                     $data['lt'],
                                                                     $data['pr'],
                                                                     $data['ps'],
                                                                     $data['id'])) {
              $totalSubscriptionAdded++;

              // Response
              $responses[] = sprintf(_("Subscription %s successfully activated! \n\nExpiration date: \n%s GMT \n\nSend <strong>#help</strong> command to view available options or request human <strong>#support</strong>"),
                                     convertSubscriptionDataToLink($data),
                                     gmdate('Y-m-d H:i', $expired));
          } else {

              // Response
              $responses[] = sprintf(_("Subscription %s could not be added! \n\nSend <strong>#help</strong> command to view available options or request human <strong>#support</strong>"),
                                     convertSubscriptionDataToLink($data));
          }

        // Subscriptions limit reached
        } else {

            // Response
            $responses[] = sprintf(_("Subscriptions limit (%s per account) reached! \n\nFollowing request has been declined: \n\n%s \n\nSend <strong>#help</strong> command to view available options or request human <strong>#support</strong>"),
                                   PROFILE_MAX_SUBSCRIPRIONS,
                                   convertSubscriptionDataToLink($data));
        }
    }

    if (!$responses) {
        $responses[] = _('Internal error! Please, contact us.');
    }

    return $responses;
}

$totalProfilesAdded       = 0;
$totalMessagesAdded       = 0;
$totalSubscriptionAdded   = 0;
$totalSubscriptionUpdated = 0;
$totalSubscriptionRemoved = 0;
$totalMessagesProcessed   = 0;

$time      = time();
$timeStart = microtime(true);

$modelProfile        = new ModelProfile(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelListing        = new ModelListing(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelSubscription   = new ModelSubscription(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$curlChat            = new CurlChat(OB_PROTOCOL, OB_HOST, OB_PORT, OB_USERNAME, OB_PASSWORD);

$modelSphinxFrontend = new ModelSphinx(SPHINX_HOST, SPHINX_PORT);

// Step 1
// Save new messages
if (false !== $conversations = $curlChat->getConversations(CHATBOT__CURL_TIMEOUT_GET_CONVERSATIONS)) {

    // Extract peers
    foreach ($conversations as $conversation) {

        $peerId = $curlChat->sanitize($conversation['peerId']);

        // Add new profile if not exits
        if (!$profileId = $modelProfile->profileExists($peerId)) {
             $profileId = $modelProfile->addProfile($peerId, $time);
             $totalProfilesAdded++;
        }

        // Extract and save messages
        if (false !== $messages = $curlChat->getMessages($peerId, CHATBOT__CURL_TIMEOUT_GET_MESSAGES)) {

            foreach ($messages as $message) {

                $messageId = $curlChat->sanitize($message['messageId']);

                if (!$modelProfile->profileMessageExists($profileId, $messageId)) {
                     $modelProfile->addProfileMessage($profileId,
                                                      $messageId,
                                                      $curlChat->sanitize($message['subject']),
                                                      $curlChat->sanitize(str_replace('&lt;=', '&amp;lt=', $message['message'])), // fix lt encoding
                                                      $curlChat->sanitize($message['outgoing']),
                                                      $time,
                                                      strtotime(preg_replace('/\.(\d+)/', '', $curlChat->sanitize($message['timestamp']))));
                     $totalMessagesAdded++;
                }
            }
        }

        // Mark profile messages as read
        // Disabled by UI
        // $curlChat->readMessages($peerId, '', CHATBOT__CURL_TIMEOUT_READ_MESSAGES);
    }
}

// Step 2
// Process messages
$chatBotMessages = [];
foreach ($modelProfile->profileMessagesQueue(CHATBOT__MODEL_PROFILE_MESSAGES_QUEUE) as $message) {

    // Collect unique chat bot-compatible messages
    if (0 === strpos($message['message'], '#')) {
        $chatBotMessages[sha1($message['profileId'] . $message['message'])] = $message;
    }

    // Update message processed
    $totalMessagesProcessed = $totalMessagesProcessed + $modelProfile->updateProfileMessageProcessed($message['profileId'], $message['messageId'], $time);
}

// Step 3
// Process chatbot-compatible messages
foreach ($chatBotMessages as $message) {

    // Parse message
    $part = explode(' ', $message['message']);

    // Detect command
    switch ($part[0]) {
        case '#help':

            $help  = _("<strong>#help</strong> - Supported commands & options \n\n");
            $help .= _("<strong>#support</strong> - Request human support \n\n");
            $help .= _("<strong>#index</strong> - Index profile and listings \n\n");
            $help .= _("<strong>#subscriptions <i>list|flush</i></strong> - Show or Delete all subscriptions \n\n");
            $help .= _("<strong>#subscribe <i>{url}|{url} [{int} hour(s)|day(s)|week(s)|month(s)]</i></strong> - Add or Update subscription by search URL. Expiration time optionally supported \n\n");
            $help .= _("<strong>#unsubscribe <i>{url}</i></strong> - Unsubscribe from URL \n\n");

            // Send response message
            $curlChat->sendMessage( $message['peerId'],
                                    '',
                                    $help,
                                    $message['message'], CHATBOT__CURL_TIMEOUT_SEND_MESSAGE);
        break;
        case '#support':

            // Send message
            $curlChat->sendMessage( OB_PEER_ID,
                                    '',
                                    sprintf(_("Support request has been received \n\nMessage: \n\n%s \n\nPeerID: \n\n%s"), $message['message'], $message['peerId']),
                                    1);

            // Send response message
            $curlChat->sendMessage( $message['peerId'],
                                    '',
                                    _('Support request has been sent. Please be patient.'),
                                    CHATBOT__CURL_TIMEOUT_SEND_MESSAGE);
        break;
        case '#index':

            if ($modelProfile->updateProfileIndexed($message['profileId'], 0) ||
                $modelProfile->updateListingsIndexed($message['profileId'], 0)) {
                $curlChat->sendMessage( $message['peerId'],
                                        '',
                                        _('Index request has been received and will be processed as quickly as possible.'),
                                        CHATBOT__CURL_TIMEOUT_SEND_MESSAGE);
            } else {
                $curlChat->sendMessage( $message['peerId'],
                                        '',
                                        _('Index request already received. Please be patient.'),
                                        CHATBOT__CURL_TIMEOUT_SEND_MESSAGE);
            }

        break;
        case '#subscriptions':

            if (isset($part[1])) {

                switch ($part[1]) {

                    // List subscriptions
                    case 'list':

                        $responses = [];

                        $subscriptionsTotal = $modelSubscription->getTotalSubscriptions($message['profileId']);

                        $responses[] = sprintf(_("You have %s %s"), $subscriptionsTotal, pluralSubscription($subscriptionsTotal, [_('subscription'), _('subscriptions'), _('subscriptions ')]));

                        if ($subscriptionsTotal) {
                            foreach ($modelSubscription->getSubscriptions($message['profileId']) as $subscription) {
                                $responses[] = sprintf(_("%s \n\nExpiration date: \n%s GMT"), convertSubscriptionDataToLink($subscription), gmdate('Y-m-d H:i', $subscription['expired']));
                            }
                        }

                        $responses[] = _("Send <strong>#help</strong> command to view available options or request human <strong>#support</strong>");

                        // Send response message
                        $curlChat->sendMessage($message['peerId'], '', implode(" \n\n", $responses), CHATBOT__CURL_TIMEOUT_SEND_MESSAGE);
                    break;

                    // Flush subscriptions
                    case 'flush':

                        $responses = [];

                        if ($totalSubscriptionFlushed = $modelSubscription->flushSubscriptions($message['profileId'])) {
                            $totalSubscriptionRemoved = $totalSubscriptionRemoved + $totalSubscriptionFlushed;
                            $responses[] = sprintf(_("%s %s was successfully deleted!"), $totalSubscriptionRemoved, pluralSubscription($totalSubscriptionRemoved, [_('subscription'), _('subscriptions'), _('subscriptions ')]));
                        } else {
                            $responses[] = sprintf(_("You have %s %s"), 0, pluralSubscription(0, [_('subscription'), _('subscriptions'), _('subscriptions ')]));
                        }

                        $responses[] = _("Send <strong>#help</strong> command to view available options or request human <strong>#support</strong>");

                        // Send response message
                        $curlChat->sendMessage($message['peerId'], '', implode(" \n\n", $responses), CHATBOT__CURL_TIMEOUT_SEND_MESSAGE);

                    break;
                    default:

                        // Send response message
                        $curlChat->sendMessage( $message['peerId'],
                                                '',
                                                sprintf(_("Incorrect <strong>#subscriptions <i>option</i></strong> in following request: \n\n<i>%s</i> \n\nSend <strong>#help</strong> command to view available options or request human <strong>#support</strong>"), $message['message']),
                                                CHATBOT__CURL_TIMEOUT_SEND_MESSAGE);
                }
            } else {

                  // Send response message
                  $curlChat->sendMessage( $message['peerId'],
                                          '',
                                          sprintf(_("<strong>#subscriptions <i>option</i></strong> required in following request: \n\n<i>%s</i> \n\nSend <strong>#help</strong> command to view available options or request human <strong>#support</strong>"), $message['message']),
                                          CHATBOT__CURL_TIMEOUT_SEND_MESSAGE);
            }
        break;
        case '#subscribe':

            switch (true) {

                // Lifetime subscription mode
                case (isset($part[1]) && is_numeric($part[1]) && $part[1] > 0 && isset($part[2]) && isset($part[3]) && $data = convertSubscriptionLinkToData($part[3])):

                    // Set defaults
                    $lifetime  = CHATBOT__MODEL_SUBSCRIPTION_DEFAULT_LIFETIME;
                    $responses = [];

                    // Set provided multiplier
                    $multiplier = (int) $part[1];

                    // Detect subscribe lifetime
                    switch ($part[2]) {
                        case 'hour':
                        case 'hours':
                            $lifetime = $multiplier * 3600;
                        break;
                        case 'day':
                        case 'days':
                            $lifetime = $multiplier * 3600 * 24;
                        break;
                        case 'week':
                        case 'weeks':
                            $lifetime = $multiplier * 3600 * 24 * 7;
                        break;
                        case 'month':
                        case 'months':
                            $lifetime = $multiplier * 3600 * 24 * 7 * 4;
                        break;
                    }

                    // Validate lifetime, check if provided only
                    if ($lifetime > CHATBOT__MODEL_SUBSCRIPTION_MAX_LIFETIME) {
                        $responses[] = _("Subscription time more than maximum value allowed!");
                    } elseif ($lifetime < CHATBOT__MODEL_SUBSCRIPTION_MIN_LIFETIME) {
                        $responses[] = _("Subscription time less than minimum value allowed!");
                    }

                    // Calculate lifetime
                    $expired = $lifetime + $time;

                    // Process subscription (using common function)
                    $chatbotProcessSubscriptionResponses = chatbotProcessSubscription( $modelSubscription,
                                                                                       $modelSphinxFrontend,
                                                                                       $message,
                                                                                       $data,
                                                                                       $time,
                                                                                       $expired,
                                                                                       $totalSubscriptionAdded,
                                                                                       $totalSubscriptionUpdated);

                    $responses[] = implode(" \n\n", $chatbotProcessSubscriptionResponses);

                    // Send response message
                    $curlChat->sendMessage($message['peerId'], '', implode(" \n\n", $responses), CHATBOT__CURL_TIMEOUT_SEND_MESSAGE);
                break;

                // Simple subscription mode
                case (isset($part[1]) && $data = convertSubscriptionLinkToData($part[1])):

                    // Set defaults
                    $responses = [];

                    // Calculate lifetime
                    $expired = CHATBOT__MODEL_SUBSCRIPTION_DEFAULT_LIFETIME + $time;

                    // Process subscription (using common function)
                    $chatbotProcessSubscriptionResponses = chatbotProcessSubscription( $modelSubscription,
                                                                                       $modelSphinxFrontend,
                                                                                       $message,
                                                                                       $data,
                                                                                       $time,
                                                                                       $expired,
                                                                                       $totalSubscriptionAdded,
                                                                                       $totalSubscriptionUpdated);

                    $responses[] = implode(" \n\n", $chatbotProcessSubscriptionResponses);

                    // Send response message
                    $curlChat->sendMessage($message['peerId'], '', implode(" \n\n", $responses), CHATBOT__CURL_TIMEOUT_SEND_MESSAGE);

                break;
                default:

                    // Send response message
                    $curlChat->sendMessage( $message['peerId'],
                                            '',
                                            sprintf(_("Incorrect <strong>#subscribe <i>option</i></strong> in following request: \n\n<i>%s</i> \n\nSend <strong>#help</strong> command to view available options or request human <strong>#support</strong>"), $message['message']),
                                            CHATBOT__CURL_TIMEOUT_SEND_MESSAGE);
            }
        break;
        case '#unsubscribe':

            if (isset($part[1]) && $data = convertSubscriptionLinkToData($part[1])) {

                if ($subscriptionId = $modelSubscription->subscriptionExists($message['profileId'],
                                                                             $data['protocol'],
                                                                             $data['t'],
                                                                             $data['q'],
                                                                             $data['s'],
                                                                             $data['o'],
                                                                             $data['m'],
                                                                             $data['lf'],
                                                                             $data['lc'],
                                                                             $data['lt'],
                                                                             $data['pr'],
                                                                             $data['ps'],
                                                                             $data['id'])) {

                     if ($totalSubscriptionDeleted = $modelSubscription->deleteSubscription($subscriptionId)) {

                         $totalSubscriptionRemoved = $totalSubscriptionRemoved + $totalSubscriptionDeleted;

                         $curlChat->sendMessage( $message['peerId'],
                                                 '',
                                                 sprintf(_("Subscription \n\n<i>%s</i> \n\nwas successfully deleted! \n\nSend <strong>#help</strong> command to view available options or request human <strong>#support</strong>"), convertSubscriptionDataToLink($data)),
                                                 CHATBOT__CURL_TIMEOUT_SEND_MESSAGE);

                     } else {
                         $curlChat->sendMessage( $message['peerId'],
                                                 '',
                                                 sprintf(_("Subscription with provided <strong><i>url</i></strong> does not exist in following request: \n\n<i>%s</i> \n\nSend <strong>#help</strong> command to view available options or request human <strong>#support</strong>"), $message['message']),
                                                 CHATBOT__CURL_TIMEOUT_SEND_MESSAGE);
                     }

                 } else {
                     $curlChat->sendMessage( $message['peerId'],
                                             '',
                                             sprintf(_("Subscription with provided <strong><i>url</i></strong> does not exist in following request: \n\n<i>%s</i> \n\nSend <strong>#help</strong> command to view available options or request human <strong>#support</strong>"), $message['message']),
                                             CHATBOT__CURL_TIMEOUT_SEND_MESSAGE);
                 }

            // Url option invalid
            } else {

                // Send response message
                $curlChat->sendMessage( $message['peerId'],
                                        '',
                                        sprintf(_("Subscription <strong><i>url</i></strong> required in following request: \n\n<i>%s</i> \n\nSend <strong>#help</strong> command to view available options or request human <strong>#support</strong>"), $message['message']),
                                        CHATBOT__CURL_TIMEOUT_SEND_MESSAGE);
            }
        break;
        default:

            // Send response message
            $curlChat->sendMessage( $message['peerId'],
                                    '',
                                    sprintf(_("Incorrect command in following request: \n\n<i>%s</i> \n\nSend <strong>#help</strong> command to view available options or request human <strong>#support</strong>"), $message['message']),
                                    CHATBOT__CURL_TIMEOUT_SEND_MESSAGE);
    }
}

$timeEnd = microtime(true);

// Debug output
echo sprintf("\nExecution time: %s\n\n", $timeEnd - $timeStart);
echo sprintf("Total profiles added: %s\n", $totalProfilesAdded);
echo sprintf("Total messages added: %s\n", $totalMessagesAdded);
echo sprintf("Total messages processed: %s\n\n", $totalMessagesProcessed);
echo sprintf("Total subscriptions added: %s\n", $totalSubscriptionAdded);
echo sprintf("Total subscriptions updated: %s\n", $totalSubscriptionUpdated);
echo sprintf("Total subscriptions removed: %s\n", $totalSubscriptionRemoved);
