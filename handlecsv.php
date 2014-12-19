<?php 

function cmp0($a, $b) 
{ 

     return strcmp($a[0], $b[0]); 
} 

function cmp1($a, $b) 
{ 

     return strcmp($a[1], $b[1]); 
} 

class handleCSV
{
    public $sizeValue = 1;
    public $colorValue = 2;
    public $times = 0;

	public $server="localhost";
    public $data="test";
    public $user="root";
    public $pass="";

    public $lines_per_file = 5000;

    public $prev_color=array();
    public $prev_category=array();
    public $prev_size=array();

    public $categoryID = 1;
    public $sizeID = 1;
    public $colorID = 1;

	function __construct($fileName)
	{
        $connect= mysql_connect($this->server,$this->user,$this->pass)
        or die("Error!!");

        $select= mysql_select_db($this->data);
        mysql_query("set names 'utf8'");
        $this->fileName = $fileName;
	}

    public function __destruct() {
        @mysql_close(); 
        @mysql_free_result();   
    }

    //Dung de sort cac phan tu theo 1 cot cho trc.
    //Ko the dung usort vi day la mang 2 chieu.
	function array_sort_bycolumn(&$array,$column,$dir = 'asc') {
	    foreach($array as $a) {
	    	$sortcol[$a[$column]][] = $a;
	    }
	    ksort($sortcol);
	    foreach($sortcol as $col) {
	        foreach($col as $row) {
	        	$newarr[] = $row;
	        }
	    }

	    if($dir=='desc') $array = array_reverse($newarr);
	    else $array = $newarr;
	}

	//Dung de xoa cac phan tu giong nhau.
	//Ko the xai unique dc, vi day la mang 2 chieu.
	function array_unique_multidimensional(&$input,$column,$array_size)
	{
		for ($i=0; $i < $array_size-1;) { 
			$k=1;
			while(@$input[$i][1] == @$input[$i+$k][1]) {
				$input[$i+$k][0] = $input[$i][0];
				$k++;
			}
			$i += $k;
		}
		return $input;
	}

    function array_delete_repeate(&$input,$array_size)
    {
        for ($i=0; $i < $array_size-1;) { 
            $k=1;
            while(@$input[$i][1] == @$input[$i+$k][1] &&
                @$input[$i][0] == @$input[$i+$k][0] &&
                @$input[$i][2] == @$input[$i+$k][2]) {
                echo "ASDASDAS";
                unset($input[$i+$k][0]);
                $k++;
            }
            $i += $k;
        }
        return $input;
    }


