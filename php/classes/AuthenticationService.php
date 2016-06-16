<?php

//namespace OAuth2\Storage;

include_once 'Trainer.php';
include_once 'Seeker.php';
include_once 'User.php';
include_once 'Review.php';
include_once 'CredentialsValidationService.php';
include_once 'ReviewValidationService.php';
include_once 'QueryBuilder.php';
include_once 'AccountInterface.php';

class AuthenticationService implements OAuth2\Storage\UserCredentialsInterface, OAuth2\Storage\ClientCredentialsInterface, OAuth2\Storage\RefreshTokenInterface, OAuth2\Storage\AccessTokenInterface {

    private $pdo;
    private $queryBuilder;

    function __construct($dbSettings) {
        $this->currentHashAlgorithm = PASSWORD_DEFAULT;
        $this->currentHashOptions = array('cost' => 15);
        //$this->validationService = new CredentialsValidationService();
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

    public function register($firstName, $lastName, $email, $password, $confirmPassword, $birthDate, $gender, $role) {
        $filteredFirst = filter_var($firstName, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_ENCODE_HIGH);
        $filteredLast = filter_var($lastName, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_ENCODE_HIGH);
        $filteredEmail = filter_var($email, FILTER_SANITIZE_EMAIL);

        $credentialsValidationService = new CredentialsValidationService();

        $isValidCredentials = $credentialsValidationService->isValidRegisterCredentials($filteredFirst, $filteredLast, $filteredEmail, $password, $confirmPassword, $birthDate, $gender, $role);
        if (!$isValidCredentials) {
            return array('success' => false, 'message' => 'Invalid Credentials');
        }

        $user = $this->fetchAbyB("user", "email", $filteredEmail, "id");
        if ($user) {
            return array('success' => false, 'message' => 'Invalid Credentials');
        }

        $passwordHash = password_hash($password, $this->currentHashAlgorithm, $this->currentHashOptions);
        if (!$passwordHash) {
            return array('success' => false, 'message' => 'Password Hash Failed');
        }

        //Convert birth date to mysql format
        $currentTimeStamp = date('Y-m-d H:i:s', time());
        $bday = new DateTime($birthDate);

        $userArray = array(
            'id' => null,
            'firstName' => $filteredFirst,
            'lastName' => $filteredLast,
            'email' => $filteredEmail,
            'passwordHash' => $passwordHash,
            'gender' => $gender,
            'birthDate' => $bday->format('Y-m-d'),
            'totalReviews' => 0,
            'avatar' => null,
            'mime' => null,
            'role' => intval($role),
            'lastUpdated' => $currentTimeStamp,
            'created' => $currentTimeStamp,
            'activated' => 1
        );

        if ($userArray['role'] == 1) {
            $trainerArray = array(
                "PTScore" => 0.0,
                "rating" => 0.0,
                "clarity" => 0.0,
                "effectiveness" => 0.0,
                "motivation" => 0.0,
                "intensity" => 0.0,
                "onlineCoaching" => 0
            );
            $trainer = new Trainer($userArray, $trainerArray);
            $trainer->save($this->pdo);
        } else {
            $seeker = new Seeker($userArray);
            $seeker->save($this->pdo);
        }
        return array('success' => true, 'message' => 'Welcome to The PT Spot!');
    }

    public function leaveReview($trainerID, $seekerID, $clarity, $effectiveness, $motivation, $intensity, $comment, $recommend, $continuing) {

        $filteredTrainerID = filter_var($trainerID, FILTER_SANITIZE_NUMBER_INT);
        $filteredSeekerID = filter_var($seekerID, FILTER_SANITIZE_NUMBER_INT);
        $filteredClarity = filter_var($clarity, FILTER_SANITIZE_NUMBER_INT);
        $filteredEffectiveness = filter_var($effectiveness, FILTER_SANITIZE_NUMBER_INT);
        $filteredMotivation = filter_var($motivation, FILTER_SANITIZE_NUMBER_INT);
        $filteredIntensity = filter_var($intensity, FILTER_SANITIZE_NUMBER_INT);
        $filteredComment = filter_var($comment, FILTER_SANITIZE_STRING);
        $filteredRecommend = filter_var($recommend, FILTER_SANITIZE_NUMBER_INT);
        $filteredContinuing = filter_var($continuing, FILTER_SANITIZE_NUMBER_INT);


        $reviewValidationService = new ReviewValidationService();

        $isVaildReview = $reviewValidationService->isValidReview($filteredTrainerID, $filteredSeekerID, $filteredClarity, $filteredEffectiveness, $filteredMotivation, $filteredIntensity, $filteredComment, $filteredRecommend, $filteredContinuing);

        if (!$isVaildReview) {
            return array('success' => false, 'message' => 'Invalid Review');
        }

        if ($this->fetchReview($trainerID, $seekerID, "id")) {
            return array('success' => false, 'message' => 'You already reviewed this trainer.');
        }

        $review = new Review($trainerID, $seekerID, $clarity, $effectiveness, $motivation, $intensity, $comment, $recommend, $continuing);

        $this->pdo->beginTransaction();

        $review->save($this->pdo);
        $trainer = $this->fetchTrainerByID($trainerID);

        $currentTotalReviews = $this->fetchTotalReviewsByID($trainerID)["totalReviews"];

        $averageClarity = $this->updateAverageRating($trainer["clarity"], $currentTotalReviews, $clarity);
        $averageEffectiveness = $this->updateAverageRating($trainer["effectiveness"], $currentTotalReviews, $effectiveness);
        $averageMotivation = $this->updateAverageRating($trainer["motivation"], $currentTotalReviews, $motivation);
        $averageIntensity = $this->updateAverageRating($trainer["intensity"], $currentTotalReviews, $intensity);
        $averageRating = $this->updateAverageRating($trainer["rating"], $currentTotalReviews, $review->getRating());

        $updatedPTScore = $averageRating * ($currentTotalReviews + 1);

        $updatedTrainerValues = array(
            'PTScore' => $updatedPTScore,
            'rating' => $averageRating,
            'clarity' => $averageClarity,
            'effectiveness' => $averageEffectiveness,
            'motivation' => $averageMotivation,
            'intensity' => $averageIntensity,
        );

        $this->updateTrainerByID($filteredTrainerID, $updatedTrainerValues);

        $this->updateTotalReviewsByID($filteredTrainerID);
        $this->updateTotalReviewsByID($filteredSeekerID);

        $this->pdo->commit();

        return array('success' => true, 'message' => 'Thanks for reviewing!');
    }

    private function updateTotalReviewsByID($id) {
        $preparedStatement = $this->pdo->prepare("UPDATE `pt_spot`.`user` SET totalReviews = totalReviews + 1 WHERE id = :id");
        $preparedStatement->bindValue(':id', $id);
        $preparedStatement->execute();
    }

    private function updateTrainerByID($id, $fields) {
        $updateStatement = $this->queryBuilder->update("pt_spot.trainer", array_keys($fields), "id = :id");
        $preparedStatement = $this->pdo->prepare($updateStatement);
        $preparedStatement->bindValue(':id', $id);
        $preparedStatement->bindValue(':PTScore', $fields['PTScore']);
        $preparedStatement->bindValue(':rating', $fields['rating']);
        $preparedStatement->bindValue(':clarity', $fields['clarity']);
        $preparedStatement->bindValue(':effectiveness', $fields['effectiveness']);
        $preparedStatement->bindValue(':motivation', $fields['motivation']);
        $preparedStatement->bindValue(':intensity', $fields['intensity']);
        $preparedStatement->execute();
    }

    public function fetchReview($trainerID, $seekerID, $fields) {
        $select_statement = $this->queryBuilder->selectFieldsWhere("pt_spot.review", $fields, "trainerID = :trainerID AND seekerID = :seekerID");
        $preparedStatement = $this->pdo->prepare($select_statement);
        $preparedStatement->bindValue(':trainerID', $trainerID);
        $preparedStatement->bindValue(':seekerID', $seekerID);
        $preparedStatement->execute();
        return $preparedStatement->fetch(PDO::FETCH_ASSOC);
    }

    private function fetchTotalReviewsByID($id) {
        $select_statement = $this->queryBuilder->selectFieldsWhere("pt_spot.user", "totalReviews", "id = :id");
        $preparedStatement = $this->pdo->prepare($select_statement);
        $preparedStatement->bindValue(':id', $id);
        $preparedStatement->execute();
        return $preparedStatement->fetch(PDO::FETCH_ASSOC);
    }

    private function updateAverageRating($currentAverage, $currentTotal, $newRating) {
        return (($currentAverage * $currentTotal) + $newRating) / ($currentTotal + 1);
    }

    public function updateUserByID($id, $fields) {

        $filteredID = filter_var($id, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)));

