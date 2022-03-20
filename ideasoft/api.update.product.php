<?php

    require_once(__DIR__ . "/api.connection.php");
    require_once(__DIR__ . "/api.config.php");

    class updateProduct extends Tokenizer{

        public function __construct($id, $price, $currency, $stock) {

            $tokenizer = new Tokenizer();
            $status = $tokenizer->tokenController();

            if($status == 1){

                $url = URL . "/api/products/$id";

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Authorization: Bearer " . $tokenizer->acc, "Content-Type: application/json; charset=utf-8", "Content-Encoding: gzip"
                ));
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, 
                         json_encode(array(
                             "stockAmount" => $stock
                         )));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $server_output = curl_exec($ch);
                curl_close ($ch);

                echo $server_output;

            }

        }
    }
?>