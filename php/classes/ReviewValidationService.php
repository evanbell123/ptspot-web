<?php

class ReviewValidationService {

    private $minRating = 1;
    private $maxTrainerRating = 5;
    private $minCharacters = 0;
    private $maxCharacters = 500;

    public function isValidReview($trainerID, $seekerID, $clarity, $effectiveness, $motivation, $intensity, $comment, $recommend, $continuing) {
        
        if (!$this->isValidRating($clarity) ||
                !$this->isValidRating($effectiveness) ||
                !$this->isValidRating($motivation) ||
                !$this->isValidRating($intensity) ||
                !$this->isValidComment($comment) ||
                !$this->isValidID($trainerID) ||
                !$this->isValidID($seekerID) ||
                !$this->isValidBool($recommend) ||
                !$this->isValidBool($continuing) ||
                $this->isReviewingSelf($trainerID, $seekerID)) {
            return false;
        } else {
            return true;
        }
    }

    private function isValidRating($rating) {
        return filter_var($rating, FILTER_VALIDATE_INT, array("options" => array("min_range" => $this->minRating, "max_range" => $this->maxTrainerRating)));
    }
    
    private function isValidBool($var) {
        if (!($var == 1 || $var == 0)) {
            return false;
        } else {
            return true;
        }
    }
    
    private function isValidComment($comment) {
        $commentLength = strlen($comment);
        if ($commentLength < $this->minCharacters || $commentLength > $this->maxCharacters) {
            return false;
        } else {
            return true;
        }
    }

    private function isValidID($id) {
        return filter_var($id, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)));
    }

    private function isReviewingSelf($trainerID, $seekerID) {
        if ($trainerID != $seekerID) {
            return false;
        } else {
            return true;
        }
    }

}
