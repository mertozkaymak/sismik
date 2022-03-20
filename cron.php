<?php
    
    ini_set("allow_url_fopen", 1);

    $jsonUrl = "https://www.sismikmarket.com/dosya/excel-urunler.json";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $jsonUrl);
    $result = curl_exec($ch);
    curl_close($ch);

    $obj = json_decode($result);

    require_once "excel/PHPExcel.php";
    require_once "excel/PHPExcel/IOFactory.php";

    file_put_contents( __DIR__ . "/excel.xlsx",
        file_get_contents("https://app6.bosch.de/cgi-bin/WebObjects.dll/V5Prod_CatalogWeb.woa/wa/Exchange/material?exchange=s9g4r3d7lv0hhuqr6e5jbgcdir&customer=54002980")
    );
    
    $inputFileName = __DIR__ . "/excel.xlsx";

    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);

    $sheet = $objPHPExcel->getSheet(0); 
    $highestRow = $sheet->getHighestRow(); 
    $highestColumn = $sheet->getHighestColumn();

    $targetProduct = array();

    for ($row = 2; $row <= $highestRow; $row++){ 
        
        $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
        for ($index=0; $index < count($obj); $index++) {
            if(str_replace(".", "", $obj[$index]->SKU) == $rowData[0][0]){
                array_push($targetProduct, $rowData[0]);
            }
        }
    }

    unlink(__DIR__ . "/excel.xlsx");

    function skuFormat($array) {
        $sku = str_split($array[0]);
        $formattedSku = "";
        
        for ($index = 0; $index < count($sku); $index++) {
            if($index % 3 == 0 && $index < count($sku) - 1){
                $formattedSku .= $sku[$index] . ".";
            }
            else{
                $formattedSku .= $sku[$index];
            }
        }
        
        $array[0] = $formattedSku;
        return $array;
    }
    
    for ($index = 0; $index < count($targetProduct); $index++) { 
        $targetProduct[$index] = skuFormat($targetProduct[$index]);
    }
    
    try {

        require_once __DIR__ . "/ideasoft/api.config.php";
        require_once __DIR__ . "/ideasoft/api.update.product.php";
                
        $dbh = new PDO("mysql:host=" . HOST . "; dbname=" . DBNAME . "; charset=utf8;", USER, PASS);

        for ($index = 0; $index < count($targetProduct); $index++) {

            $smtp = $dbh->prepare("SELECT * FROM products WHERE sku = ?");
            $smtp->execute(array($targetProduct[$index][0]));
            $result = $smtp->fetch(PDO::FETCH_ASSOC);

            if($result){

                for ($index2 = 0; $index2 < count($obj); $index2++) {

                    if($obj[$index2]->SKU == $result["sku"]){

                        $price = floatval($targetProduct[$index][4]) * floatval($obj[$index2]->KarMarjı);

                        if($targetProduct[$index][2] == "C"){
                            new updateProduct($result["iid"], $price, $targetProduct[$index][6], 0);
                        }
                        else if($targetProduct[$index][2] == "A" || $targetProduct[$index][2] == "B"){
                            new updateProduct($result["iid"], $price, $targetProduct[$index][6], 1);
                        }

                    }

                }

            }
        }
        
    } catch (PDOException $ex) {

        $ex->getMessage();

    }

?>