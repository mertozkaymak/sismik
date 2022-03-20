<?php

    require_once(__DIR__ . "/api.config.php");

    class Database{

        protected $dbh;

        protected function connect() {

            try {
                
                $this->dbh = new PDO("mysql:host=" . HOST . "; dbname=" . DBNAME . "; charset=utf8;", USER, PASS);
                
            } catch (PDOException $ex) {

                $ex->getMessage();

            }

        }

        protected function disconnect() {

            $this->dbh = NULL;

        }

    }

    class Tokenizer extends Database{

        protected $acc;
        protected $ref;

        public function __construct() {

            parent::connect();

        }
        
        public function __destruct() {

            parent::disconnect();

        }

        public function tokenController() {
            
            $smtp = $this->dbh->prepare("SELECT access_token, refresh_token FROM ideasoft WHERE access_token IS NOT NULL AND refresh_token IS NOT NULL LIMIT 1");
            $smtp->execute();
            $result = $smtp->fetch(PDO::FETCH_ASSOC);

            if($result["access_token"] == NULL || $result["refresh_token"] == NULL) {
                
                $smtp = NULL;
                return "<a href='" . ACCESS . "'>CONNECT API</a>";

            }
            else {

                $this->acc = $result["access_token"];
                $this->ref = $result["refresh_token"];
                $smtp = NULL;
                return 1;

            }

        }

        public function connectApi($code) {
            
            $url = URL . "/oauth/v2/token";
            $postRequest = array(
                'grant_type'    => 'authorization_code',
                'client_id'     => CLIENTID,
                'client_secret' => SECRETID,
                'code'          => $code,
                'redirect_uri'  => REDIRECT
            );

            $cURL = curl_init($url);
            curl_setopt($cURL, CURLOPT_POSTFIELDS, $postRequest);
            curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
            
            $apiResponse = curl_exec($cURL);
            curl_close($cURL);
            $jsonResponse = (array) json_decode($apiResponse);

            $smtp = $this->dbh->prepare("UPDATE ideasoft SET access_token = ?, refresh_token = ? WHERE id = 1");
            $smtp->execute(array($jsonResponse["access_token"], $jsonResponse["refresh_token"]));

            $this->acc = $jsonResponse["access_token"];
            $this->ref = $jsonResponse["refresh_token"];

        }

    }

?>