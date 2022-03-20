<?php

    require_once(__DIR__ . "/api.connection.php");
    require_once(__DIR__ . "/api.config.php");

    class TakeToken extends Database{

        public function __construct() {

            parent::connect();

        }

        public function __destruct() {

            parent::disconnect();

        }

        public function takeNewToken() {

            $smtp = $this->dbh->prepare("SELECT access_token, refresh_token FROM ideasoft WHERE id = 1");
            $smtp->execute();
            $result = $smtp->fetch(PDO::FETCH_ASSOC);

            $url = URL . "/oauth/v2/token";
            $postRequest = array(
                'grant_type' => 'refresh_token',
                'client_id' => CLIENTID,
                'client_secret' => SECRETID,
                'refresh_token' => $result["refresh_token"]
            );

            $cURL = curl_init($url);
            curl_setopt($cURL, CURLOPT_POSTFIELDS, $postRequest);
            curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);

            $apiResponse = curl_exec($cURL);
            curl_close($cURL);
            $jsonResponse = (array) json_decode($apiResponse);

            $smtp = $this->dbh->prepare("UPDATE ideasoft SET access_token = ?, refresh_token = ? WHERE id = 1");
            $smtp->execute(array($jsonResponse["access_token"], $jsonResponse["refresh_token"]));

        }

    }

    $take_token = new TakeToken();
    $take_token->takeNewToken();
    
?>