<?php

$json = [
    'success'     => false,
    'message'     => _('Internal server error!')
];

if (isset($_POST['peerid']) && isset($_POST['message']) && !empty($_POST['message'])) {

    if ($remoteIp = getRemoteIP()) {
        $ip = $remoteIp['address'];
    } else {
        $ip = '';
    }

    $curlChat->sendMessage(OB_PEER_ID, '', sprintf(_("Website message: \n\n%s \n\nPeerID: \n\n%s \n\nIP: \n\n%s"), sanitizeRequest($_POST['message']), sanitizeRequest($_POST['peerid']), $ip), 1);

    $json = [
        'success'     => true,
        'message'     => _('Message successfully sent!')
    ];

} else {
    $json = [
        'success'     => false,
        'message'     => _('Message required!')
    ];
}

header('Content-Type: application/json');
echo json_encode($json);
