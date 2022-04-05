<?php

function readByNumbersList($connect,$numbersList){
    if ($numbersList){
        $numbersArray = pg_fetch_all($numbersList);
        $responseEcho = [];
        for ($i = 0; $i < count($numbersArray); ++$i){
            $query = "SELECT user_name,review_text,rating FROM reviews INNER JOIN users USING (user_id) WHERE phone_id = ". $numbersArray[$i]['phone_id'];
            $reviewsList = pg_query($connect, $query);
            $reviewsArray = pg_fetch_all($reviewsList);
            $responseEcho[$i] = array('phone_number' => $numbersArray[$i]['phone_number'], 'reviews' => $reviewsArray);
        }
        return json_encode($responseEcho);
    }
    else{
        return "error";
    }
}