<?php

$json = [
    'success'    => false,
    'message'    => _('Internal server error!'),
    'extensions' => []
];

if (isset($_GET['clientVersion']) && !empty($_GET['clientVersion']) &&
    isset($_GET['serverVersion']) && !empty($_GET['serverVersion'])) {

    $extensions = [];
    foreach ($modelExtension->getExtensions($_GET['clientVersion'], $_GET['serverVersion']) as $extension) {

        $mirrors = [];
        foreach ($modelExtension->getExtensionMirrors($extension['extensionId']) as $mirror) {
            $mirrors[] = $mirror['url'];
        }

        if ($mirrors) {
            $extensions[] = [
                'name'          => (string) $extension['name'],
                'version'       => (string) $extension['version'],
                'serverVersion' => (string) $extension['serverVersion'],
                'clientVersion' => (string) $extension['clientVersion'],
                'title'         => (string) $extension['title'],
                'description'   => (string) $extension['description'],
                'tags'          => (array) explode(',', $extension['tags']),
                'author'        => [
                    'peerId' => (string) $extension['peerId'],
                ],
                'mirrors'   => (array) $mirrors,
                'logo'      => (string) $extension['logo'],
                'downloads' => (int) $modelExtension->getTotalExtensionDownloads($extension['extensionId']),
            ];
        }
    }

    $json = [
        'success'    => true,
        'message'    => _('Extensions successfully loaded!'),
        'extensions' => $extensions
    ];

} else {
    $json = [
        'success'    => false,
        'message'    => _('Valid clientVersion / serverVersion required!'),
        'extensions' => []
    ];
}

header('Content-Type: application/json');
echo json_encode($json);
