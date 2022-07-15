<?php

class CurlChat extends Curl {

    public function getConversations($timeout) {

        $this->prepare('/ob/chatconversations', 'GET', $timeout);

        if ($response = $this->execute()) {

            $responses = [];

            foreach ($response as $conversation) {
                switch (false) {
                    case isset($conversation['peerId']):
                    case isset($conversation['lastMessage']):
                    case isset($conversation['outgoing']):
                    case isset($conversation['timestamp']):
                    case isset($conversation['unread']):
                    break;
                    default:
                        $responses[] = $conversation;
                }
            }

            return $responses;
        }

        return false;
    }

    public function getMessages($peerId, $timeout) {

        $this->prepare('/ob/chatmessages/' . $peerId, 'GET', $timeout);

        if ($response = $this->execute()) {

            $messages = [];

            foreach ($response as $message) {
                switch (false) {
                    case isset($message['messageId']):
                    case isset($message['peerId']):
                    case isset($message['subject']):
                    case isset($message['message']):
                    case isset($message['outgoing']):
                    case isset($message['read']):
                    case isset($message['timestamp']):
                    break;
                    default:
                        $messages[] = $message;
                }
            }

            return $messages;
        }

        return false;
    }

    public function sendMessage($peerId, $subject, $message, $timeout) {

        $this->prepare('/ob/chat', 'POST', $timeout, [
            'peerId'  => $peerId,
            'subject' => $subject,
            'message' => $message,
        ]);

        return $this->execute();
    }

    public function readMessages($peerId, $subject, $timeout) {

        $this->prepare('/ob/markchatasread/' . $peerId . '?subject=' . $subject, 'POST', $timeout);

        return $this->execute();
    }
}