	//Tu 1 file csv, tach cac cot nhu category, option_size, option_color ra 2 file khac.
    public function createChildCSV_m($fileName) {
    	//Tạo file tạm : temp_category, temp_option
        $w_category = fopen("files/temp_category.csv", "a+");
        $w_size = fopen("files/temp_size.csv", "a+");
        $w_color = fopen("files/temp_color.csv", "a+");

    	$originCSV = fopen($fileName.".csv","r");

    	$firstRow = fgetcsv($originCSV);

        foreach ($firstRow as $key) {
            $columns[] = $key;
        }

        $i=0;
        foreach ($columns as $key) {    
            switch ($key) {
                case 'id': 
                    $col_id = array("offset" => $i, "name" => "id");
                    $i++;
                    break;

                case 'name' : 
                    $col_name = array("offset" => $i, "name" => "name");
                    $i++;
                    break;

                case 'slug' : 
                    $col_slug = array("offset" => $i, "name" => "slug");
                    $i++;
                    break;

                case 'short_description' : 
                    $col_short_description = array("offset" => $i, "name" => "short_description");
                    $i++;
                    break;

                case 'description' : 
                    $col_description = array("offset" => $i, "name" => "description");
                    $i++;
                    break;

                case 'available_on' : 
                    $col_available_on = array("offset" => $i, "name" => "available_on");
                    $i++;
                    break;

                case 'created_at' : 
                    $col_created_at = array("offset" => $i, "name" => "created_at");
                    $i++;
                    break;

                case 'updated_at' : 
                    $col_updated_at = array("offset" => $i, "name" => "updated_at");
                    $i++;
                    break;

                case 'deleted_at' : 
                    $col_deleted_at = array("offset" => $i, "name" => "deleted_at");
                    $i++;
                    break;

                case 'variant_selection_method' : 
                    $col_variant_selection_method = array("offset" => $i, "name" => "variant_selection_method");
                    $i++;
                    break;

                case 'option_color' : 
                    $col_option_color = array("offset" => $i, "name" => "option_color");
                    $i++;
                    break;

                case 'option_size' : 
                    $col_option_size = array("offset" => $i, "name" => "option_size");
                    $i++;
                    break;

                case 'categories' : 
                    $col_categories = array("offset" => $i, "name" => "categories");
                    $i++;
                    break;

                default:
                    // $temp[50] = "ASDASD";
                    break;
            }
        }


    	$time_add_to_final1 = microtime(true);

    	unset($this->current_color);
		unset($this->current_size);
		unset($this->current_category);

    	while ($cols = fgetcsv($originCSV)) {
	    		@$color_on_1_row = explode(";", $cols[$col_option_color["offset"]]);
	            foreach ($color_on_1_row as $key) {
	                $final_color[] = array($this->colorID, str_replace("\r\n", "", $key), $cols[$col_id["offset"]]);
	                $this->colorID++;
	            }
  		}
    	fclose($originCSV);
        handleCSV::deduplicateInArray($final_color,"color",$this->colorValue);
        unset($final_color);

    	$originCSV = fopen($fileName.".csv","r");
    	while ($cols = fgetcsv($originCSV)) {
            @$size_on_1_row = explode(";", $cols[$col_option_size["offset"]]);
            foreach ($size_on_1_row as $key) {
                $final_size[] = array($this->sizeID, str_replace("\r\n", "", $key), $cols[$col_id["offset"]]);
                $this->sizeID++;
            }
    	}
    	fclose($originCSV);
        handleCSV::deduplicateInArray($final_size,"size",$this->sizeValue);
        unset($final_size);

    	$originCSV = fopen($fileName.".csv","r");
    	while ($cols = fgetcsv($originCSV)) {
    		@$category_on_1_row = explode(";", $cols[$col_categories["offset"]]);
	    	foreach ($category_on_1_row as $key) {
	    	    $final_category[] = array($this->categoryID, str_replace("\r\n", "", $key), $cols[$col_id["offset"]]);
	    	    $this->categoryID++;
	    	}
    	}
    	fclose($originCSV);
        handleCSV::deduplicateInArray($final_category,"category");
        unset($final_category);

    	$time_add_to_final2 = microtime(true);
    	$time_to_write1 = microtime(true);

    	$time_to_write2 = microtime(true);

    	fclose($w_size);
		fclose($w_category);
		fclose($w_color);

        echo "<br>time_to_write : ".($time_to_write2-$time_to_write1);
        echo "<br>time_add_to_final : ".($time_add_to_final2-$time_add_to_final1);
    }

