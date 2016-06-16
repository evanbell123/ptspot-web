<?php

class CredentialsValidationService {
    
    private $minPasswordLength = 8;
    
    public function isValidLoginCredentials($email, $password) {
        
        if (!$this->isValidEmail($email) || !$this->isValidPasswordLength($password)) {
            return false;
        } else {
            return true;
        }
    }

    public function isValidRegisterCredentials($firstName, $lastName, $email, $password, $confirmPassword, $birthDate, $gender, $role) {
        
        if (!$firstName || !$lastName ||
                !$this->isValidEmail($email) ||
                !$this->isValidPasswordLength($password) ||
                !$this->isMatchingPasswords($password, $confirmPassword) ||
                !$this->isValidDate($birthDate) ||
                !$this->isValidBool($gender) ||
                !$this->isValidBool($role)) {
            return false;
        } else {
            return true;
        }
        
    }
    
    private function isValidEmail($email) {
        $is_email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if (!$is_email) {
            return false;
        } else {
            return true;
        }
    }
    
    private function isValidPasswordLength($password) {
        if (mb_strlen($password) < $this->minPasswordLength) {
            return false;
        } else {
            return true;
        }
    }
    
    private function isMatchingPasswords($password, $confirmPassword) {
        if ($password !== $confirmPassword) {
            return false;
        } else {
            return true;
        }
    }
    
    private function isValidDate($date) {
        $dateArray = explode('/', $date);
        if (count($dateArray) !== 3 || !checkdate($dateArray[0], $dateArray[1], $dateArray[2])) {
            return false;
        } else {
            return true;
        }
    }
    
    private function isValidBool($var) {
        if (!($var == 1 || $var == 0)) {
            return false;
        } else {
            return true;
        }
    }
    
    function __destruct() {
        $this->minPasswordLength = null;
    }

}
