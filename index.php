<?php

require 'vendor/autoload.php';
include_once 'php/classes/AuthenticationService.php';
require_once 'php/classes/DatabaseService.php';

require_once 'php/dbSettings.php';

$databaseService = new DatabaseService($dbSettings);
$authService = new AuthenticationService($dbSettings);
//$crop = new CropAvatar($src, $data, $file)

$server = new OAuth2\Server($authService);

// add the grant type to your OAuth server
$server->addGrantType(new OAuth2\GrantType\UserCredentials($authService));
//$server->addGrantType(new OAuth2\GrantType\RefreshToken($authService));



$app = new \Slim\Slim();

$app->config(array(
    'templates.path' => './templates'
));

$app->get('/', function() use ($app) {
    $app->render("head.html");
    $app->render("head_home.html");
    $app->render("header.html");
    $app->render("index.html");
});

$app->get('/profile/', function() use ($app) {
    $app->render("head.html");
    $app->render("head_profile.html");
    $app->render("header.html");
    $app->render("profile.html");
});

$app->get('/search/', function() use ($app) {
    $app->render("head.html");
    $app->render("head_search.html");
    $app->render("header.html");
    $app->render("search.html");
});

$app->get('/review/', function() use ($app) {
    $app->render("head.html");
    $app->render("head_review.html");
    $app->render("header.html");
    $app->render("review.html");
});

