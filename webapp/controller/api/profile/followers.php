<?php

$json = [
    'success'    => (bool) false,
    'page'       => (int) 1,
    'total'      => (int) 0,
    'message'    => (string) _('Internal server error!'),
    'followers'  => (array) [],
];

$page = isset($_GET['page']) && $_GET['page'] >= 1 ? (int) $_GET['page'] : 1;

if (isset($_GET['peerId']) && $profileId = $modelProfile->getProfileIdByPeerId(sanitizeRequest($_GET['peerId']))) {

    $followers = [];
    if ($profileFollowers = $modelProfile->getProfileFollowers($profileId, ($page - 1) * PROFILE_FOLLOWERS_LIMIT, PROFILE_FOLLOWERS_LIMIT)) {
        foreach ($profileFollowers as $value) {
            $online = (time() - $value['online'] < PROFILE_ONLINE_TIME);
            $followers[] = [
                'peerId'           => (string) $value['peerId'],
                'avatar'           => (string) $value['avatarHashMedium'],
                'available'        => (bool) $value['updated'],
                'name'             => (string) formatText($value['name']),
                'shortDescription' => $value['updated'] ? formatText($value['shortDescription'], false, true) : _('Some info can\'t be included because it is not indexed...'),
                'online'           => (array)  [
                    'status' => $online ? 'online' : ($value['online'] ? 'active' : 'passive'),
                    'text'   => $online ? _('Online') : ($value['online'] ? sprintf(_('Active %s'),  timeLeft($value['online'])) : _('Passive'))
                ],
                'nsfw'            => [
                    'status'      => (bool) $value['nsfw'],
                    'text'        => $value['nsfw'] ? _('Content for adults or prohibited by law in your country') : _('Safe content'),
                ],
            ];
        }
    }

    $total = $modelProfile->getTotalProfileFollowers($profileId);

    $json  = [
        'success'   => (bool) true,
        'page'      => (int) $page,
        'total'     => (int) $total,
        'message'   => $followers ? sprintf(_('%s %s'), $total, plural($total, [_('follower found!'), _('followers found!'), _('followers found!')])) : _('Followers not found!'),
        'followers' => (array) $followers,
    ];

} else {
    $json = [
        'success'    => (bool) false,
        'page'       => (int) $page,
        'total'      => (int) 0,
        'message'    => (string) _('Valid PeerId required!'),
        'followers'  => (array) [],
    ];
}

header('Content-Type: application/json');
echo json_encode($json);
