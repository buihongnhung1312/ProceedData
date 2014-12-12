<?php 

class handleCSV{
    public $server="localhost";
    public $data="dbtest";
    public $user="root";
    public $pass="";

	public function __construct($fileName) {
        //connect database                                
        $connect= mysql_connect($this->server,$this->user,$this->pass)
        or die("Error!!");
        //select database
        $this->fileName = $fileName;


        $select= mysql_select_db($this->data);
        mysql_query("set names 'utf8'");
	}

	public function __destruct() {
		// @mysql_close();
        // @mysql_free_result();
	}

    public function createTables() 
    {
		$sql = 'CREATE TABLE IF NOT EXISTS `option` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;';
		mysql_query($sql);

		$sql = 'CREATE TABLE IF NOT EXISTS `option_value` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `option_id` int(11) NOT NULL,
		  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `IDX_249CE55CA7C41D6F` (`option_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;';
		mysql_query($sql);

		$sql = 'CREATE TABLE IF NOT EXISTS `product` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
		  `available_on` datetime NOT NULL,
		  `created_at` datetime NOT NULL,
		  `updated_at` datetime DEFAULT NULL,
		  `deleted_at` datetime DEFAULT NULL,
		  `variant_selection_method` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;';
		mysql_query($sql);

		$sql = 'CREATE TABLE IF NOT EXISTS `product_option_value` (
		  `product_id` int(11) NOT NULL,
		  `option_value_id` int(11) NOT NULL,
		  PRIMARY KEY (`product_id`,`option_value_id`),
		  KEY `IDX_38FA41144584665A` (`product_id`),
		  KEY `IDX_38FA4114A7C41D6F` (`option_value_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
		mysql_query($sql);

		$sql = 'CREATE TABLE IF NOT EXISTS `product_category` (
		  `product_id` int(11) NOT NULL,
		  `category_id` int(11) NOT NULL,
		  PRIMARY KEY (`product_id`,`category_id`),
		  KEY `IDX_FA3B58F74584665A` (`product_id`),
		  KEY `IDX_FA3B58F7DE13F470` (`category_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
		mysql_query($sql);

		$sql = 'CREATE TABLE IF NOT EXISTS `category` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;';
		mysql_query($sql);
    }

    public function run() 
    {    
    	$time_start = microtime(true);

    	$this->createTables();
    	$this->removeConstraint();

    	$strFields = "";
		$strSet = "";

		$numberOfColumn = 12;
		$tempTable = 'temporary';
		$options = array();

		$this->file = fopen("assets/".$this->fileName.".csv","r");
		$firstRow = fgetcsv($this->file);

		$i=0;
		foreach ($firstRow as $key) {
			$columns[$i][0] = $key;
			$columns[$i][1] = $i;
			$temp = explode("_", $key);
			$i++;
		}

		foreach ($columns as $key) {
			switch ($key[0]) {
				case 'id': 
					$temp[0] = $key;
					break;

				case 'name' : 
					$temp[1] = $key;
					break;

				case 'slug' : 
					$temp[2] = $key;
					break;

				case 'short_description' : 
					$temp[3] = $key;
					break;

				case 'description' : 
					$temp[4] = $key;
					break;

				case 'available_on' : 
					$temp[5] = $key;
					break;

				case 'created_at' : 
					$temp[6] = $key;
					break;

				case 'updated_at' : 
					$temp[7] = $key;
					break;

				case 'deleted_at' : 
					$temp[8] = $key;
					break;

				case 'variant_selection_method' : 
					$temp[9] = $key;
					break;

				case 'option_color' : 
					$temp[10] = $key;
					break;

				case 'option_size' : 
					$temp[11] = $key;
					break;

				case 'categories' : 
					$temp[12] = $key;
					break;

				default:
					// $temp[50] = "ASDASD";
					break;
			}
		}

		//Check the column that should be in DATE format instead of string.
		for ($i=0; $i <= $numberOfColumn; $i++) { 
			for ($j=0; $j <= $numberOfColumn; $j++) { 
				if($temp[$j][1]==$i)
				{
					if($temp[$j][0] == 'available_on' ||
						$temp[$j][0] == 'created_at' ||
						$temp[$j][0] == 'updated_at' ||
						$temp[$j][0] == 'deleted_at') 
					{
						$strSet .= $temp[$j][0] . ' = str_to_date(@' . $temp[$j][0] . ', "%m/%d/%Y %H:%i"), ';
						$strFields .= "@".$temp[$j][0].", ";
					}
					else {
						$strFields .= $temp[$j][0].", ";
					}
				}
			}
		}

		$strFields = rtrim($strFields,", ");
		$strSet = rtrim($strSet,", ");


		//Create a temporary table
    	$sql = '
    	CREATE TABLE IF NOT EXISTS ' . $tempTable . ' (
		  id int(11) NOT NULL,
		  name varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		  slug varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		  short_description varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
		  description longtext COLLATE utf8_unicode_ci NOT NULL,
		  available_on datetime NOT NULL,
		  created_at datetime NOT NULL,
		  updated_at datetime DEFAULT NULL,
		  deleted_at datetime DEFAULT NULL,
		  option_color varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		  option_size varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		  categories varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		  variant_selection_method varchar(255) COLLATE utf8_unicode_ci NOT NULL
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;';

		mysql_query($sql);

		//Load data from csv file to insert into temporary table
		$sql ='
			LOAD DATA LOCAL INFILE "assets/'.$this->fileName.'.csv" 
			REPLACE INTO TABLE '.$tempTable.'
			FIELDS TERMINATED BY "," 
			LINES TERMINATED BY "\\r\\n"
			ignore 1 lines
			('.$strFields.')
			set '.$strSet.';';
		mysql_query($sql);

		$sql = "select * from ".$tempTable;
		$rows = mysql_query($sql);

		//Get the column from the table.
		$numberOfRows = mysql_num_rows($rows);
		for ($i=0; $i < $numberOfRows; $i++) { 
			$row = mysql_fetch_assoc($rows);
			$results_color[] = $row["option_color"];
			$results_size[] = $row["option_size"];
			$results_categories[] = $row["categories"];
			$results_id[] = $row["id"];
		}

		//Explode to get component in order to insert to table.
		for ($i=0; $i < $numberOfRows; $i++) { 
			$colors = explode(";", $results_color[$i]);
			$sizes = explode(";", $results_size[$i]);
			$categories = explode(";", $results_categories[$i]);
			foreach ($colors as $key) {
				$color[] = $key;
			}

			foreach ($sizes as $key) {
				$size[] = $key;
			}

			foreach ($categories as $key) {
				$category[] = $key;
			}
		}


		//Insert into option
		$sql = "insert into `option` (id,name) values (1, 'size'), (2, 'color');";
		mysql_query($sql);

		//Insert all the size
		$size = array_unique($size);
		$sql = "insert into `option_value` (option_id,value) values ";
		foreach ($size as $key) {
			@$sql .= "('1', '".$key."'),";
		}
		$sql = rtrim($sql, ",");
		mysql_query($sql);

		//Insert all the color
		$color = array_unique($color);
		$sql = "insert into `option_value` (option_id,value) values ";
		foreach ($color as $key) {
			@$sql .= "('2', '".$key."'),";
		}
		$sql = rtrim($sql, ",");
		mysql_query($sql);

		//Insert all the categories
		$category = array_unique($category);
		$sql = "insert into `category` (name) values ";
		foreach ($category as $key) {
			$sql .= "('".$key."'), ";
		}
		$sql = rtrim($sql, ", ");
		mysql_query($sql);

		//Insert contraint in product_category
		$sqlProduct_Category = "insert into `product_category` (product_id,category_id) values ";
		foreach ($results_id as $i) {
			$categories_of_a_product = explode(";", $results_categories[$i-1]);
			$sql = "select id from `category` where ";
			foreach ($categories_of_a_product as $key) {
				$sql .= " name='".$key."' or";
			}
			$sql = rtrim($sql, " or");


			$categories_product = mysql_query($sql);

			for ($k=0; $k < mysql_num_rows($categories_product); $k++) { 
				$category_product = mysql_fetch_assoc($categories_product);
				if($i != 0)
					$sqlProduct_Category .= "('".$i."', '".$category_product["id"]."'),";
			}
		}
		$sqlProduct_Category = rtrim($sqlProduct_Category, ",");

		mysql_query($sqlProduct_Category);

		//Insert contraint in product_option_value
		$temp = 0;
		$temp2 = 0;
		$sqlProduct_Option_value = "insert into `product_option_value` (product_id,option_value_id) values ";
		foreach ($results_id as $i) {
			$sizes_of_a_product = explode(";", $results_size[$i-1]);
			$sql = "select id from `option_value` where option_id = 1 and (";
			foreach ($sizes_of_a_product as $key) {
				$sql .= " value='".$key."' or";
			}	
			$sql = rtrim($sql, " or");
			$sql .= ");";

			$product_option_values = mysql_query($sql);

			for ($k=0; $k < mysql_num_rows($product_option_values); $k++) { 
				$product_option_value = mysql_fetch_assoc($product_option_values);
				if($i != 0)
					$sqlProduct_Option_value .= "('".$i."', '".$product_option_value["id"]."'),";
			}

			$colors_of_a_product = explode(";", $results_color[$i-1]);
			$sql = "select id from `option_value` where option_id = 2 and (";
			foreach ($colors_of_a_product as $key) {
				$sql .= " value='".$key."' or";
			}	
			$sql = rtrim($sql, " or");
			$sql .= ");";

			$product_option_values = mysql_query($sql);

			for ($k=0; $k < mysql_num_rows($product_option_values); $k++) { 
				$product_option_value = mysql_fetch_assoc($product_option_values);
				if($i != 0)
					$sqlProduct_Option_value .= "('".$i."', '".$product_option_value["id"]."'),";
			}
		}
		$sqlProduct_Option_value = rtrim($sqlProduct_Option_value, ",");
		mysql_query($sqlProduct_Option_value);

		//insert data from temporary table to product table
		$sql = '
		INSERT INTO product
		(id,name,slug,short_description,description,available_on,created_at,updated_at,deleted_at,variant_selection_method)
		SELECT id,name,slug,short_description,description,available_on,created_at,updated_at,deleted_at,variant_selection_method
		FROM temporary;';
		mysql_query($sql);

		$sql = 'DROP TABLE '.$tempTable;
		mysql_query($sql);

		$this->addConstraint();

		$time_end = microtime(true);



		return ($time_end-$time_start);
	}

	public function addConstraint() {
		$sql = '
		ALTER TABLE `option_value`
  		ADD CONSTRAINT `FK_249CE55CA7C41D6F` FOREIGN KEY (`option_id`) REFERENCES `option` (`id`);
		';
		mysql_query($sql);

		$sql .= '
		ALTER TABLE `product_option_value`
  		ADD CONSTRAINT `FK_38FA41144584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  		ADD CONSTRAINT `FK_38FA4114A7C41D6F` FOREIGN KEY (`option_value_id`) REFERENCES `option_value` (`id`);
		';
		mysql_query($sql);

		$sql .= '
		ALTER TABLE `product_category`
  		ADD CONSTRAINT `FK_FA3B58F74584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  		ADD CONSTRAINT `FK_FA3B58F7DE13F470` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`);
		';
		mysql_query($sql);
	}

	public function removeConstraint() {
		$sql = '
		ALTER TABLE `option_value`
  		DROP CONSTRAINT `FK_249CE55CA7C41D6F`
		';
		mysql_query($sql);

		$sql .= '
		ALTER TABLE `product_option_value`
  		DROP CONSTRAINT `FK_38FA41144584665A`,
  		DROP CONSTRAINT `FK_38FA4114A7C41D6F`
		';
		mysql_query($sql);

		$sql .= '
		ALTER TABLE `product_category`
  		DROP CONSTRAINT `FK_FA3B58F74584665A`,
  		DROP CONSTRAINT `FK_FA3B58F7DE13F470`
		';
		mysql_query($sql);

	}
}
 ?>