// API group
$app->group('/account', function() use ($app, $server, $authService) {

    $app->post('/register/', function() use ($app, $authService) {
        $allPostVars = $app->request->post();

        $response = $authService->register($allPostVars["firstName"], $allPostVars["lastName"], $allPostVars["registerEmail"], $allPostVars["registerPassword"], $allPostVars["confirmPassword"], $allPostVars["birthDate"], $allPostVars["gender"], $allPostVars["role"]);

        header('Content-Type: application/json');
        echo json_encode($response);
    });

    $app->post('/login/', function() use ($server) {
        $server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();
    });

    $app->post('/status/', function() use ($server) {
        $request = OAuth2\Request::createFromGlobals();
        // Handle a request to a resource and authenticate the access token
        if (!$server->verifyResourceRequest($request)) {
            $response = array('loggedIn' => false);
        } else {
            $userID = $server->getAccessTokenData($request)["user_id"];
            $response = array('loggedIn' => true, 'userID' => intval($userID));
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    });

    $app->post('/logout/', function() use ($app, $authService) {
        //$server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();
        $accessToken = json_decode($app->getCookie("PTSpot"))->access_token;
        $authService->unsetAccessToken($accessToken);
        echo json_encode(array('success' => true, 'message' => ''));
    });

    // retrieve info about Trainer
    $app->post('/profile/:id', function($id) use ($server, $authService) {

        $isValidID = filter_var($id, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)));
        if (!$isValidID) {
            echo json_encode(array('success' => false, 'message' => 'Invalid ID'));
            die;
        }

        $fields = array(
            "user.id",
            "user.firstName",
            "user.lastName",
            "trainer.rating",
            "trainer.clarity",
            "trainer.effectiveness",
            "trainer.motivation",
            "trainer.intensity"
        );

        // Handle a request to a resource and authenticate the access token
        if ($server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            array_push($fields, "user.email");
        }

        $trainer = array('summary' => $authService->getTrainerByID($id, $fields));

        $reviewResult = $authService->getTrainerReviewsByID($id);


        if ($reviewResult) {
            $reviews = array('reviews' => $reviewResult);
        } else {
            $reviews = array('reviews' => null);
        }

        $contactResult = $authService->getTrainerContactInfoByID($id);

        if ($contactResult) {
            $contact = array('contact' => $contactResult);
        } else {
            $contact = array('contact' => null);
        }

        $response = array_merge($trainer, $reviews, $contact);


        if (!$response) {
            $response = array('success' => false, 'message' => 'array_merge failed');
        }

        header('Content-Type: application/json');
        echo json_encode(array_merge(array('success' => true), $response));
    });

    $app->post('/review/', function() use ($app, $server, $authService) {

        // Handle a request to a resource and authenticate the access token
        if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $server->getResponse()->send();
            die;
        }

        $allPostVars = $app->request->post();

        $clarity = $allPostVars["clarity"];
        $effectiveness = $allPostVars["effectiveness"];
        $motivation = $allPostVars["motivation"];
        $intensity = $allPostVars["intensity"];
        $comment = $allPostVars["comment"];
        $recommend = $allPostVars["recommend"];
        $continuing = $allPostVars["continuing"];

        /*
          if ($intensity > 5) {
          $intensity = $intensity - (2 * ($intensity - 5));
          }
         * 
         */

        $seekerID = $server->getAccessTokenData(OAuth2\Request::createFromGlobals())["user_id"];
        $trainerID = $app->getCookie("lastTrainerClicked");

        $response = $authService->leaveReview($trainerID, $seekerID, $clarity, $effectiveness, $motivation, $intensity, $comment, $recommend, $continuing);

        header('Content-Type: application/json');
        echo json_encode($response);
    });

    $app->post('/reviewStatus/', function() use ($app, $server, $authService) {

        // Handle a request to a resource and authenticate the access token
        if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $server->getResponse()->send();
            die;
        }

        $seekerID = $server->getAccessTokenData(OAuth2\Request::createFromGlobals())["user_id"];
        $trainerID = $app->getCookie("lastTrainerClicked");
        $reviewRating = $authService->fetchReview($trainerID, $seekerID, "rating");

        if ($reviewRating) {
            $response = array_merge(array('reviewed' => true), $reviewRating);
        } else {
            $response = array('reviewed' => false);
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    });

    $app->post('/edit/', function() use ($app, $server, $authService) {

        // Handle a request to a resource and authenticate the access token
        if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $server->getResponse()->send();
            die;
        }

        $userID = $server->getAccessTokenData(OAuth2\Request::createFromGlobals())["user_id"];

        $allPostVars = $app->request->post();

        $updateSuccess = $authService->updateUserByID($userID, $allPostVars);

        if ($updateSuccess) {
            $response = array('success' => true);
        } else {
            $response = array('success' => false);
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    });

    $app->post('/editAvatar/', function() use ($app, $server, $authService) {

        // Handle a request to a resource and authenticate the access token
        if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $server->getResponse()->send();
            die;
        }

        $userID = $server->getAccessTokenData(OAuth2\Request::createFromGlobals())["user_id"];

        include_once 'php/classes/CropAvatar.php';

        $crop = new CropAvatar(
                isset($_POST['avatar_src']) ? $_POST['avatar_src'] : null, isset($_POST['avatar_data']) ? $_POST['avatar_data'] : null, isset($_FILES['avatar_file']) ? $_FILES['avatar_file'] : null
        );

        $dst = $crop->getResult();

        $updateSuccess = $authService->updateAvatarByID($userID, $dst);

        unlink($dst);

        if ($updateSuccess) {
            $response = array('success' => true);
        } else {
            $response = array_merge(array('success' => false), array('message' => $crop->getMsg()));
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    });

    $app->post('/editWebsiteLink/', function() use ($app, $server, $authService) {

        // Handle a request to a resource and authenticate the access token
        if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $server->getResponse()->send();
            die;
        }

        $userID = $server->getAccessTokenData(OAuth2\Request::createFromGlobals())["user_id"];

        $url = $app->request->post()["link"];

        // Remove all illegal characters from a url
        $sanitizedURL = filter_var($url, FILTER_SANITIZE_URL);

// Validate url
        if (!filter_var($sanitizedURL, FILTER_VALIDATE_URL)) {
            $response = array('success' => false, 'message' => 'Invalid URL. Be sure to include "http://" at the beginning.');
        } else {
            $response = $authService->updateWebsiteLinkByID($userID, $url);
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    });

    $app->post('/editEmail/', function() use ($app, $server, $authService) {

        // Handle a request to a resource and authenticate the access token
        if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $server->getResponse()->send();
            die;
        }

        $id = $server->getAccessTokenData(OAuth2\Request::createFromGlobals())["user_id"];

        $newEmail = $app->request->post()["email"];

        $response = $authService->updateEmailByID($id, $newEmail);

        header('Content-Type: application/json');
        echo json_encode($response);
    });
});

$app->group('/api', function() use ($app, $databaseService) {
    // retrieve all Trainers in database
    // default sort is pt score
    // sort methods include overall rating, total reviews, and pt score
    $app->post('/search/', function() use ($databaseService) {
        $response = $databaseService->getAllTrainers();

        if (!$response) {
            $response = null;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    });



    // retrieve avatar
    $app->get('/avatar/:id', function($id) use ($databaseService) {

        $response = $databaseService->getAvatarByID($id);

        //var_dump($response);
        if (!$response) {
            $response = "fail";
        }

        header('Content-Type: image/png');
        echo $response["avatar"];
    });
});

$app->run();
