<?php

    //Database Settings
    define("HOST", "localhost");
    define("DBNAME", "***");
    define("USER", "***");
    define("PASS", "***");

    //Shop Settings
    define("URL", "***");
    define("CLIENTID", "***");
    define("SECRETID", "***");
    define("REDIRECT", "***");
    define("ACCESS", URL . "/admin/user/auth?client_id=" . CLIENTID . "&response_type=code&state=***&redirect_uri=" . REDIRECT);

?>
