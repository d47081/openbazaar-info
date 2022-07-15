<?php

$json = [
    'success'   => (bool) false,
    'page'      => (int) 1,
    'total'     => (int) 0,
    'message'   => (string) _('Internal server error!'),
    'following' => (array) [],
];

$page = isset($_GET['page']) && $_GET['page'] >= 1 ? (int) $_GET['page'] : 1;

if (isset($_GET['peerId']) && $profileId = $modelProfile->getProfileIdByPeerId(sanitizeRequest($_GET['peerId']))) {

    $following = [];
    if ($profileFollowing = $modelProfile->getProfileFollowing($profileId, ($page - 1) * PROFILE_FOLLOWING_LIMIT, PROFILE_FOLLOWING_LIMIT)) {
        foreach ($profileFollowing as $value) {

            $online = (time() - $value['online'] < PROFILE_ONLINE_TIME);
            $following[] = [
                'peerId'           => (string) $value['peerId'],
                'avatar'           => (string) $value['avatarHashMedium'],
                'available'        => (bool) $value['updated'],
                'name'             => (string) formatText($value['name']),
                'shortDescription' => $value['updated'] ? formatText($value['shortDescription'], false, true) : _('Some info can\'t be included because it is not indexed...'),
                'online'           => (array) [
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

    $total = $modelProfile->getTotalProfileFollowing($profileId);
    $json  = [
        'success'   => (bool) true,
        'page'      => (int) $page,
        'total'     => (int) $total,
        'message'   => $following ? sprintf(_('%s %s'), $total, plural($total, [_('following found!'), _('followings found!'), _('followings found!')])) : _('Followings not found!'),
        'following' => (array) $following,
    ];
} else {
    $json = [
        'success'   => (bool) false,
        'page'      => (int) $page,
        'total'     => (int) 0,
        'message'   => (string) _('Valid PeerId required!'),
        'following' => (array) [],
    ];
}

header('Content-Type: application/json');
echo json_encode($json);
