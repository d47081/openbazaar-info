<?php

// HTTP
define('PROJECT_HOST', '');
define('PROJECT_LOGO', '');
define('PROJECT_NAME', '');
define('PROJECT_DIR', __DIR__);

// DB
define('DB_HOSTNAME', 'localhost');
define('DB_PORT', 3306);
define('DB_DATABASE', '');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');

// OpenBazaar
define('OB_PROTOCOL', 'https');
define('OB_HOST', '');
define('OB_PORT', 4002);
define('OB_USERNAME', '');
define('OB_PASSWORD', '');
define('OB_CONNECTION_TIMEOUT_IMAGE', 10);

define('OB_PEER_ID', '');
define('OB_PEER_LISTING_HASH', '');
define('OB_IMAGE_HASH', '');

// Sphinx
define('SPHINX_HOST', '');
define('SPHINX_PORT', 9306);

// App
define('SERVER_ONLINE_TIME', 900);
define('PROFILE_ONLINE_TIME', 900); // Value also using in the sphinx index, sync it!
define('PROFILE_LISTINGS_LIMIT', 20);
define('PROFILE_RATINGS_LIMIT', 20);
define('PROFILE_FOLLOWING_LIMIT', 20);
define('PROFILE_FOLLOWERS_LIMIT', 20);
define('PROFILE_CONNECTIONS_LIMIT', 20);

define('LISTING_SHIPPINGS_LIMIT', 20);
define('LISTING_MODERATORS_LIMIT', 20);

define('SEARCH_LISTINGS_LIMIT', 20);
define('SEARCH_PROFILES_LIMIT', 20);
define('SEARCH_SUBSCRIBE_EXPIRED_VALUE', 'day');
define('SEARCH_SUBSCRIBE_EXPIRED_TIME', 30);

define('SEARCH_SUBSCRIBE_DEFAULT_LIFETIME', 2629800); # Should be synced with crawler config!
define('SEARCH_SUBSCRIBE_MAX_LIFETIME', 31557600);    # Should be synced with crawler config!
define('SEARCH_SUBSCRIBE_MIN_LIFETIME', 3600);        # Should be synced with crawler config!

define('LISTING_DESCRIPTION_COLLAPSE_LENGHT', 1000);

// Update browser cache: time|false
define('APP_SCRIPTS_UPDATE', date('Ym'));

// Errors
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

define('LOG_ERROR_PATH', '');

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

    return true;
});
