<?php

function pluralSubscription($number, array $texts) {
    $cases = [2, 0, 1, 1, 1, 2];
    return $texts[(($number % 100) > 4 && ($number % 100) < 20) ? 2 : $cases[min($number % 10, 5)]];
}

function sanitizeSubscriptionData($string) {

    // Encode URL
    $string = urldecode($string);

    // Remove all tags
    $string = strip_tags($string);

    // Remove all symbols except required
    $string = preg_replace("/[^-\d\w\s\|\/]/ui", " ", $string);

    // Remove double spaces
    $string = preg_replace('/\s+/', ' ',$string);

    // Trim spaces
    $string = trim($string);

    return $string;
}

function convertSubscriptionLinkToData($url) {

    if (0 === strpos($url, 'ob://') || 0 === strpos($url, PROJECT_HOST)) {

        $url = urldecode($url);
        $url = str_replace('&amp;', '&', $url);

        preg_match('/^([\w]+):\/\/(.*)\?(.*)/ui', $url, $matches);

        if (isset($matches[1]) && in_array($matches[1], ['ob', 'http', 'https']) &&
            isset($matches[3])) {

              $data['protocol'] = $matches[1];

              parse_str($matches[3], $query);

              $data['t']  = isset($query['t']) && in_array($query['t'], ['profile', 'listing']) ? $query['t'] : ($matches[1] == 'ob' ? 'listing' : '');
              $data['s']  = isset($query['s']) && in_array($query['s'], ['online','added','price']) ? $query['s'] : '';
              $data['o']  = isset($query['o']) && in_array($query['o'], ['asc', 'desc']) ? $query['o'] : '';
              $data['m']  = isset($query['m']) && in_array($query['m'], ['true', 'false']) ? $query['m'] : '';
              $data['q']  = isset($query['q'])  ? sanitizeSubscriptionData($query['q']) : '';
              $data['lf'] = isset($query['lf']) ? sanitizeSubscriptionData($query['lf']) : '';
              $data['lc'] = isset($query['lc']) ? sanitizeSubscriptionData($query['lc']) : '';
              $data['lt'] = isset($query['lt']) ? sanitizeSubscriptionData($query['lt']) : '';
              $data['pr'] = isset($query['pr']) ? sanitizeSubscriptionData($query['pr']) : '';
              $data['ps'] = isset($query['ps']) ? ($matches[1] == 'ob' ? '' : sanitizeSubscriptionData($query['ps'])) : ''; // reserved
              $data['id'] = isset($query['id']) ? sanitizeSubscriptionData($query['id']) : '';

              if ($data['protocol'] &&
                  $data['t'] && ($data['q'] ||
                                 $data['s'] ||
                                 $data['o'] ||
                                 $data['m'] ||
                                 $data['lf'] ||
                                 $data['lc'] ||
                                 $data['lt'] ||
                                 $data['pr'] ||
                                 $data['ps'] ||
                                 $data['id'])) {
                  return $data;
              }
        }
    }

    return false;
}

function convertSubscriptionDataToLink($data) {

    $link    = '';
    $queries = [];

    if (isset($data['protocol'])) {

        switch ($data['protocol']) {
            case 'ob':

                $link .= 'ob://search';

                if (isset($data['t'])) {

                    switch ($data['t']) {
                        case 'listing':
                          $link .= '/listings';
                          $queries[] = 'providerQ=' . urlencode(PROJECT_HOST);
                        break;
                        default:
                            return false;
                    }

                } else {
                    return false;
                }

            break;
            case 'http':
            case 'https':

                $link .= PROJECT_HOST . '/search';

                if (isset($data['t'])) {

                    switch ($data['t']) {
                        case 'listing':
                            $queries[] = 't=listing';
                        break;
                        case 'profile':
                            $queries[] = 't=profile';
                        break;
                        default:
                            return false;
                    }

                } else {
                    return false;
                }

            break;
            default:
                return false;
        }
    }

    if (isset($data['q']) && $data['q']) {
        $queries[] = 'q=' . $data['q'];
    }

    if (isset($data['s']) && $data['s']) {
        $queries[] = 's=' . $data['s'];
    }

    if (isset($data['o']) && $data['o']) {
        $queries[] = 'o=' . $data['o'];
    }

    if (isset($data['m']) && $data['m']) {
        $queries[] = 'm=' . $data['m'];
    }

    if (isset($data['lf']) && $data['lf']) {
        $queries[] = 'lf=' . $data['lf'];
    }

    if (isset($data['t']) && $data['t'] == 'listing') {
        if (isset($data['lc']) && $data['lc']) {
            $queries[] = 'lc=' . sanitizeSubscriptionData($data['lc']);
        }

        if (isset($data['lt']) && $data['lt']) {
            $queries[] = 'lt=' . sanitizeSubscriptionData($data['lt']);
        }
    }

    if (isset($data['pr']) && $data['pr']) {
        $queries[] = 'pr=' . $data['pr'];
    }

    if (isset($data['ps']) && $data['ps']) {
        $queries[] = 'ps=' . $data['ps'];
    }

    if (isset($data['id']) && $data['id']) {
        $queries[] = 'id=' . $data['id'];
    }

    if ($queries) {
        return htmlentities($link . '?' . implode('&', $queries));
    }

    return false;
}

function convertSubscriptionDataToSearchFilters($data) {

    $filters = [];

    if ($data['m']) {
        $filters['m'] = $data['m'];
    }

    if ($data['id']) {
        $filters['id'] = explode('|', $data['id']);
    }

    if ($data['lf']) {
        $filters['lf'] = explode('|', $data['lf']);
    }

    if ($data['ps']) {
        $filters['ps'] = explode('|', $data['ps']);
    }

    if ($data['pr']) {
        $filters['pr'] = explode('|', $data['pr']);
    }

    if ($data['lt']) {
        $filters['lt'] = explode('|', str_replace('-', '_', $data['lt']));
    }

    if ($data['lc']) {
        $filters['lc'] = explode('|', str_replace('-', '_', $data['lc']));
    }

    return $filters;
}
