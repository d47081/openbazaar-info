<?php

require('../bootstrap.php');

if (isset($_GET['_route_'])) {

    if ($parts = explode('/', $_GET['_route_'])) {
        switch ($parts[0]) {
            case 'api':
                switch ($_GET['_route_']) {
                    case 'api':
                    case 'api/image':
                    case 'api/extensions/list':
                    case 'api/extensions/download':
                    case 'api/contact':
                    case 'api/profile':
                    case 'api/listing':
                    case 'api/search/subscribe':
                    case 'api/search/listing':
                    case 'api/search/profile':
                    case 'api/search/sorts':
                    case 'api/search/filters/ratings':
                    case 'api/search/filters/countries':
                    case 'api/search/filters/conditions':
                    case 'api/search/filters/types':
                    case 'api/search/filters/moderators':
                    case 'api/search/filters/protected':
                    case 'api/search/filters/tor':
                    case 'api/profile/ratings':
                    case 'api/profile/connections':
                    case 'api/profile/followers':
                    case 'api/profile/following':
                    case 'api/profile/listings':
                    case 'api/profile/contacts':
                    case 'api/listing/moderators':
                    case 'api/listing/shippings':
                        require(PROJECT_DIR . '/controller/' . sanitizeRequest($_GET['_route_']) . '.php');
                    break;
                    default:
                        require(PROJECT_DIR . '/controller/error/404.php');
                }
            break;
            //case 'history':
            //    require(PROJECT_DIR . '/controller/history.php');
            //break;
            case 'search':
                require(PROJECT_DIR . '/controller/search.php');
            break;
            case 'profile':
                if (isset($parts[1])) {
                    $_GET['peerId'] = sanitizeRequest($parts[1]);
                    require(PROJECT_DIR . '/controller/profile.php');

                } else {
                    require(PROJECT_DIR . '/controller/error/404.php');
                }
            break;
            case 'listing':
                if (isset($parts[1])) {
                    $_GET['hash'] = sanitizeRequest($parts[1]);
                    require(PROJECT_DIR . '/controller/listing.php');

                } else {
                    require(PROJECT_DIR . '/controller/error/404.php');
                }
            break;
            case 'error/404':
                require(PROJECT_DIR . '/controller/error/404.php');
            break;
            case 'error/500':
                require(PROJECT_DIR . '/controller/error/500.php');
            break;
            default:
                require(PROJECT_DIR . '/controller/error/404.php');
        }
    } else {
        require(PROJECT_DIR . '/controller/error/404.php');
    }

} else {

    // API request
    if (isset($_GET['q'])) {
        require(PROJECT_DIR . '/controller/api/openbazaar.php');

    // Main page
    } else {
        require(PROJECT_DIR . '/controller/index.php');
    }
}
