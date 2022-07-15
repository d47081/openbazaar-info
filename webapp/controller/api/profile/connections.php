<?php

$json = [
    'success'     => (bool) false,
    'page'        => (int) 1,
    'total'       => (int) 0,
    'message'     => (string) _('Internal server error!'),
    'connections' => (array) [],
];

$page = isset($_GET['page']) && $_GET['page'] >= 1 ? (int) $_GET['page'] : 1;

if (isset($_GET['peerId']) && $profileId = $modelProfile->getProfileIdByPeerId(sanitizeRequest($_GET['peerId']))) {

    $connections = [];
    $total = $modelProfile->getTotalProfileConnections($profileId);
    foreach ($modelProfile->getProfileConnections($profileId, ($page - 1) * PROFILE_CONNECTIONS_LIMIT, PROFILE_CONNECTIONS_LIMIT) as $value) {

        $flagFile      = mb_strtolower($value['countryCode'], 'UTF-8');
        $flagExists    = file_exists(sprintf('%s/public/image/flag/4x3/%s.svg', PROJECT_DIR, $flagFile));

        if (($value['countryCode'] && $value['country']) || $value['region'] || $value['city']) {

            $locations = [];

            if ($value['city']) {
                $locations[] = $value['city'];
            }

            if ($value['region']) {
                $locations[] = $value['region'];
            }

            if ($value['country']) {
                $locations[] = $value['country'];
            }

            $location = implode(', ', $locations);
        } else {
            $location = _('Unknown');
        }

        $connections[] = [
            'time'        => (string) timeLeft($value['time']),
            'frequency'   => (string) round($value['frequency'] * 100 / $total) . '%',
            'tor'         => (bool) $value['tor'],
            'location'    => [
                'countryCode' => (string) $value['countryCode'],
                'country'     => (string) $value['country'],
                'region'      => (string) $value['region'],
                'city'        => (string) $value['city'],
                'flag'        => $flagExists ? sprintf('image/flag/4x3/%s.svg', $flagFile) : false,
                'name'        => (string) $location
            ],
        ];
    }

    $total = $modelProfile->getDistinctTotalProfileConnections($profileId);

    $json = [
        'success'     => (bool) true,
        'page'        => (int) $page,
        'total'       => (int) $total,
        'message'     => $connections ? sprintf(_('%s %s'), $total, plural($total, [_('connection found!'), _('connections found!'), _('connections found!')])) : _('Connections not found!'),
        'connections' => (array) $connections,
    ];
} else {
    $json = [
        'success'     => (bool) false,
        'page'        => (int) $page,
        'total'       => (int) 0,
        'message'     => (string) _('Valid PeerId required!'),
        'connections' => (array) [],
    ];
}

header('Content-Type: application/json');
echo json_encode($json);
