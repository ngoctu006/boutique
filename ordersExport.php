<?php
require(dirname(__FILE__).'/config/config.inc.php');
require(dirname(__FILE__).'/init.php');
//@ini_set('display_errors', 'on');

@error_reporting(E_ALL | E_STRICT);

if (!defined('_SHOP_EXPORT'))
    define('_SHOP_EXPORT', Configuration::get('PS_SHOP_DEFAULT'));//get id default shop

if (!defined('_EXPORT_FILE_NAME_'))
    define('_EXPORT_FILE_NAME_', 'Commandes.csv');

if (!defined('_EXPORT_FILE_LINK_'))
    define('_EXPORT_FILE_LINK_', _PS_BASE_URL_.__PS_BASE_URI__.'script/'._EXPORT_FILE_NAME_);

if (!defined('_LOG_FILE_NAME_'))
    define('_LOG_FILE_NAME_', 'logExport.txt');

if (!defined('_AR_FILE_NAME_'))
    define('_AR_FILE_NAME_', 'orders_transfered.txt');

//ajouter l'accusé reception à log
if($arArray = file(_AR_FILE_NAME_)){
    $handle = fopen(_LOG_FILE_NAME_, 'a');
    foreach ($arArray as $idOrder){        
        if($id = intval($idOrder)){
            fwrite($handle, $id."\n");
        }
    }
    fclose($handle);
}

$idsArray = array();
if($logFile = fopen(_LOG_FILE_NAME_, 'c+')){
    $idsArray = file(_LOG_FILE_NAME_);
    fclose($logFile);
}

$context = Context::getContext();
$id_lang = $context->language->id;


