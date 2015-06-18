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
$handle = openCsvFile('clients/Clients.csv');
lineSeek($handle, $_SESSION['import']['position']);

for ($current_line = 0; $line = fgetcsv($handle, MAX_LINE_SIZE, ";"); $current_line++) {
        echo $current_line;
        unset($errors);
        $errors = array();
	$line = utf8_encode_array($line);
	$customer = new Customer ;
        $customer->firstname = pSQL($line[1]);//name
        $customer->lastname = ' ';//name
        $customer->siret = pSQL($line[11]);//siret

        if(Validate::isEmail($line[15])){
            $customer->email = $line[15];
        }
        
        $customer->passwd =$line[15];//siret
        $customer->add();
	$address = new Address();
        $address->id_customer = $customer->id;
        if ($id_country = Country::getIdByName(null, $line[7])){
            $address->id_country = (int) $id_country;
        }
        else
        {
            $country = new Country();
            $country->active = 1;
            $country->name = AdminImportController::createMultiLangField($address->country);
            $country->id_zone = 0; // Default zone for country to create
            $country->iso_code = Tools::strtoupper(Tools::substr($address->country, 0, 2)); // Default iso for country to create
            $country->contains_states = 0; // Default value for country to create
            $lang_field_error = $country->validateFieldsLang(UNFRIENDLY_ERROR, true);
            if (($field_error = $country->validateFields(UNFRIENDLY_ERROR, true)) === true &&
            ($lang_field_error = $country->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $country->add())
            {
                $address->id_country = (int) $country->id;
            }
            else
            {
                $errors[] = sprintf(Tools::displayError('%s cannot be saved'), $country->name[$id_lang]);
                $errors[] = ($field_error !== true ? $field_error : '').(isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '');
            }
        }
        if(count($errors) > 0){
                echo json_encode($errors);
        }else{
            $address->address1 = pSQL($line[3]);
            $address->address2 = pSQL($line[4]);
            $address->phone = pSQL($line[13]);
            $address->city = pSQL($line[6]);
            $address->postcode = pSQL($line[5]);  
            $address->firstname = $customer->firstname ;  
            $address->lastname = ' ';  
            $address->alias = 'My address';  
            $address->add();
        }
        
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

