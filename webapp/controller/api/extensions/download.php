<?php

$json = [
    'success'    => false,
    'message'    => _('Internal server error!'),
];

// Attributes exists
if (isset($_POST['name']) && isset($_POST['peerId']) && isset($_POST['clientVersion']) && isset($_POST['serverVersion'])) {

    // Profile exists
    if ($profileId = $modelProfile->getProfileIdByPeerId($_POST['peerId'])) {

      // Ip exists
      if ($remoteIp = getRemoteIP()) {

          // Register IP
          if (!$ipId = $modelIp->ipExists($remoteIp['address'], $remoteIp['version'])) {
               $ipId = $modelIp->addIp($remoteIp['address'], $remoteIp['version']);
          }

          // Extension exists
          if ($extensionId = $modelExtension->getExtensionIdByKeys($profileId,
                                                                   $_POST['name'],
                                                                   $_POST['clientVersion'],
                                                                   $_POST['serverVersion'])) {

              if ($modelExtension->addExtensionDownload($extensionId, $ipId, time())) {

                  $json = [
                      'success' => true,
                      'message' => _('ExtensionExtension download successfully registered!'),
                  ];
              }
          }
        }
    }

} else {
    $json = [
        'success'    => false,
        'message'    => _('Valid attributes required!'),
    ];
}

header('Content-Type: application/json');
echo json_encode($json);