$delimiter = ";";
if(count($idsArray)){
    $idsString = implode(",", $idsArray);
    $idsOrder = Db::getInstance()->executeS(
                "SELECT id_order 
                 FROM "._DB_PREFIX_."orders 
                 WHERE id_order NOT IN (".$idsString.") And id_shop = ".(int)_SHOP_EXPORT."");
}else{
    $idsOrder = Db::getInstance()->executeS(
            "SELECT id_order    
             FROM "._DB_PREFIX_."orders
             WHERE id_shop = ".(int)_SHOP_EXPORT."");
}
//            die(var_dump(OrderDetail::getList($idsOrder[0])));
if(count($idsOrder)){
    ob_clean();

        $header = array("ID"," Shop Name ", "Référence", "Date", "Total", "Réductions", "Taux de Tva sur Réductions", "Paquet cadeau", "Taux de Tva sur Paquet cadeau", "État",
            "Frais de port", "Taux de Tva sur Frais de port",
            "Mode de transport", "Mode de paiement", "Référence article","Produit", "Qte", "Prix", "Taux de Tva", "Client", "Email",
            "ADRESSE DE FACTURATION : nom", "prenom", "Raison sociale", "addresse 1", "addresse 2", "code postal", "ville", "Pays", "Téléphone", "Tel mobile",
            "ADRESSE DE LIVRAISON: nom", "prenom", "Raison sociale", "addresse 1", "addresse 2", "code postal", "ville", "Pays", "Téléphone", "Tel mobile");
        
        //echo header
        echo iconv("UTF-8", "Windows-1252", implode($delimiter, $header))."\r\n";

        $i = 0;
        $addToLogArray = array();

        foreach ($idsOrder as $id){
            $order = new Order((int)$id['id_order']);    
            $orderState = $order->getCurrentOrderState();
            $customer = new Customer((int)$order->id_customer);
            
            $addressDelivery = new Address((int) $order->id_address_delivery);
            $addressInvoice = new Address((int) $order->id_address_invoice);
            $shippingArray = $order->getShipping();
            $paymentCollection = array();
            foreach ($order->getOrderPaymentCollection() as $payment){
                $paymentCollection[] = $payment->payment_method;
            }
            $tax = 100 * ($order->total_paid_tax_incl - $order->total_paid_tax_excl) / $order->total_paid_tax_excl;
            //Order Detail info

            $shop = Shop::getShop($order->id_shop);
            $shopName = $shop['name'];
            foreach ($order->getOrderDetailList() as $product){
                $line = array();
                //Order infos
                $line['id'] = $order->id;
                $line['shop_name'] = $shopName;
                $line['reference'] = $order->reference;
                $line['date_add'] = $order->date_add;
                $line['total'] = Tools::ps_round($order->total_paid, 2);
                $line['total_discounts'] = Tools::ps_round($order->total_discounts_tax_incl, 2);
                $line['discounts_tva'] = Tools::ps_round($tax, 1);
                $line['total_wrapping'] = Tools::ps_round($order->total_wrapping_tax_incl, 2);
                $line['wrapping_tva'] = Tools::ps_round($tax, 1);
                $line['state'] = $orderState->name[$id_lang];
                
                $line['total_shipping'] = Tools::ps_round($order->total_shipping, 2);
                $line['carrier_tax_rate'] = Tools::ps_round($order->carrier_tax_rate, 2);
                $line['carrier_name'] = $shippingArray[0]['carrier_name'];
                $line['payment_method'] = (count($paymentCollection)) ? implode(" ", $paymentCollection) : $order->payment;

                //Product infos
                $line['product_reference'] = $product['product_reference'];
                $line['product'] = $product['product_name'];
                $line['qte'] = $product['product_quantity'];
                $line['product_price'] = Tools::ps_round($product['total_price_tax_incl'], 2);
                $line['product_tax_rate'] = Tools::ps_round($tax, 1);

                //Customer infos
                $line['customer'] = (int)$customer->id;
                $line['email'] = $customer->email;
                
                $line['addressInvoiceName'] = $addressInvoice->lastname;
                $line['addressInvoiceFirstname'] = $addressInvoice->firstname;
                $line['addressInvoicecompany'] = $addressInvoice->company;
                $line['addressInvoiceaddress1'] = $addressInvoice->address1;
                $line['addressInvoiceaddress2'] = $addressInvoice->address2;
                $line['addressInvoicepostcode'] = $addressInvoice->postcode;
                $line['addressInvoicecity'] = $addressInvoice->city;
                $line['addressInvoicecountry'] = $addressInvoice->country;
                $line['addressInvoicephone'] = $addressInvoice->phone;
                $line['addressInvoicephone_mobile'] = $addressInvoice->phone_mobile;
                
                $line['addressDeliveryName'] = $addressDelivery->lastname;
                $line['addressDeliveryFirstname'] = $addressDelivery->firstname;
                $line['addressDeliverycompany'] = $addressDelivery->company;
                $line['addressDeliveryaddress1'] = $addressDelivery->address1;
                $line['addressDeliveryaddress2'] = $addressDelivery->address2;
                $line['addressDeliverypostcode'] = $addressDelivery->postcode;
                $line['addressDeliverycity'] = $addressDelivery->city;
                $line['addressDeliverycountry'] = $addressDelivery->country;
                $line['addressDeliveryphone'] = $addressDelivery->phone;
                $line['addressDeliveryphone_mobile'] = $addressDelivery->phone_mobile;

                //echo line
                //echo iconv("UTF-8", "Windows-1252", implode($delimiter, $line))."\r\n";
				//edit by: orchid.fine
				$line['messages'] = '';
                $messages = CustomerMessage::getMessagesByOrderId((int)($order->id), false);
                if($messages)
	                foreach($messages as $message){
	                	$line['messages'] .= str_replace('<br />', '¤', nl2br($message['message']));
	                }
                $line['messages'] = str_replace(array("\r\n","\n","\r","\t"), '', $line['messages']);
                echo html_entity_decode(htmlentities(implode($delimiter, $line), ENT_NOQUOTES, "UTF-8"), ENT_NOQUOTES, "Windows-1252")."\r\n";
            }
            
            //
            unset($order);
            unset($orderState);
            unset($addressInvoice);
            unset($addressDelivery);
            unset($customer);
        }

}


