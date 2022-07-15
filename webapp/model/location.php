<?php

class ModelLocation extends Model {

    public function getCountries($display = 1) {

        try {

            $query = $this->db->prepare('SELECT * FROM `country` WHERE `display` = :display ORDER BY `name` ASC');

            $query->bindValue(':display', $display, PDO::PARAM_STR);
            $query->execute();

            return $query->rowCount() ? $query->fetchAll() : [];

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getCountry($countryId) {

        try {

            $query = $this->db->prepare('SELECT * FROM `country` WHERE `countryId` = ? LIMIT 1');

            $query->execute([$countryId]);

            return $query->rowCount() ? $query->fetch() : false;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getLocationTotals() {

        try {

          $query = $this->db->prepare('SELECT COUNT(DISTINCT `p`.`profileId`) AS `profiles`,
                                              COUNT(DISTINCT `l`.`listingId`) AS `listings`,
                                                                                    `c`.`codeIso2`,
                                                                                    `c`.`name` FROM `profile` AS `p`
                                                                                               JOIN `country` AS `c` ON (`c`.`countryId` = `p`.`countryId`)
                                                                                               LEFT JOIN `listing` AS `l` ON (`l`.`profileId` = `p`.`profileId`)

                                                                                               GROUP BY `c`.`countryId`');

            $query->execute();

            return $query->rowCount() ? $query->fetchAll() : [];

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }
}
