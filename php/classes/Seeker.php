<?php

include_once 'User.php';
include_once 'QueryBuilder.php';


class Seeker extends User {
    //put your code here
    
    function __construct($userArray) {
        parent::__construct($userArray);
    }

    public function save($pdo) {
        $queryBuilder = new QueryBuilder();
        
        $fields = ["id"];
        
        $pdo->beginTransaction();
        
        parent::save($pdo);
        $insertStatement = $queryBuilder->insert("pt_spot.seeker", $fields);
        $preparedStatement = $pdo->prepare($insertStatement);
        $preparedStatement->bindValue(':id', $pdo->lastInsertId(), PDO::PARAM_INT);
        $preparedStatement->execute();
        
        $pdo->commit();
    }

}
