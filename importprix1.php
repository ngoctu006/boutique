<?php
require(dirname(__FILE__) . '/config/config.inc.php');
require(dirname(__FILE__) . '/init.php');
function shutdown()
{
	global $_;
	$_SESSION['import']['processing'] = false;

	if($_SESSION['import']['finished'] == true){
		$msg = 'execution finie: ' . $_SESSION['import']['position'] . ' lignes traitees';
		echo $msg;
	}else {
		sleep(10);
		$msg = $_SESSION['import']['position'] . ' lignes traitees';
		echo $msg;
		echo '<script>window.location.reload()</script>';
	}
}
register_shutdown_function('shutdown');

function setupSession()
{
	session_start();
	if(!isset($_SESSION['import'])){
		$_SESSION['import'] = array(
				'position' => 0,
				'processing' => false,
				'finished' => false
		);
	}else {
		if($_SESSION['import']['finished'] == true || isset($_GET['force'])){
			$_SESSION['import']['position'] = 0;
			$_SESSION['import']['finished'] = false;
		}
	}
}

function lineSeek($handle, $line){
	fseek($handle, 0);
	for($i =0; $i<=$line; $i++){
		fgets($handle);
	}
}

setupSession();
define('MAX_LINE_SIZE', 0);

//mutex
if($_SESSION['import']['processing']){
	header('Content-Type: application/json');
	echo json_encode(array($_SESSION['import']));
	exit(0);
}else {
	$_SESSION['import']['processing'] = true;
}

@ini_set('display_errors', 'on');
@error_reporting(E_ALL | E_STRICT);
$id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
$handle = openCsvFile('import/Articles.csv');
lineSeek($handle, $_SESSION['import']['position']);

for ($current_line = 0; $line = fgetcsv($handle, MAX_LINE_SIZE, ";"); $current_line++) {
	$line = utf8_encode_array($line);
	$datas = Db::getInstance()->getRow('SELECT p.`id_product`
                FROM `' . _DB_PREFIX_ . 'product` p
                ' . Shop::addSqlAssociation('product', 'p') . '
                WHERE p.`reference` = "' . pSQL($line[0]) . '"
        ');
	if (isset($datas['id_product']) && $datas['id_product'])
		$product = new Product((int) $datas['id_product']);
	else
		$product = new Product();
		
	$product->reference = pSQL($line[0]);
	$product->shop = 1;
	$product->id_shop_default = 1;
	$product->name[$id_lang] = pSQL($line[1]);
	$product->link_rewrite[$id_lang] = Tools::link_rewrite($product->name[$id_lang]);
	
	$product->indexed = 0;
	$product->quantity = (int)$line[6];
	$product->active = (int)$line[8];
	$product->id_tax_rules_group = 55;
	$price = str_replace(',', '.', $line[2]);
//	$product->price = (float)number_format($price / (1 + (float)$line[3] / 100), 6, '.', '');
	$product->price = (float)number_format($price, 6, '.', '');
	
	$tmp = array(
			'reference' => $product->reference,
			'qty' => $line[6],
	);
        if($line[7]){
          $listcategory = explode(',',$line[7]);
          if (count($listcategory) > 2) {
                $flag = true;
                $listcategory = array_unique($listcategory);
                foreach ($listcategory as $category) {
                    if (Category::categoryExists((int) $category) && $flag) {
                        $product->id_category_default = (int) $category;
                        $flag = false;
                    }
                    $product->id_category[] = (int) $category;
                }
            } else {
                if (Category::categoryExists((int) $listcategory[0])) {
                    $product->id_category_default = (int) $listcategory[0];
                    $product->id_category[0] = (int) $listcategory[0];
                }
            }
        }
	  
	if (isset($datas['id_product']) && $datas['id_product']){
		$product->update();
		$tmp['action'] = 'update';
	}
	else{
      
		$product->add();
		$tmp['action'] = 'add new';
	}
        StockAvailable::setQuantity((int)$product->id, 0, $product->quantity, 1);
        /**
         * 
         * 
         * **/
        // add prix shop 2
        $shopOthers = 2 ; // set id shop hard code
        $price2 = str_replace(',', '.', $line[3]);
        $price2 = (float)number_format($price2, 6, '.', '');
        $product_shop['id_product'] = $product->id;
        $product_shop['id_shop'] = $shopOthers;
        $product_shop['id_category_default'] = $product->id_category_default;
        $product_shop['price'] = $price2;
        $product_shop['active'] = $product->active;
        $product_shop['id_tax_rules_group'] = 55;
      
        Db::getInstance()->insert('product_shop', $product_shop);
        StockAvailable::setQuantity((int)$product->id, 0, $product->quantity, $shopOthers);
        echo json_encode(array($tmp));
}

function getIdAttributeByReference($reference){
	if (empty($reference))
		return 0;

	$query = new DbQuery();
	$query->select('pa.id_product_attribute');
	$query->from('product_attribute', 'pa');
	$query->where('pa.reference LIKE \'%'.pSQL($reference).'%\'');

	return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
}

function openCsvFile($fileName) {
	$handle = false;
	if (is_file($fileName) && is_readable($fileName))
		$handle = fopen($fileName, 'r');

	if (!$handle)
		die('le fichier '.$fileName.' est introuvable');

	rewindBomAware($handle);

	return $handle;
}

function closeCsvFile($handle) {
	fclose($handle);
}

function rewindBomAware($handle) {
	// A rewind wrapper that skips BOM signature wrongly
	if (!is_resource($handle))
		return false;
	rewind($handle);
	if (($bom = fread($handle, 3)) != "\xEF\xBB\xBF")
		rewind($handle);
}

function getPrice($field) {
	$field = ((float) str_replace(',', '.', $field));
	$field = ((float) str_replace('%', '', $field));
	return $field;
}

function utf8_encode_array($array)
{
	if (is_array($array))
		foreach ($array as $key => $value)
			$array[$key] = utf8_encode($value);
		else
			$array = utf8_encode($array);

		return $array;
}

//job complete
$_SESSION['import']['finished'] = true;
$_SESSION['import']['processing'] = false;

