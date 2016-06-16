<?php

include_once 'User.php';
include_once 'QueryBuilder.php';

class Trainer extends User {

    private $PTScore;
    private $rating;
    private $clarity;
    private $effectiveness;
    private $motivation;
    private $intensity;
    private $onlineCoaching;

    function __construct($userArray, $trainerArray) {
        parent::__construct($userArray);
        $this->PTScore = $trainerArray['PTScore'];
        $this->rating = $trainerArray['rating'];
        $this->clarity = $trainerArray['clarity'];
        $this->effectiveness = $trainerArray['effectiveness'];
        $this->motivation = $trainerArray['motivation'];
        $this->intensity = $trainerArray['intensity'];
        $this->onlineCoaching = $trainerArray['onlineCoaching'];
    }

    public function save($pdo) {
        $queryBuilder = new QueryBuilder();
        $fields = [
            "id",
            "PTScore",
            "rating",
            "clarity",
            "effectiveness",
            "motivation",
            "intensity",
            "onlineCoaching",
            ];
        
        $pdo->beginTransaction();
        
        parent::save($pdo);
        $insertStatement = $queryBuilder->insert("pt_spot.trainer", $fields);
        $preparedStatement = $pdo->prepare($insertStatement);
        $preparedStatement->bindValue(':id', $pdo->lastInsertId(), PDO::PARAM_INT);
        $preparedStatement->bindValue(':PTScore', $this->PTScore);
        $preparedStatement->bindValue(':rating', $this->rating);
        $preparedStatement->bindValue(':clarity', $this->clarity, PDO::PARAM_INT);
        $preparedStatement->bindValue(':effectiveness', $this->effectiveness, PDO::PARAM_INT);
        $preparedStatement->bindValue(':motivation', $this->motivation, PDO::PARAM_INT);
        $preparedStatement->bindValue(':intensity', $this->intensity, PDO::PARAM_INT);
        $preparedStatement->bindValue(':onlineCoaching', $this->onlineCoaching, PDO::PARAM_BOOL);
        $preparedStatement->execute();
        
        $pdo->commit();
    }

}
