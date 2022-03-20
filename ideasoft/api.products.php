<?php

    require_once(__DIR__ . "/api.connection.php");
    require_once(__DIR__ . "/api.config.php");

    class Products extends Tokenizer{

        protected $products = array();

        public function __construct() {

            $tokenizer = new Tokenizer();
            $status = $tokenizer->tokenController();

            if($status == 1){

                $page = 1;
                $total_pages = 0;
                $stop = 0;

                while ($stop == 0) {

                    $url = URL . "/api/products?limit=100&sort=-id&page=" . $page;
                    $variables = array("Authorization: Bearer " . $tokenizer->acc, "Content-Type: application/json; charset=utf-8");

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HEADER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $variables);

                    $res = curl_exec($ch);
                    curl_close($ch);
                    
                    $response = explode("[{", $res, 2);
                    $header = $response[0];
                    $body = $response[1];

                    if($total_pages == 0){

                        $total_pages = floor(intval(trim(explode("\n", explode(":", $header)[9])[0])) / 100) + 1;
                        
                    }

                    if($page == $total_pages){

                        $stop = 1;

                    }
                    else{

                        $page++;
                        sleep(2);

                    }

                    $body = "[{" . $body;
                    $body = json_decode($body);
                    array_push($this->products, $body);

                }

                $this->asyncProducts();
                
            }

        }

        public function doFlush() {
		
			if (!headers_sent()) {
				
				ini_set('zlib.output_compression', 0);			
				header('Content-Encoding: none');
			}
			
			echo str_pad('', 4 * 1024);
			
			do {
				$flushed = @ob_end_flush();
			} while ($flushed);
			@ob_flush();
			flush();
			
		}

        public function asyncProducts() {

            $database = new Database();
            $database->connect();

            for ($page=0; $page < count($this->products); $page++) {
             
                for ($product=0; $product < count($this->products[$page]); $product++) {

                    echo "<pre>";
                    print_r($this->products[$page][$product]);
                    echo "</pre>";
                    
                    $smtp = $database->dbh->prepare("SELECT * FROM products WHERE iid=?");
                    $smtp->execute(array($this->products[$page][$product]->id));
                    $result = $smtp->fetchAll(PDO::FETCH_ASSOC);

                    if(count($result) > 0){

                        $smtp = $database->dbh->prepare("UPDATE products SET name=?, sku=?, stock_amount=? WHERE iid=?");
                        $smtp->execute(array(
                            $this->products[$page][$product]->name,
                            $this->products[$page][$product]->sku,
                            $this->products[$page][$product]->stockAmount,
                            $this->products[$page][$product]->id
                        ));

                    }
                    else{

                        $smtp = $database->dbh->prepare("INSERT INTO products(iid, name, sku, stock_amount) VALUES (?, ?, ?, ?)");
                        $smtp->execute(array(
                            $this->products[$page][$product]->id,
                            $this->products[$page][$product]->name,
                            $this->products[$page][$product]->sku,
                            $this->products[$page][$product]->stockAmount,
                        ));

                    }
                    
                }

            }

        }

    }

    new Products();

?>