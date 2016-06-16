<?php

include_once 'review.php';

class DatabaseService {
    private $pdo;
    private $queryBuilder;

    function __construct($dbSettings) {
        $this->queryBuilder = new QueryBuilder();
        try {
            $this->pdo = new PDO(sprintf('mysql:host=%s;dbname=%s;port=%s;charset=%s', $dbSettings['servername'], $dbSettings['databasename'], $dbSettings['dbport'], $dbSettings['charset']), $dbSettings['username'], $dbSettings['password'], array(
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => true,
                PDO::ERRMODE_EXCEPTION => true
            ));
        } catch (PDOException $e) {
            echo "Database connection failed\n";
            echo $e->getMessage();
            exit;
        }
    }
    
    public function getAllTrainers() {

        $values = "user.id, user.firstName, user.lastName, user.totalReviews, trainer.PTScore, trainer.rating, trainer.clarity, trainer.effectiveness, trainer.motivation, trainer.intensity";
        $innerJoin = $this->queryBuilder->innerJoinOrderBy("trainer", "user", $values, "user.id = trainer.id", "PTScore");
        //echo $innerJoin;
        $preparedStatement = $this->pdo->prepare($innerJoin);
        $preparedStatement->execute();
        return $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAvatarByID($id) {
        $selectStatement = $this->queryBuilder->selectFieldsWhere("pt_spot.avatar", "photo", "id = :id");
        $preparedStatement = $this->pdo->prepare($selectStatement);
        $preparedStatement->bindValue(':id', $id);
        $preparedStatement->execute();
        $preparedStatement->bindColumn(1, $avatar, PDO::PARAM_LOB);
        $preparedStatement->fetch(PDO::FETCH_BOUND);
        
        return array("avatar" => $avatar);
    }
    
    
    function __destruct() {
        $this->pdo = null;
        $this->queryBuilder = null;
    }
}
