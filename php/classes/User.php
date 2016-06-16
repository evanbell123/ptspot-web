<?php

include_once 'QueryBuilder.php';

class User {

    private $id;
    private $firstName;
    private $lastName;
    private $email;
    private $passwordHash;
    private $gender;
    private $birthDate;
    private $totalReviews;
    private $role;
    private $lastUpdated;
    private $created;
    private $activated;

    function __construct($info) {
        $this->id = $info['id'];
        $this->firstName = $info['firstName'];
        $this->lastName = $info['lastName'];
        $this->email = $info['email'];
        $this->passwordHash = $info['passwordHash'];
        $this->gender = $info['gender'];
        $this->birthDate = $info['birthDate'];
        $this->totalReviews = $info['totalReviews'];
        $this->role = $info['role'];
        $this->lastUpdated = $info['lastUpdated'];
        $this->created = $info['created'];
        $this->activated = $info['activated'];
    }
    
    public function getID() {
        return $this->id;
    }
    
    public function getPasswordHash() {
        return $this->passwordHash;
    }
    
    public function setPasswordHash($passwordHash) {
        $this->passwordHash = $passwordHash;
    }
    
    protected function save($pdo) {
        $queryBuilder = new QueryBuilder();
        $values = [
            "id",
            "firstName",
            "lastName",
            "email",
            "passwordHash",
            "gender",
            "birthDate",
            "totalReviews",
            "role",
            "lastUpdated",
            "created",
            "activated"
            ];

        $insertStatement = $queryBuilder->insert("pt_spot.user", $values);
        $preparedStatement = $pdo->prepare($insertStatement);

        $preparedStatement->bindValue(':id', $this->id, PDO::PARAM_INT);
        $preparedStatement->bindValue(':firstName', $this->firstName);
        $preparedStatement->bindValue(':lastName', $this->lastName);
        $preparedStatement->bindValue(':email', $this->email);
        $preparedStatement->bindValue(':passwordHash', $this->passwordHash);
        $preparedStatement->bindValue(':gender', $this->gender, PDO::PARAM_BOOL);
        $preparedStatement->bindValue(':birthDate', $this->birthDate);
        $preparedStatement->bindValue(':totalReviews', $this->totalReviews, PDO::PARAM_INT);
        $preparedStatement->bindValue(':role', $this->role, PDO::PARAM_INT);
        $preparedStatement->bindValue(':lastUpdated', $this->lastUpdated);
        $preparedStatement->bindValue(':created', $this->created);
        $preparedStatement->bindValue(':activated', $this->activated, PDO::PARAM_BOOL);
        $preparedStatement->execute();
    }
    
    function updatePasswordHash($pdo) {
        $queryBuilder = new QueryBuilder();
        $updateStatement = $queryBuilder->update('pt_spot.user', array('passwordHash' => ':passwordHash'), 'email = :email');
        $preparedStatement = $pdo->prepare($updateStatement);
        $preparedStatement->bindValue(':passwordHash', $this->passwordHash);
        $preparedStatement->bindValue(':email', $this->email);
        $preparedStatement->execute();
    }

    function toArray() {
        return array(
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'passwordHash' => $this->passwordHash,
            'gender' => $this->gender,
            'birthDate' => $this->birthDate,
            'totalReviews' => $this->totalReviews,
            'role' => $this->role,
            'lastUpdated' => $this->lastUpdated,
            'created' => $this->created,
            'activated' => $this->activated
        );
    }

}