    public function createChildCSV_p($fileName) {
    	//Tạo file tạm : temp_category, temp_option
        $w_category = fopen("files/temp_category.csv", "a+");
        $w_size = fopen("files/temp_size.csv", "a+");
        $w_color = fopen("files/temp_color.csv", "a+");

    	$originCSV = fopen($fileName.".csv","r");

    	$firstRow = fgetcsv($originCSV);

        foreach ($firstRow as $key) {
            $columns[] = $key;
        }

        $i=0;
        foreach ($columns as $key) {    
            switch ($key) {
                case 'id': 
                    $col_id = array("offset" => $i, "name" => "id");
                    $i++;
                    break;

                case 'name' : 
                    $col_name = array("offset" => $i, "name" => "name");
                    $i++;
                    break;

                case 'slug' : 
                    $col_slug = array("offset" => $i, "name" => "slug");
                    $i++;
                    break;

                case 'short_description' : 
                    $col_short_description = array("offset" => $i, "name" => "short_description");
                    $i++;
                    break;

                case 'description' : 
                    $col_description = array("offset" => $i, "name" => "description");
                    $i++;
                    break;

                case 'available_on' : 
                    $col_available_on = array("offset" => $i, "name" => "available_on");
                    $i++;
                    break;

                case 'created_at' : 
                    $col_created_at = array("offset" => $i, "name" => "created_at");
                    $i++;
                    break;

                case 'updated_at' : 
                    $col_updated_at = array("offset" => $i, "name" => "updated_at");
                    $i++;
                    break;

                case 'deleted_at' : 
                    $col_deleted_at = array("offset" => $i, "name" => "deleted_at");
                    $i++;
                    break;

                case 'variant_selection_method' : 
                    $col_variant_selection_method = array("offset" => $i, "name" => "variant_selection_method");
                    $i++;
                    break;

                case 'option_color' : 
                    $col_option_color = array("offset" => $i, "name" => "option_color");
                    $i++;
                    break;

                case 'option_size' : 
                    $col_option_size = array("offset" => $i, "name" => "option_size");
                    $i++;
                    break;

                case 'categories' : 
                    $col_categories = array("offset" => $i, "name" => "categories");
                    $i++;
                    break;

                default:
                    // $temp[50] = "ASDASD";
                    break;
            }
        }


    	$time_add_to_final1 = microtime(true);

    	unset($this->current_color);
		unset($this->current_size);
		unset($this->current_category);

    	while ($cols = fgetcsv($originCSV)) {
    		@$color_on_1_row = explode(";", $cols[$col_option_color["offset"]]);
            foreach ($color_on_1_row as $key) {
                $final_color[] = array($this->colorID, str_replace("\r\n", "", $key), $cols[$col_id["offset"]]);
                $this->colorID++;
            }

            @$size_on_1_row = explode(";", $cols[$col_option_size["offset"]]);
            foreach ($size_on_1_row as $key) {
                $final_size[] = array($this->sizeID, str_replace("\r\n", "", $key), $cols[$col_id["offset"]]);
                $this->sizeID++;
            }

            @$category_on_1_row = explode(";", $cols[$col_categories["offset"]]);
            // $categories_on_1_row[count($categories_on_1_row)] = str_replace("\r\n", "", $categories_on_1_row[count($categories_on_1_row)]);
            foreach ($category_on_1_row as $key) {
                $final_category[] = array($this->categoryID, str_replace("\r\n", "", $key), $cols[$col_id["offset"]]);
                $this->categoryID++;
            }
		}
        handleCSV::deduplicateInArray($final_color,"color",$this->colorValue);
        handleCSV::deduplicateInArray($final_size,"size",$this->sizeValue);
        handleCSV::deduplicateInArray($final_category,"category");

    	$time_add_to_final2 = microtime(true);
    	$time_to_write1 = microtime(true);

    	$time_to_write2 = microtime(true);

    	fclose($w_size);
		fclose($w_category);
		fclose($w_color);

        echo "<br>time_to_write : ".($time_to_write2-$time_to_write1);
        echo "<br>time_add_to_final : ".($time_add_to_final2-$time_add_to_final1);
    }

    //Tách 1 file csv lớn thành nhiều file nhỏ.
    //Mỗi lần chỉ chạy 1 file nhỏ => tốn ít bộ nhớ hơn.
    //Nhưng hiện tại nếu tách ra để chạy thì bị sai và chậm.
    //ví dụ file lớn là products.csv. 
    // Thì những file nhỏ sẽ có dạng products0.csv, products1.csv ...
    function splitFile() {
        $rOrigin = fopen($this->fileName.".csv","r");
        // $fileSize = filesize($this->fileName.".csv")/1024/1024;

        $firstRow = fgets($rOrigin);

        $i=0;
        $childNumber = 0;
        $wChild = fopen("files/".$this->fileName.$childNumber.".csv","w+");
        fwrite($wChild, $firstRow);
        while($row = fgets($rOrigin)) {
            if($i==$this->lines_per_file) 
            {
                $childNumber++;
                $wChild = fopen("files/".$this->fileName.$childNumber.".csv","w+");
                fwrite($wChild, $firstRow);
                $i=0;
            }
            fwrite($wChild, $row);
            $i++;
        }
        fclose($wChild);
        return $this->number_of_child = $childNumber;
    }

