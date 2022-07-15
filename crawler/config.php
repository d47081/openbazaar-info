<?php

// HTTP
define('PROJECT_NAME', '');
define('PROJECT_DOMAIN', '');
define('PROJECT_HOST', '');

// OpenBazaar
define('OB_PROTOCOL', 'https');
define('OB_HOST', 'local.host');
define('OB_PORT', 4002);
define('OB_USERNAME', '');
define('OB_PASSWORD', '');
define('OB_PEER_ID', '');
define('OB_PEER_IP', '');
define('OB_PEER_IP_VERSION', 4);

// Sphinx
define('SPHINX_HOST', '127.0.0.1');
define('SPHINX_PORT', 9306);

// IP
define('IP_PROTOCOL', 'http');
define('IP_HOST', 'www.geoplugin.net');
define('IP_PORT', 80);

// Tor exit nodes
define('TOR_EXIT_NODES_PROTOCOL', 'https');
define('TOR_EXIT_NODES_HOST', 'check.torproject.org');
define('TOR_EXIT_NODES_PORT', 443);
define('TOR_CURL_EXIT_NODES_TIMEOUT', 30);
define('TOR_EXIT_NODE_EXPIRED', 31557600);

// Currency
define('CURRENCY_RATE_PROTOCOL', 'https');
define('CURRENCY_RATE_HOST', 'ticker.openbazaar.org');
define('CURRENCY_RATE_PORT', 443);
define('CURRENCY_RATE_TIMEOUT', 10);

// DB
define('DB_HOSTNAME', 'localhost');
define('DB_PORT', '3306');
define('DB_DATABASE', '');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');

// Sitemap
define('SITEMAP_BASE', '/public');
define('SITEMAP_DOMAIN', '');
define('SITEMAP_ITEMS_PER_SITEMAP', 50000);

// Common
define('DB_LANGUAGE_DEFAULT_LANGUAGE_ID', 1);

//System
define('SYSTEM_GO_PATH', '/usr/local/go/bin/go');
define('SYSTEM_OPENBAZAAR_PATH', 'openbazaard.go');

// Crawler
define('PEER__CURL_TIMEOUT_LOCALHOST', 1);                #
define('PEER__CURL_TIMEOUT_GET_PEERS', 10);               # Max 10 sec awaiting server response
define('PEER__CURL_TIMEOUT_GET_PEER_IPS', 2);             #

define('IP__MODEL_IP_INDEX_QUEUE', 1);                    # Warning: 120 API reqwests per minute MAX!
define('IP__CURL_TIMEOUT_GET_LOCATION', 10);              #

define('PROFILE__MODEL_PROFILE_INDEX_QUEUE', 5);          # 12 queries per minute MAX
define('PROFILE__CURL_TIMEOUT_GET_PROFILE', 5);           # Min 4, Max 10 sec/peer (PROFILE__CURL_TIMEOUT_GET_PROFILE + PROFILE__CURL_TIMEOUT_GET_PROFILE_FOLLOWING + PROFILE__CURL_TIMEOUT_GET_PROFILE_FOLLOWERS + PROFILE__CURL_TIMEOUT_GET_LISTINGS)
define('PROFILE__CURL_TIMEOUT_GET_PROFILE_RATINGS', 5);   #
define('PROFILE__CURL_TIMEOUT_GET_PROFILE_RATING', 5);    #
define('PROFILE__CURL_TIMEOUT_GET_PROFILE_FOLLOWING', 5); #
define('PROFILE__CURL_TIMEOUT_GET_PROFILE_FOLLOWERS', 5); #
define('PROFILE__CURL_TIMEOUT_GET_LISTINGS', 5);          #
define('PROFILE__CURL_TIMEOUT_GET_RATINGS', 5);           #
define('PROFILE__CURL_TIMEOUT_FOLLOW', 5);                #

define('LISTING__MODEL_LISTING_INDEX_QUEUE', 5);          # 6 queries per minute MAX
define('LISTING__CURL_TIMEOUT_GET_IPNS_LISTING', 5);      # Max 10 sec/listing (LISTING__CURL_TIMEOUT_GET_IPNS_LISTING + LISTING__CURL_TIMEOUT_GET_IPFS_LISTING)
define('LISTING__CURL_TIMEOUT_GET_IPFS_LISTING', 5);      #
define('LISTING__REMOVED_DELETE_TIMEOUT', 0);             # Listings where removed + LISTING__REMOVED_DELETE_TIMEOUT < current time

define('PROFILE_ONLINE_TIME', 900); // Value also using in the sphinx index, sync it!
define('PROFILE_MAX_SUBSCRIPRIONS', 5); // Value also using in the sphinx index, sync it!

define('CHATBOT__MODEL_SUBSCRIPTION_DEFAULT_LIFETIME', 2629800); # Should be synced with website config!
define('CHATBOT__MODEL_SUBSCRIPTION_MIN_LIFETIME', 3600);        # Should be synced with website config!
define('CHATBOT__MODEL_SUBSCRIPTION_MAX_LIFETIME', 31557600);    # Should be synced with website config!

define('SUBSCRIPTION__MODEL_SUBSCRIPTION_QUEUE', 100);

define('CHATBOT__MODEL_PROFILE_MESSAGES_QUEUE', 1000);
define('CHATBOT__CURL_TIMEOUT_GET_CONVERSATIONS', 10);
define('CHATBOT__CURL_TIMEOUT_GET_MESSAGES', 5);
define('CHATBOT__CURL_TIMEOUT_SEND_MESSAGE', 5);
define('CHATBOT__CURL_TIMEOUT_READ_MESSAGES', 5);

// Errors
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

define('LOG_ERROR_PATH', 'openbazaar-crawler.log');

set_error_handler(function ($code, $message, $file, $line) {

    if (error_reporting() === 0) {
        return false;
    }

    switch ($code) {
        case E_NOTICE:
        case E_USER_NOTICE:
            $error = 'Notice';
            break;
        case E_WARNING:
        case E_USER_WARNING:
            $error = 'Warning';
            break;
        case E_ERROR:
        case E_USER_ERROR:
            $error = 'Fatal Error';
            break;
        default:
            $error = 'Unknown';
            break;
    }

    $handle = fopen(LOG_ERROR_PATH, 'a');
    fwrite($handle, date('Y-m-d G:i:s') . ' - ' . $error . ': ' . $message . ' File: ' . $file . ' Line:' . $line . "\n");
    fclose($handle);

    //@mail(EMAIL_CONTACT_ADDRESS, 'ERROR DETECTED', 'LOGGED');

    return true;
});
