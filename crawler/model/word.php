<?php

class ModelWord extends Model {

    public function getNsfwFords($value) {

        try {

            $query = $this->db->prepare('SELECT * FROM  `word` WHERE `nsfw` = :nsfw');

            $query->bindValue(':nsfw', $value, PDO::PARAM_STR);
            $query->execute();

            return $query->rowCount() ? $query->fetchAll() : [];

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }
}