        $nameFilter = array(
            "filter" => FILTER_SANITIZE_STRING,
            "flags" => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_ENCODE_HIGH
        );

        $filters = array(
            "email" => FILTER_SANITIZE_EMAIL,
            "firstName" => $nameFilter,
            "lastName" => $nameFilter
        );

        //filter array and get rid of null values
        $filteredFields = array_filter(filter_var_array($fields, $filters));

        $updateStatement = $this->queryBuilder->update("pt_spot.user", array_keys($filteredFields), "id = :id");
        $preparedStatement = $this->pdo->prepare($updateStatement);
        $preparedStatement->bindValue(':id', $filteredID);

        foreach ($filteredFields as $key => $value) {
            $preparedStatement->bindValue(':' . $key, $value);
        }

        return $preparedStatement->execute();
    }

    public function updateAvatarByID($id, $filePath) {
        $filteredID = filter_var($id, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)));

        $blob = fopen($filePath, 'rb');

        $avatarExists = $this->fetchAbyB("avatar", "id", $id, "id");

        if (!$avatarExists) {
            $insertFields = array("id" => "id", "photo" => "photo");
            $query = $this->queryBuilder->insert("pt_spot.avatar", $insertFields);
        } else {
            $updateFields = array("photo");
            $query = $this->queryBuilder->update("pt_spot.avatar", $updateFields, "id = :id");
        }
        $preparedStatement = $this->pdo->prepare($query);
        $preparedStatement->bindValue(':id', $filteredID);
        $preparedStatement->bindValue(':photo', $blob, PDO::PARAM_LOB);

        return $preparedStatement->execute();
    }

    public function updateEmailByID($id, $newEmail) {

        $isValidEmail = filter_var($newEmail, FILTER_VALIDATE_EMAIL);

        if (!$isValidEmail) {
            return array('success' => false, 'message' => 'Invalid Email');
        }

        $user = $this->fetchAbyB("user", "email", $newEmail);
        if ($user) {
            return array('success' => false, 'message' => 'Invalid Email');
        }

        if (!$this->updateUserByID($id, array("email" => $newEmail))) {
            return array('success' => false, 'message' => 'Update Failed');
        } else {
            return array('success' => true);
        }
    }

    public function updateWebsiteLinkByID($id, $siteURL) {

        $filteredID = filter_var($id, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)));

        $file = $siteURL;
        $file_headers = @get_headers($file);
        if ($file_headers[0] == 'HTTP/1.1 404 Not Found') {
            return array('success' => false, 'message' => "This website doesn't exist");
        }

        $trainerHasWebsite = $this->fetchAbyB("website", "id", $id, "id");

        if (!$trainerHasWebsite) {
            $insertFields = array("id" => "id", "link" => "link");
            $query = $this->queryBuilder->insert("pt_spot.website", $insertFields);
        } else {
            $updateFields = array("link");
            $query = $this->queryBuilder->update("pt_spot.website", $updateFields, "id = :id");
        }
        $preparedStatement = $this->pdo->prepare($query);
        $preparedStatement->bindValue(':id', $filteredID);
        $preparedStatement->bindValue(':link', $siteURL);

        return $preparedStatement->execute();
    }

    public function getTrainerByID($id, $fields) {
        $values = implode(", ", $fields);
        $innerJoin = $this->queryBuilder->innerJoinWhere("trainer", "user", $values, "user.id = trainer.id", "user.id = :id");
        $preparedStatement = $this->pdo->prepare($innerJoin);
        $preparedStatement->bindValue(':id', $id);
        $preparedStatement->execute();
        return $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTrainerReviewsByID($id) {

        $values = "review.id, review.clarity, review.effectiveness, review.motivation, review.intensity, review.rating, review.comment, review.recommend, review.date";
        $innerJoin = $this->queryBuilder->innerJoinWhereOrderBy("trainer", "review", $values, "trainer.id = review.trainerID", "trainer.id = " . $id, "review.rating");
        $preparedStatement = $this->pdo->prepare($innerJoin);
        $preparedStatement->execute();
        return $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTrainerContactInfoByID($id) {
        return $this->fetchAbyB("website", "id", $id, "link");
    }

    public function checkUserCredentials($email, $password) {
        $filteredEmail = filter_var($email, FILTER_SANITIZE_EMAIL);

        $credentialsValidationService = new CredentialsValidationService();
        $isValidCredentials = $credentialsValidationService->isValidLoginCredentials($filteredEmail, $password);

        if (!$isValidCredentials) {
            return false;
        }

        $userArray = $this->fetchAbyB("user", "email", $filteredEmail);

        if (empty($userArray)) {
            return false;
        }

        $user = new User($userArray);

        // check if encrypted passwords match
        if (password_verify($password, $user->getPasswordHash()) === false) {
            return false;
        }

        // rehash password if necessary
        $passwordNeedsRehash = password_needs_rehash($user->getPasswordHash(), $this->currentHashAlgorithm, $this->currentHashOptions);

        if ($passwordNeedsRehash === true) {
            $user->setPasswordHash(password_hash($password, $this->currentHashAlgorithm, $this->currentHashOptions));
            $user->updatePasswordHash($this->pdo);
        }

        return true;
    }

    /*
      private function fetchUserByID($id, $fields = "*") {
      $select_statement = $this->queryBuilder->selectFieldsWhere("pt_spot.user", $fields, "id = :id");
      $preparedStatement = $this->pdo->prepare($select_statement);
      $preparedStatement->bindValue(':id', $id);
      $preparedStatement->execute();
      return $preparedStatement->fetch(PDO::FETCH_ASSOC);
      }
     * 
     */

    private function fetchAbyB($a, $b, $value, $fields = "*") {
        $select_statement = $this->queryBuilder->selectFieldsWhere("pt_spot.$a", $fields, "$b = :$b");
        $preparedStatement = $this->pdo->prepare($select_statement);
        $preparedStatement->bindValue(":$b", $value);
        $preparedStatement->execute();
        return $preparedStatement->fetch(PDO::FETCH_ASSOC);
    }

    private function fetchTrainerByID($id) {
        $select_statement = $this->queryBuilder->selectWhere("pt_spot.trainer", "id = :id");
        $preparedStatement = $this->pdo->prepare($select_statement);
        $preparedStatement->bindValue(':id', $id);
        $preparedStatement->execute();
        return $preparedStatement->fetch(PDO::FETCH_ASSOC);
    }

    private function fetchOAuthClient($client_id, $fields = "*") {
        $select_statement = $this->queryBuilder->selectFieldsWhere("pt_spot.oauth_client", $fields, "client_id = :client_id");
        $preparedStatement = $this->pdo->prepare($select_statement);
        $preparedStatement->bindValue(':client_id', $client_id);
        $preparedStatement->execute();
        return $preparedStatement->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserDetails($email) {
        $result = $this->fetchAbyB("user", "email", $email, "id, role");

        if (!$result) {
            return false;
        }

        return array(
            "user_id" => $result["id"], // REQUIRED user_id to be stored with the authorization code or access token
            "scope" => $result["role"]       // OPTIONAL space-separated list of restricted scopes
        );
    }

    public function getAccessToken($access_token) {
        $select_statement = $this->queryBuilder->selectWhere("pt_spot.oauth_access_token", "access_token = :access_token");
        $preparedStatement = $this->pdo->prepare($select_statement);
        $preparedStatement->bindValue(':access_token', $access_token);
        $preparedStatement->execute();
        $token = $preparedStatement->fetch(PDO::FETCH_ASSOC);

        if ($token) {
            // convert date string back to timestamp
            $token['expires'] = strtotime($token['expires']);
        }
        return $token;
    }

    public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = null) {
        $fields = [
            "access_token",
            "client_id",
            "user_id",
            "expires",
            "scope"
        ];

        //check if access token exists
        if ($this->getAccessToken($oauth_token)) {
            $this->unsetAccessToken($oauth_token);
        }

        $insertStatement = $this->queryBuilder->insert("pt_spot.oauth_access_token", $fields);
        $preparedStatement = $this->pdo->prepare($insertStatement);

        $preparedStatement->bindValue(':access_token', $oauth_token);
        $preparedStatement->bindValue(':client_id', $client_id);
        $preparedStatement->bindValue(':user_id', $user_id);
        $preparedStatement->bindValue(':expires', date('Y-m-d H:i:s', $expires));
        $preparedStatement->bindValue(':scope', $scope);

        return $preparedStatement->execute();
    }

    public function unsetAccessToken($oauth_token) {
        $deleteStatement = $this->queryBuilder->deleteWhere("pt_spot.oauth_access_token", "access_token = :access_token");
        $preparedStatement = $this->pdo->prepare($deleteStatement);
        $preparedStatement->bindValue(':access_token', $oauth_token);
        return $preparedStatement->execute();
    }

    public function checkClientCredentials($client_id, $client_secret = null) {
        $result = $this->fetchOAuthClient($client_id, "client_secret");
        return $result && $result['client_secret'] == $client_secret;
    }

    public function isPublicClient($client_id) {
        $result = $this->fetchOAuthClient($client_id);
        if (!$result) {
            return false;
        }

        return empty($result['client_secret']);
    }

    public function checkRestrictedGrantType($client_id, $grant_types) {
        $result = $this->fetchOAuthClient($client_id, "grant_types");
        if (isset($result['grant_types'])) {
            $grant_types = explode(' ', $result['grant_types']);

            return in_array($grant_types, (array) $grant_types);
        }
        return true;
    }

    public function getClientDetails($client_id) {
        return $this->fetchOAuthClient($client_id);
    }

    public function getClientScope($client_id) {
        return $this->fetchOAuthClient($client_id, "scope");
    }

    public function getRefreshToken($refresh_token) {
        $select_statement = $this->queryBuilder->selectWhere("pt_spot.oauth_refresh_token", "refresh_token = :refresh_token");
        $preparedStatement = $this->pdo->prepare($select_statement);
        $preparedStatement->bindValue(':refresh_token', $refresh_token);
        $preparedStatement->execute();

        if ($token = $preparedStatement->fetch(PDO::FETCH_ASSOC)) {
            // convert expires to epoch time
            $token['expires'] = strtotime($token['expires']);
        }

        return $token;
    }

    public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = null) {
        $values = [
            "refresh_token",
            "client_id",
            "user_id",
            "expires",
            "scope"
        ];

        $insertStatement = $this->queryBuilder->insert("pt_spot.oauth_refresh_token", $values);

        $preparedStatement = $this->pdo->prepare($insertStatement);

        $preparedStatement->bindValue(':refresh_token', $refresh_token);
        $preparedStatement->bindValue(':client_id', $client_id);
        $preparedStatement->bindValue(':user_id', $user_id);
        $preparedStatement->bindValue(':expires', date('Y-m-d H:i:s', $expires));
        $preparedStatement->bindValue(':scope', $scope);

        return $preparedStatement->execute();
    }

    public function unsetRefreshToken($refresh_token) {
        $deleteStatement = $this->queryBuilder->deleteWhere("pt_spot.oauth_refresh_token", "refresh_token = :refresh_token");
        $preparedStatement = $this->pdo->prepare($deleteStatement);
        $preparedStatement->bindValue(':refresh_token', $refresh_token);
        return $preparedStatement->execute();
    }

    function __destruct() {
        $this->pdo = null;
        $this->credentials = null;
        $this->validationService = null;
        $this->currentHashAlgorithm = null;
        $this->currentHashOptions = null;
        $this->validationService = null;
        $this->queryBuilder = null;
    }

    //optional
}
