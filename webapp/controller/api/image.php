<?php

if (isset($_GET['hash'])) {
    if (false !== $image = $curlImage->getImage(sanitizeRequest($_GET['hash']), OB_CONNECTION_TIMEOUT_IMAGE)) {
        if ($imagesize = @getimagesizefromstring($image)) {
            if (isset($imagesize['mime'])) {
                switch ($imagesize['mime']) {
                    case 'image/jpg':
                    case 'image/jpeg':
                        header('content-type: image/jpeg');
                        echo $image;
                        exit;
                    break;
                    case 'image/png':
                        header('content-type: image/png');
                        echo $image;
                        exit;
                    break;
                    case 'image/gif':
                        header('content-type: image/gif');
                        echo $image;
                        exit;
                    break;
                }
            }
        }
    }
}

// No image
$noimage = sprintf('%s/public/image/noimage.png', PROJECT_DIR);

if (file_exists($noimage)) {
    header('Content-type: image/png');
    echo file_get_contents($noimage);
}
