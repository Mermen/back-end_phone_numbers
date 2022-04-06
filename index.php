<?php
header('Content-type: json/application');
require 'connect_to_pgsql.php';
require 'functions.php';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

switch ($uri[1]){
    case 'numbers':{
        $database = new Database();
        $connect = $database->getConnection();
        $numbersList = pg_query($connect, "SELECT phone_id,phone_number FROM phone_numbers");
        $responseEcho = readByNumbersList($connect,$numbersList);
        echo $responseEcho;
        break;
    }
    case 'number':{
        $number = '';
        $international = 0;
        $database = new Database();
        $connect = $database->getConnection();
        $check = 0;
        $responseEcho = [];
        if (count($uri)<3 || count($uri)>3){
            $responseEcho = array('error'=>"count error");
        }
        else {
            if (!(strlen($uri[2])<11 || strlen($uri[2])>16)){
                $countryId = '';
                $countryName = '';
                if ($uri[2][0]==='+'){
                    $international = 1;
                }
                else{
                    $countryId = 'RU';
                    $countryName = 'Россия';
                }
                $number = ltrim($uri[2],"+");
                if (ctype_digit($number)){
                    if($international) {
                        $countryCode = substr($number, 0, strlen($number) - 10);
                        $countryQuery = pg_query($connect, "SELECT country_id,country_name FROM country_сodes WHERE phone_code = " . $countryCode);
                        $countryArray = pg_fetch_all($countryQuery);
                        if ($countryArray) {
                            $countryId = $countryArray[0]['country_id'];
                            $countryName = $countryArray[0]['country_name'];
                            $responseEcho['phone_number'] = $uri[2];
                            $responseEcho['country_id'] =$countryId;
                            $responseEcho['country_name'] = $countryName;
                            $check =1;
                        }
                        else {
                            $responseEcho = array('error'=>"countryCode error");
                        }
                    }
                    else{
                        $responseEcho['phone_number'] = $uri[2];
                        $responseEcho['country_id'] =$countryId;
                        $responseEcho['country_name'] = $countryName;
                        $check =1;
                    }

                }
                else {
                    $responseEcho = array('error'=>"cType digit error");
                }

            }
            else{
                $responseEcho = array('error'=>"numberLen error");
            }
        }
        if($check){
            $countQuery = pg_query($connect, "SELECT COUNT(*) FROM phone_numbers WHERE phone_number = " . $number);
            $countArray = pg_fetch_all($countQuery);
            if (!$countArray[0]['count']) {
                $insertQuery = pg_query($connect, "INSERT INTO phone_numbers(phone_number, international) VALUES (".$number.",".$international.")");
                $insertArray = pg_fetch_all($countQuery);
            }
            $query = "SELECT phone_id FROM phone_numbers WHERE phone_number = ". $number;
            $phoneIdList = pg_query($connect, $query);
            $phoneIdArray = pg_fetch_all($phoneIdList);
            $query = "SELECT review_id, user_name,review_text,rating FROM reviews INNER JOIN users USING (user_id) WHERE phone_id = ". $phoneIdArray[0]['phone_id'];
            $reviewsList = pg_query($connect, $query);
            $reviewsArray = pg_fetch_all($reviewsList);
            $responseEcho['reviews']=$reviewsArray;
        }
        echo json_encode($responseEcho);
        break;
    }
    case 'review':{
        $international=0;
        $rev = explode( '&', $uri[2] );
        if ($rev[0][0]==='+'){
            $international = 1;
        }
        $number = ltrim($rev[0],"+");
        $database = new Database();
        $connect = $database->getConnection();
        $countQuery = pg_query($connect, "SELECT COUNT(*) FROM phone_numbers WHERE phone_number = " . $number);
        $countArray = pg_fetch_all($countQuery);
        if (!$countArray[0]['count']) {
            $insertQuery = pg_query($connect, "INSERT INTO phone_numbers(phone_number, international) VALUES (".$number.",".$international.")");
            $insertArray = pg_fetch_all($countQuery);
        }
        $query = "SELECT phone_id FROM phone_numbers WHERE phone_number = ". $number;
        $phoneIdList = pg_query($connect, $query);
        $phoneIdArray = pg_fetch_all($phoneIdList);
        $phoneId = $phoneIdArray[0]['phone_id'];
        $query = "SELECT user_id FROM users WHERE token = '". $rev[2]."'";
        $userIdList = pg_query($connect, $query);
        $userIdArray = pg_fetch_all($userIdList);
        $userId = $userIdArray[0]['user_id'];
        $insertReviewQuery = pg_query($connect, "INSERT INTO reviews(user_id, phone_id,review_text,rating) VALUES (".$userId.",".$phoneId.", '".$rev[1]."', 0)");
        $insertReviewArray = pg_fetch_all($countQuery);

        echo json_encode("done");



        break;
    }
    case 'find':{
        $international=0;
        $rev = explode( '&', $uri[2] );
        if ($rev[0][0]==='+'){
            $international = 1;
        }
        $number = ltrim($rev[0],"+");
        $database = new Database();
        $connect = $database->getConnection();
        $responseEcho = [];
        if (ctype_digit($number)) {
            $query = "SELECT phone_id,phone_number FROM phone_numbers WHERE phone_number::text LIKE '". $number ."%'";
            $phoneIdList = pg_query($connect, $query);
            $phoneIdArray = pg_fetch_all($phoneIdList);
            for ($i = 0; $i < count($phoneIdArray); ++$i){
                $query = "SELECT COUNT(*) FROM reviews INNER JOIN users USING (user_id) WHERE phone_id = ". $phoneIdArray[$i]['phone_id'];
                $reviewsList = pg_query($connect, $query);
                $reviewsArray = pg_fetch_all($reviewsList);
                $responseEcho[$i] = array('phone_number' => $phoneIdArray[$i]['phone_number'], 'reviews' => $reviewsArray);
            }
            echo json_encode($responseEcho);
        }
        else{
            $responseEcho = array('error'=>"cType digit error");
        }

        break;
    }
    default:{
        echo "error";
    }
}




