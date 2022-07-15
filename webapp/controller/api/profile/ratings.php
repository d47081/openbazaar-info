<?php

$json = [
    'success'    => (bool) false,
    'page'       => (int) 1,
    'total'      => (int) 0,
    'message'    => (string) _('Internal server error!'),
    'ratings'    => (array) [],
];

$page = isset($_GET['page']) && $_GET['page'] >= 1 ? (int) $_GET['page'] : 1;

if (isset($_GET['peerId']) && $profileId = $modelProfile->getProfileIdByPeerId(sanitizeRequest($_GET['peerId']))) {

    $total = 0;
    $ratings = [];
    if ($profileRatings = $modelProfile->getProfileRatings($profileId, ($page - 1) * PROFILE_RATINGS_LIMIT, PROFILE_RATINGS_LIMIT)) {
        foreach ($profileRatings as $value) {

            $online    = (time() - $value['online'] < PROFILE_ONLINE_TIME);
            $average   = round(($value['customerService'] + $value['deliverySpeed'] + $value['description'] + $value['overall'] + $value['quality']) / 5);
            $ratings[] = [
                'peerId'           => (string) $value['peerId'],
                'available'        => (bool) $value['updated'],
                'name'             => (string) formatText($value['name']),
                'online'           => (array) [
                    'status' => $online ? 'online' : ($value['online'] ? 'active' : 'passive'),
                    'text'   => $online ? _('Online') : ($value['online'] ? sprintf(_('Active %s'),  timeLeft($value['online'])) : _('Passive'))
                ],
                'time'            => (string) sprintf(_('Sent %s'), timeLeft($value['time'])),
                'review'          => (string) formatText($value['review'], false, true),
                'status'          => $average < 3 ? 'low' : ($average == 5 ? 'high' : 'medium'),
                'average'         => (int) $average,
                'customerService' => (array) [
                    'value'  => (int) $value['customerService'],
                    'text'   => (string) sprintf(_('Service: %s'), $value['customerService']),
                    'status' => $value['customerService'] < 3 ? 'low' : ($value['customerService'] == 5 ? 'high' : 'medium'),
                ],
                'deliverySpeed' => [
                    'value'  => (int) $value['deliverySpeed'],
                    'text'   => (string) sprintf(_('Speed: %s'), $value['deliverySpeed']),
                    'status' => $value['deliverySpeed'] < 3 ? 'low' : ($value['deliverySpeed'] == 5 ? 'high' : 'medium'),
                ],
                'description' => [
                    'value'  => (int) $value['description'],
                    'text'   => (string) sprintf(_('Description: %s'), $value['description']),
                    'status' => $value['description'] < 3 ? 'low' : ($value['description'] == 5 ? 'high' : 'medium'),
                ],
                'overall' => [
                    'value'  => (int) $value['overall'],
                    'text'   => (string) sprintf(_('Overall: %s'), $value['overall']),
                    'status' => $value['overall'] < 3 ? 'low' : ($value['overall'] == 5 ? 'high' : 'medium'),
                ],
                'quality' => [
                    'value'  => (int) $value['quality'],
                    'text'   => (string) sprintf(_('Quality: %s'), $value['quality']),
                    'status' => $value['quality'] < 3 ? 'low' : ($value['quality'] == 5 ? 'high' : 'medium'),
                ],
            ];

            $total++;
        }
    }

    $total = $modelProfile->getTotalProfileRatings($profileId);

    $json = [
        'success'   => (bool) true,
        'page'      => (int) $page,
        'total'     => (int) $total,
        'message'   => $ratings ? sprintf(_('%s %s'), $total, plural($total, [_('rating found!'), _('ratings found!'), _('ratings found!')])) : _('Ratings not found!'),
        'ratings'   => (array) $ratings,
    ];
} else {
    $json = [
        'success'    => (bool) false,
        'page'       => (int) $page,
        'total'      => (int) 0,
        'message'    => (string) _('Valid PeerId required!'),
        'ratings'    => (array) [],
    ];
}

header('Content-Type: application/json');
echo json_encode($json);
