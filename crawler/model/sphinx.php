<?php

class ModelSphinx {

    private $_db;

    public function __construct($host, $port) {
        try {
            $this->_db = new PDO('mysql:host=' . $host . ';port=' . $port . ';charset=utf8', false, false, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
            $this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->_db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            trigger_error($e->getMessage());
        }
    }

    public function wordExists($index, $keyword, $id) {

        try {

            switch ($index) {
                case 'listing':
                    $index = 'listing';
                break;
                case 'profile':
                    $index = 'profile';
                    $keyword = '@!listings ' . $keyword; // Do not block profile by listings keyword
                break;
                default:
                    trigger_error(_('Index not found!'));
                    return false;
            }

            $query = $this->_db->prepare("SELECT `id` FROM `{$index}`
                                                      WHERE MATCH(:keyword)
                                                      AND `id` = :id
                                                      OPTION cutoff = 1");

            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':keyword', $keyword, PDO::PARAM_STR);

            $query->execute();

            return $query->rowCount() ? true : false;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return [];

        }
    }

    public function updateProfileRatingAverage($id, $value) {

        try {

            $query = $this->_db->prepare('UPDATE `profile` SET `ratingaverage` = ' . (float) $value . ' WHERE `id` = :id AND `ratingaverage` <> ' . (float) $value);


            $query->bindValue(':id', $id, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateProfileRatingCount($id, $value) {

        try {

            $query = $this->_db->prepare('UPDATE `profile` SET `ratingcount` = :value WHERE `id` = :id AND `ratingcount` <> :value');


            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateProfileNsfw($id, $value) {

        try {

            $query = $this->_db->prepare('UPDATE `profile` SET `nsfw` = :value WHERE `id` = :id AND `nsfw` <> :value');


            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateProfileModerator($id, $value) {

        try {

            $query = $this->_db->prepare('UPDATE `profile` SET `moderator` = :value WHERE `id` = :id AND `moderator` <> :value');


            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateProfileIndexed($id, $value) {

        try {

            $query = $this->_db->prepare('UPDATE `profile` SET `indexed` = :value WHERE `id` = :id AND `indexed` <> :value');


            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateProfileUpdated($id, $value) {

        try {

            $query = $this->_db->prepare('UPDATE `profile` SET `updated` = :value WHERE `id` = :id AND `updated` <> :value');


            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateProfileOnline($id, $value) {

        try {
            $query = $this->_db->prepare('UPDATE `profile` SET `online` = :value WHERE `id` = :id AND `online` <> :value');

            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateProfileTor($id, $value) {

        try {
            $query = $this->_db->prepare('UPDATE `profile` SET `tor` = :value WHERE `id` = :id AND `tor` <> :value');

            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateProfilePs($id, $value) {

        try {
            $query = $this->_db->prepare('UPDATE `profile` SET `ps` = :value WHERE `id` = :id AND `ps` <> :value');

            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_STR);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateProfileLf($id, $value) {

        try {

            $query = $this->_db->prepare('UPDATE `profile` SET `lf` = :value WHERE `id` = :id AND `lf` <> :value');


            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_STR);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateListingPrice($id, $value) {

        try {

            $query = $this->_db->prepare('UPDATE `listing` SET `price` = ' . (float) $value . ' WHERE `id` = :id AND `price` <> ' . (float) $value);


            $query->bindValue(':id', $id, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateListingPriceBtc($id, $value) {

        try {

            $query = $this->_db->prepare('UPDATE `listing` SET `pricebtc` = ' . (float) $value . ' WHERE `id` = :id AND `pricebtc` <> ' . (float) $value);


            $query->bindValue(':id', $id, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateListingPriceModifier($id, $value) {

        try {

            $query = $this->_db->prepare('UPDATE `listing` SET `pricemodifier` = ' . (float) $value . ' WHERE `id` = :id AND `pricemodifier` <> ' . (float) $value);


            $query->bindValue(':id', $id, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateListingCode($id, $value) {

        try {

            $query = $this->_db->prepare('UPDATE `listing` SET `code` = :value WHERE `id` = :id AND `code` <> :value');


            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_STR);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateListingCondition($id, $value) {

        try {

            $query = $this->_db->prepare('UPDATE `listing` SET `lc` = :value WHERE `id` = :id AND `lc` <> :value');


            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_STR);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateListingContractType($id, $value) {

        try {

            $query = $this->_db->prepare('UPDATE `listing` SET `lt` = :value WHERE `id` = :id AND `lt` <> :value');


            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_STR);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateListingNsfw($id, $value) {

        try {

            $query = $this->_db->prepare('UPDATE `listing` SET `nsfw` = :value WHERE `id` = :id AND `nsfw` <> :value');


            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateListingModerators($id, $value) {

        try {

            $query = $this->_db->prepare('UPDATE `listing` SET `moderators` = :value WHERE `id` = :id AND `moderators` <> :value');

            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateListingIndexed($id, $value) {

        try {

            $query = $this->_db->prepare('UPDATE `listing` SET `indexed` = :value WHERE `id` = :id AND `indexed` <> :value');

            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateListingUpdatedIpfs($id, $value) {

        try {

            $query = $this->_db->prepare('UPDATE `listing` SET `updatedipfs` = :value WHERE `id` = :id AND `updatedipfs` <> :value');

            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateListingUpdatedIpns($id, $value) {

        try {

            $query = $this->_db->prepare('UPDATE `listing` SET `updatedipns` = :value WHERE `id` = :id AND `updatedipns` <> :value');

            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateListingOnline($id, $value) {

        try {
            $query = $this->_db->prepare('UPDATE `listing` SET `online` = :value WHERE `id` = :id AND `online` <> :value');

            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateListingTor($id, $value) {

        try {
            $query = $this->_db->prepare('UPDATE `listing` SET `tor` = :value WHERE `id` = :id AND `tor` <> :value');

            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }

    public function updateListingRemoved($id, $value) {

        try {
            $query = $this->_db->prepare('UPDATE `listing` SET `removed` = :value WHERE `id` = :id AND `removed` <> :value');

            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':value', $value, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;

        }
    }
}
