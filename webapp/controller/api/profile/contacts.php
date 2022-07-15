<?php

$json = [
    'success'    => (bool) false,
    'total'      => (int) 0,
    'message'    => (string) _('Internal server error!'),
    'contacts'   => (array) [],
];

if (isset($_GET['peerId']) && $profile = $modelProfile->getProfileByPeer(sanitizeRequest($_GET['peerId']))) {

    $total    = 0;
    $contacts = [];

    if ($profile['website']) {
        $contacts['website'] = [
            'text' => (string) _('Website'),
            'link' => (string) $profile['website'],
        ];
        $total++;
    }

    if ($profile['email']) {
        $contacts['email'] = [
            'text'    => (string) _('E-mail'),
            'address' => (string) $profile['email'],
        ];
        $total++;
    }

    if ($profile['phoneNumber']) {
        $contacts['phone'] = [
            'text'   => (string) _('Phone Number'),
            'number' => (string) $profile['phoneNumber'],
        ];
        $total++;
    }

    if ($profileSocials = $modelProfile->getProfileSocials($profile['profileId'])) {
        foreach ($profileSocials as $value) {
            $contacts['social'][] = [
                'type'     => (string) $value['type'],
                'username' => (string) $value['username'],
            ];
            $total++;
        }
    }

    $json = [
        'success'   => (bool) true,
        'total'     => (int) $total,
        'message'   => $contacts ? sprintf(_('%s %s'), $total, plural($total, [_('contact found!'), _('contacts found!'), _('contacts found!')])) : _('Contacts not found!'),
        'contacts'  => (array) $contacts,
    ];
} else {
    $json = [
        'success'   => (bool) false,
        'total'     => (int) 0,
        'message'   => (string) _('Valid PeerId required!'),
        'contacts'  => (array) [],
    ];
}

header('Content-Type: application/json');
echo json_encode($json);
