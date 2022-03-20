<?php

    require_once(__DIR__ . "/ideasoft/api.connection.php");
    
    $tokenizer = new Tokenizer();
    $status = $tokenizer->tokenController();

    if($status == 1) {

        echo "connection has been declared.";

    }
    else {

        echo $status;

    }
?>