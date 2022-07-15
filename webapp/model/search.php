<?php

class ModelSearch extends Model {

    public function addSearch($value, $request, $type, $total, $ipId, $time) {

        try {

            $query = $this->db->prepare('INSERT INTO `search` SET `query`   = :query,
                                                                  `request` = :request,
                                                                  `type`    = :type,
                                                                  `total`   = :total,
                                                                  `ipId`    = :ipId,
                                                                  `time`    = :time');

            $query->bindValue(':time', $time, PDO::PARAM_INT);
            $query->bindValue(':total', $total, PDO::PARAM_INT);
            $query->bindValue(':ipId', $ipId, PDO::PARAM_INT);
            $query->bindValue(':type', $type, PDO::PARAM_STR);
            $query->bindValue(':query', $value, PDO::PARAM_STR);
            $query->bindValue(':request', $request, PDO::PARAM_STR);

            $query->execute();

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addSearchResult($searchId, $page, $id) {

        try {

            $query = $this->db->prepare('INSERT INTO `searchResult` SET `searchId` = ?, `page` = ?, `id` = ?');
            $query->execute([$searchId, $page, $id]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /*
    public function getFound($id, $type, $distinct = false) {

      try {

          $query = $this->db->prepare('SELECT COUNT(' . ($distinct ? ' DISTINCT ' : false) . '`ipId`) AS `total` FROM `searchResult` AS `sr`
                                                                                                                 JOIN `search` AS `s` ON (`s`.`searchId` = `sr`.`searchId`)
                                                                                                                WHERE `id` = ? AND `type` = ?');

          $query->execute([$id, $type]);

          return $query->rowCount() ? $query->fetch()['total'] : 0;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }
    */
}
