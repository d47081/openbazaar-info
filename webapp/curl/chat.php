<?php

class CurlChat extends Curl {

    public function sendMessage($peerId, $subject, $message, $timeout) {

        $this->prepare('/ob/chat', 'POST', $timeout, [
            'peerId'  => $peerId,
            'subject' => $subject,
            'message' => $message,
        ]);

        return $this->execute();
    }
}
