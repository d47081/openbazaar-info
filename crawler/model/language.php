<?php

class ModelLanguage extends Model {

    public function languageExists($code) {

        try {

            $query = $this->db->prepare('SELECT `languageId` FROM  `language` WHERE `code` = ? LIMIT 1');

            $query->execute([$code]);

            return $query->rowCount() ? $query->fetch()['languageId'] : false;

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addLanguage($code) {

        try {

            $query = $this->db->prepare('INSERT INTO `language` SET `code` = ?');

            $query->execute([$code]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }
}