    //Import từng file nhỏ products0.csv, products1.csv ... vào database.
    //Hàm này chỉ import vào bảng products
    public function importProducts() {
    	$time_0 = microtime(true);

    	//Mở file nhỏ
		$fileOpened = fopen($this->fileName.".csv","r");

		//Lấy dòng đầu để biết dc cột nào nằm ở đâu.
        $firstRow = fgetcsv($fileOpened);

        $strFields = "";
        $strSet = "";

        $i=0;
        foreach ($firstRow as $key) {
            $columns[$i][0] = $key;
            $columns[$i][1] = $i;
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

        //Ứng với các cột kiểu datetime thì phải xử lý thêm 1 tí
    	for ($i=0; $i <= 9; $i++) { 
            for ($j=0; $j <= 9; $j++) { 
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

        fclose($fileOpened);

        $strFields = rtrim($strFields,", ");
        $strSet = rtrim($strSet,", ");

        $time_load_products1 = microtime(true);
        $sql ='
            LOAD DATA LOCAL INFILE "'.$this->fileName.".csv".'" 
            REPLACE INTO TABLE product
            FIELDS TERMINATED BY "," 
            LINES TERMINATED BY "\\r\\n"
            ignore 1 lines
            ('.$strFields.')
            set '.$strSet.';';
        mysql_query($sql);


        $time_load_products2 = microtime(true);

        $time_end = microtime(true);

    	echo "<br>time_load_products : ".($time_load_products2-$time_load_products1);
    	echo "<br>Time for child ".$i." : ".($time_end-$time_0);
    }

    //Import từ 2 file temp vô 4 bảng còn lại.
    public function importOthers(){
    	$time_import_others1 = microtime(true);
        $time_import_category1 = microtime(true);
    	$sql = '
            LOAD DATA LOCAL INFILE "files/temp_category.csv" 
            REPLACE INTO TABLE category
            FIELDS TERMINATED BY "," 
            LINES TERMINATED BY "\\r\\n"
            (id,name,@dummy);';
        mysql_query($sql);
        $time_import_category2 = microtime(true);

        $time_import_product_category1 = microtime(true);
        $sql = '
            LOAD DATA LOCAL INFILE "files/temp_category.csv" 
            REPLACE INTO TABLE product_category
            FIELDS TERMINATED BY "," 
            LINES TERMINATED BY "\\r\\n"
            (category_id,@dummy,product_id);';
        mysql_query($sql);
        $time_import_product_category2 = microtime(true);

        $time_import_option_value_size1 = microtime(true);
        $sql = '
            LOAD DATA LOCAL INFILE "files/temp_size.csv" 
            REPLACE INTO TABLE option_value
            FIELDS TERMINATED BY "," 
            LINES TERMINATED BY "\\r\\n"
            (id,value,@dummy,option_id);';
        mysql_query($sql);
        $time_import_option_value_size2 = microtime(true);


        $time_import_product_option_value_size1 = microtime(true);
        $sql = '
            LOAD DATA LOCAL INFILE "files/temp_size.csv" 
            REPLACE INTO TABLE product_option_value
            FIELDS TERMINATED BY "," 
            LINES TERMINATED BY "\\r\\n"
            (option_value_id,@dummy,product_id,@dummy);';
        mysql_query($sql);
        $time_import_product_option_value_size2 = microtime(true);

        $time_import_option_value_color1 = microtime(true);
        $sql = '
            LOAD DATA LOCAL INFILE "files/temp_color.csv" 
            REPLACE INTO TABLE option_value
            FIELDS TERMINATED BY "," 
            LINES TERMINATED BY "\\r\\n"
            (id,value,@dummy,option_id);';
        mysql_query($sql);
        $time_import_option_value_color2 = microtime(true);


        $time_import_product_option_value_color1 = microtime(true);
        $sql = '
            LOAD DATA LOCAL INFILE "files/temp_color.csv" 
            REPLACE INTO TABLE product_option_value
            FIELDS TERMINATED BY "," 
            LINES TERMINATED BY "\\r\\n"
            (option_value_id,@dummy,product_id,@dummy);';
        mysql_query($sql);
        $time_import_product_option_value_color2 = microtime(true);


        $sql = 'insert into `option` (id,name) 
        values ('.$this->sizeValue.',"size"),('.$this->colorValue.',"color")';

        mysql_query($sql);
        echo "<br>time_import_product_option_value_color : ".($time_import_product_option_value_color2-$time_import_product_option_value_color1);
        echo "<br>time_import_option_value_color : ".($time_import_option_value_color2-$time_import_option_value_color1);
        echo "<br>time_import_product_option_value_size : ".($time_import_product_option_value_size2-$time_import_product_option_value_size1);
        echo "<br>time_import_option_value_size : ".($time_import_option_value_size2-$time_import_option_value_size1);
        echo "<br>time_import_product_category : ".($time_import_product_category2-$time_import_product_category1);
        echo "<br>time_import_category : ".($time_import_category2-$time_import_category1);

    	$time_import_others2 = microtime(true);
    	echo "<br>time_import_others : ".($time_import_others2-$time_import_others1);
    }
    //Import file vào database.
    //Thực chất hàm này sẽ gọi hàm import để import từng file nhỏ.
    //Chứ hàm này ko trực tiếp import gì hết.
    public function import_all() {
        $time_all1 = microtime(true);
    	$file_category = fopen("files/temp_category.csv", "w+");
    	fclose($file_category);

        $file_size = fopen("files/temp_size.csv", "w+");
        fclose($file_size);

        $file_color = fopen("files/temp_color.csv", "w+");
        fclose($file_color);

    	$time_split1 = microtime(true);
        $child = $this->splitFile();
    	$time_split2 = microtime(true);

        for ($i=0; $i < $child; $i++) { 
            $this->createChildCSV_p($this->fileName);
        }
    	// $this->createChildCSV_p($this->fileName);

		// $this->importProducts();
  // 		$this->importOthers();
    	$time_all2 = microtime(true);

    	// handleCSV::remove_temp_file();

    	echo "<br>time_split : ".($time_split2-$time_split1);
    	echo "<br>time_all : ".($time_all2-$time_all1);
    }

    public function deduplicateInArray($array,$file,$value=NULL) {
        handleCSV::array_sort_bycolumn($array,1);
        $array_s = array();

    	$w_array_final = fopen("files/temp_".$file.".csv", "a+");

        $pivot_array="";
        foreach($array as $key) {
            
        	if(@($key[1][0].$key[1][1])!=$pivot_array){
        		$pivot_array = ($key[1][0].$key[1][1]);
                //set lại id của những category,color,size trùng nhau cho bằng nhau
        		handleCSV::array_unique_multidimensional($array_s,1,count($array_s));
                // handleCSV::array_delete_repeate($array_s,count($array_s));
        		foreach ($array_s as $key1) {
        			if($value==NULL)
        				fwrite($w_array_final,$key1[0].",".$key1[1].",".$key1[2]."\r\n");
        			else
        				fwrite($w_array_final,$key1[0].",".$key1[1].",".$key1[2].",".$value."\r\n");
        		}
        		unset($array_s);
        		$array_s[] = $key;
        	}
        	else
        	{
        		$array_s[] = $key;
        	}
        }
    }

    //Xóa các dòng giống nhau trong 2 file csv temp.
    public function deduplicateInCSV() {
    	$w_category = fopen("files/temp_category.csv", "r+");
        $w_size = fopen("files/temp_size.csv", "r+");
        $w_color = fopen("files/temp_color.csv", "r+");



        //--------------------finalizing for category-------------------
        $pivot_category="";

        while(!feof($w_category)) {
        	$category_s[] = fgetcsv($w_category);
        }

        fclose($w_category);
        handleCSV::array_sort_bycolumn($category_s,1);


    	$w_category = fopen("files/temp_category.csv", "w+");

        foreach ($category_s as $key) {
        	fwrite($w_category,$key[0].",".$key[1].",".$key[2].","."\r\n");
        }

        unset($category_s);
        fclose($w_category);

    	$w_category = fopen("files/temp_category.csv", "r+");
    	$w_category_final = fopen("files/temp_category_final.csv", "w+");

        while(!feof($w_category)) {
        	$one_category = fgetcsv($w_category);
        	if(@($one_category[1][0].$one_category[1][1])!=$pivot_category){
        		$pivot_category = ($one_category[1][0].$one_category[1][1]);
        		handleCSV::array_unique_multidimensional($category_s,1,count($category_s));
        		foreach ($category_s as $key) {
        			fwrite($w_category_final,$key[0].",".$key[1].",".$key[2]."\r\n");
        		}
        		unset($category_s);
        		$category_s[] = $one_category;
        	}
        	else
        	{
        		$category_s[] = $one_category;
        	}
        }
        unset($category_s);
		//--------------------finalizing for category-------------------

		//--------------------finalizing for color-------------------
		$pivot_color="";

        while(!feof($w_color)) {
        	$color_s[] = fgetcsv($w_color);
        }

        fclose($w_color);
        handleCSV::array_sort_bycolumn($color_s,1);

    	$w_color = fopen("files/temp_color.csv", "w+");

        foreach ($color_s as $key) {
        	fwrite($w_color,$key[0].",".$key[1].",".$key[2].","."\r\n");
        }

        unset($color_s);
        fclose($w_color);

    	$w_color = fopen("files/temp_color.csv", "r+");
    	$w_color_final = fopen("files/temp_color_final.csv", "w+");

        while(!feof($w_color)) {
        	$one_color = fgetcsv($w_color);
        	if(@($one_color[1][0].$one_color[1][1])!=$pivot_color){
        		$pivot_color = ($one_color[1][0].$one_color[1][1]);
        		handleCSV::array_unique_multidimensional($color_s,1,count($color_s));
        		foreach ($color_s as $key) {
        			fwrite($w_color_final,$key[0].",".$key[1].",".$key[2].",".$this->colorValue."\r\n");
        		}
        		unset($color_s);
        		$color_s[] = $one_color;
        	}
        	else
        	{
        		$color_s[] = $one_color;
        	}
        }
        unset($color_s);
		//--------------------finalizing for category-------------------

		//--------------------finalizing for category-------------------
		$pivot_size="";

		while(!feof($w_size)) {
        	$size_s[] = fgetcsv($w_size);
        }

        fclose($w_size);
        handleCSV::array_sort_bycolumn($size_s,1);

    	$w_size = fopen("files/temp_size.csv", "w+");

        foreach ($size_s as $key) {
        	fwrite($w_size,$key[0].",".$key[1].",".$key[2].","."\r\n");
        }

        unset($size_s);
        fclose($w_size);

    	$w_size = fopen("files/temp_size.csv", "r+");
    	$w_size_final = fopen("files/temp_size_final.csv", "w+");

        while(!feof($w_size)) {
        	$one_size = fgetcsv($w_size);
        	if(@($one_size[1][0].$one_size[1][1])!=$pivot_size){
        		$pivot_size = ($one_size[1][0].$one_size[1][1]);
        		handleCSV::array_unique_multidimensional($size_s,1,count($size_s));
        		foreach ($size_s as $key) {
        			fwrite($w_size_final,$key[0].",".$key[1].",".$key[2].",".$this->colorValue."\r\n");
        		}
        		unset($size_s);
        		$size_s[] = $one_size;
        	}
        	else
        	{
        		$size_s[] = $one_size;
        	}
        }
        unset($size_s);
		//--------------------finalizing for category-------------------
    }

    public function remove_temp_file() {
    	$files = glob('files/*'); // get all file names
            foreach($files as $file){ // iterate files
              if(is_file($file))
                unlink($file); // delete file
        }
    }
}