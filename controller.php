<?php

    require_once(__DIR__ . "/ideasoft/api.connection.php");
    
    $tokenizer = new Tokenizer();
    
    if(isset($_GET["code"])) {

        $code = $_GET["code"];
        $response = $tokenizer->connectApi($code);
        echo "connection success.";

    }

?>