<?php

$json = [
    'success' => false,
    'data'    => [],
    'message' => _('Internal server error!'),
];

function convertSearchSubscribeRequestToLink($data) {

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

        if (isset($data['q']) && $data['q']) {
            $queries[] = 'q=' . str_replace(' ', '+', sanitizeSearchQuery($data['q']));
        }

        if (isset($data['s']) && $data['s']) {
            $queries[] = 's=' . sanitizeRequest($data['s']);
        }

        if (isset($data['o']) && $data['o']) {
            $queries[] = 'o=' . sanitizeRequest($data['o']);
        }

        if (isset($data['m']) && $data['m']) {
            $queries[] = 'm=' . sanitizeRequest($data['m']);
        }

        if (isset($data['lf']) && $data['lf']) {
            $queries[] = 'lf=' . sanitizeRequest($data['lf']);
        }

        if (isset($_GET['t']) && $_GET['t'] == 'listing') {
            if (isset($data['lc']) && $data['lc']) {
                $queries[] = 'lc=' . sanitizeRequest($data['lc']);
            }

            if (isset($data['lt']) && $data['lt']) {
                $queries[] = 'lt=' . sanitizeRequest($data['lt']);
            }
        }
        if (isset($data['pr']) && $data['pr']) {
            $queries[] = 'pr=' . sanitizeRequest($data['pr']);
        }

        if (isset($data['ps']) && $data['ps']) {
            $queries[] = 'ps=' . sanitizeRequest($data['ps']);
        }

        if (isset($data['id']) && $data['id']) {
            $queries[] = 'id=' . sanitizeRequest($data['id']);
        }

        if ($queries) {
            return htmlentities($link . '?' . implode('&', $queries));
        }
    }

    return false;
}

if (isset($_GET)) {

    $protocol = $_GET['protocol'] = isset($_GET['protocol']) && in_array($_GET['protocol'], ['ob','https']) ? $_GET['protocol'] : 'https';
    $expired  = $_GET['expired']  = isset($_GET['expired']) && in_array($_GET['expired'], ['hour','day','week','month']) ? $_GET['expired'] : 'day';
    $time     = $_GET['time']     = isset($_GET['time']) && $_GET['time'] > 0 ? (int) $_GET['time'] : 30;

    if ($link = convertSearchSubscribeRequestToLink($_GET)) {

        // Validate
        $lifetime = SEARCH_SUBSCRIBE_DEFAULT_LIFETIME;

        // Detect subscribe lifetime
        switch ($expired) {
            case 'hour':
                $lifetime = $time * 3600;
            break;
            case 'day':
                $lifetime = $time * 3600 * 24;
            break;
            case 'week':
                $lifetime = $time * 3600 * 24 * 7;
            break;
            case 'month':
                $lifetime = $time * 3600 * 24 * 7 * 4;
            break;
        }

        $error = false;
        if ($lifetime > SEARCH_SUBSCRIBE_MAX_LIFETIME || $lifetime < SEARCH_SUBSCRIBE_MIN_LIFETIME) {
            $error = _('Warning: usupported subscription time!');
        }

        $json = [
            'success' => true,
            'message' => _('Subscribe data received!'),
            'data'    => [
                'title'    => _('Subscribe to updates'),
                'peerId'   => OB_PEER_ID,
                'text'     => sprintf(_('Send following message from your OpenBazaar account to our <a href="ob://%s">Node</a>:'), OB_PEER_ID),
                'help'     => _('Feel free to manage your subscriptions by using'),
                'command'  => sprintf(_('<strong>#subscribe %s %s</strong> %s'), $time, plural($time, [$expired, $expired . 's', $expired . 's']), $link),
                'options'  => [
                    'protocol' => [
                        [
                          'value'    => 'https',
                            'text'     => 'https://',
                            'selected' => $protocol == 'https' ? true : false,
                        ],
                    ],
                    'time' => $time,
                    'expired' => [
                        [
                          'value'    => 'hour',
                          'text'     => _('Hours'),
                          'selected' => $expired == 'hour' ? true : false,
                        ],
                        [
                          'value'    => 'day',
                          'text'     => _('Days'),
                          'selected' => $expired == 'day' ? true : false,
                        ],
                        [
                          'value'    => 'week',
                          'text'     => _('Weeks'),
                          'selected' => $expired == 'week' ? true : false,
                        ],
                        [
                          'value'    => 'month',
                          'text'     => _('Months'),
                          'selected' => $expired == 'month' ? true : false,
                        ],
                    ],
                ],
                'error' => $error,
            ],
        ];

        if (isset($_GET['t']) && $_GET['t'] == 'listing') {
            $json['data']['options']['protocol'][] = [
                'value'    => 'ob',
                'text'     => 'ob://',
                'selected' => $protocol == 'ob' ? true : false,
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($json);
