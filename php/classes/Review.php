<?php

class Review {

    private $id;
    private $trainerID;
    private $seekerID;
    private $clarity;
    private $effectiveness;
    private $motivation;
    private $intensity;
    private $rating;
    private $comment;
    private $recommend;
    private $continuing;
    private $date;

    function __construct($trainerID, $seekerID, $clarity, $effectiveness, $motivation, $intensity, $comment, $recommend, $continuing) {
        $this->id = null;
        $this->trainerID = $trainerID;
        $this->seekerID = $seekerID;
        $this->clarity = $clarity;
        $this->effectiveness = $effectiveness;
        $this->motivation = $motivation;
        $this->intensity = $intensity;
        $this->rating = ($clarity + $effectiveness + $motivation) / 3;
        $this->comment = $comment;
        $this->recommend = $recommend;
        $this->continuing = $continuing;
        $this->date = date('Y-m-d H:i:s', time());
    }
    
    public function getRating() {
        return $this->rating;
    }
    
    public function save($pdo) {
        $queryBuilder = new QueryBuilder();
        $values = [
            "id",
            "trainerID",
            "seekerID",
            "clarity",
            "effectiveness",
            "motivation",
            "intensity",
            "rating",
            "comment",
            "recommend",
            "continuing",
            "date"
            ];

        $insertStatement = $queryBuilder->insert("pt_spot.review", $values);
        $preparedStatement = $pdo->prepare($insertStatement);

        $preparedStatement->bindValue(':id', $this->id, PDO::PARAM_INT);
        $preparedStatement->bindValue(':trainerID', $this->trainerID, PDO::PARAM_INT);
        $preparedStatement->bindValue(':seekerID', $this->seekerID, PDO::PARAM_INT);
        $preparedStatement->bindValue(':clarity', $this->clarity);
        $preparedStatement->bindValue(':effectiveness', $this->effectiveness);
        $preparedStatement->bindValue(':motivation', $this->motivation, PDO::PARAM_BOOL);
        $preparedStatement->bindValue(':intensity', $this->intensity);
        $preparedStatement->bindValue(':rating', $this->rating);
        $preparedStatement->bindValue(':comment', $this->comment);
        $preparedStatement->bindValue(':recommend', $this->recommend, PDO::PARAM_BOOL);
        $preparedStatement->bindValue(':continuing', $this->continuing, PDO::PARAM_BOOL);
        $preparedStatement->bindValue(':date', $this->date);
        $preparedStatement->execute();
    }

}
