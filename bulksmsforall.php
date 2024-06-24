<?php if (!defined('_PS_VERSION_'))
    exit;
if (!function_exists('curl_version'))
    exit;
require_once(_PS_MODULE_DIR_. 'bulksmsforall/classes/crystalapi.php');
class bulksmsforall extends Module
{
    private $tabs_array;
    private $hooks_array = array(
        array('name' => 'actionCustomerAccountAdd'),
        array('name' => 'actionValidateOrder'),
        array('name' => 'actionOrderStatusUpdate'),
        array('name' => 'actionAdminOrdersTrackingNumberUpdate'),
        array('name' => 'displayAdminOrder'),
        array('name' => 'bulksmsContactForm', 'custom' => true, 'title' => 'Post submit of contact form',
            'description' => 'This hook is called when a message is sent from contact form')
    );

    public function __construct()
    {
        $this->name = 'bulksmsforall';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'CrystalSoftware';
        $this->need_instance = 1;

        parent::__construct();

        $this->displayName = $this->l('Bulk SMS Notification Module For All Companies');

        $config = Configuration::getMultiple(array('BULKSMS_FORALL_USERKEY', 'BULKSMS_FORALL_PASSKEY', 'BULKSMS_FORALL_URL', 'BULKSMS_FORALL_HPADMIN'));
        if (!isset($config['BULKSMS_FORALL_USERKEY']) || !isset($config['BULKSMS_FORALL_PASSKEY'])
            || !isset($config['BULKSMS_FORALL_URL']) || !isset($config['BULKSMS_FORALL_HPADMIN']))
            $this->warning = $this->l('Not found any API information.');
        $this->description = $this->l('You can integrate for ALL BULK SMS COMPANIES. It can work any SMS api.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete this module?');

        $this->tabs_array = array(
            'AdminAccountSettings'			=> 'API Settings',
            'AdminSmsSettings'	=> 'Notification(SMS) Settings',
            'AdminSendMessage' 		=> 'Send SMS',
            'AdminpssmsHistorys' 		=> 'SMS History',
            'AdminpssmsBuy' 		=> 'Credit Check',
        );
    }

    public function install()
    {

        if (!parent::install() || !$this->createDB() || !$this->createMenu() || !$this->createHooks())
            return false;

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() || !$this->dropDB() || !$this->removeMenu() || !$this->removeSetting() || !$this->removeHooks())
            return false;
        return true;
    }

    private function createDB()
    {
        Db::getInstance()->Execute(
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bulksms_history` (
				  `id_bulksms_history` int(10) unsigned NOT NULL auto_increment,
				  `recipient` varchar(100) NOT NULL,
				  `phone` varchar(16) NOT NULL,
				  `event` varchar(64) NOT NULL,
				  `message` text NOT NULL,
				  `status` tinyint(1) NOT NULL default \'0\',
				  `error` varchar(255) default NULL,
				  `date_add` datetime NOT NULL,
				  PRIMARY KEY  (`id_bulksms_history`)
				) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;'
        );

        Db::getInstance()->Execute(
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bulksms_phone_prefix` (
				`iso_code` varchar(3) NOT NULL,
				`prefix` int(10) unsigned default NULL,
				PRIMARY KEY  (`iso_code`)
				) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;'
        );

        Db::getInstance()->Execute('
				INSERT INTO `'._DB_PREFIX_."bulksms_phone_prefix` (`iso_code`, `prefix`) VALUES
					('AD', 376),('AE', 971),('AF', 93),('AG', 1268),('AI', 1264),('AL', 355),('AM', 374),('AN', 599),('AO', 244),
					('AQ', 672),('AR', 54),('AS', 1684),('AT', 43),('AU', 61),('AW', 297),('AX', NULL),('AZ', 994),('BA', 387),
					('BB', 1246),('BD', 880),('BE', 32),('BF', 226),('BG', 359),('BH', 973),('BI', 257),('BJ', 229),('BL', 590),('BM', 1441),
					('BN', 673),('BO', 591),('BR', 55),('BS', 1242),('BT', 975),('BV', NULL),('BW', 267),('BY', 375),('BZ', 501),
					('CA', 1),('CC', 61),('CD', 242),('CF', 236),('CG', 243),('CH', 41),('CI', 225),('CK', 682),('CL', 56),('CM', 237),
					('CN', 86),('CO', 57),('CR', 506),('CU', 53),('CV', 238),('CX', 61),('CY', 357),('CZ', 420),('DE', 49),('DJ', 253),
					('DK', 45),('DM', 1767),('DO', 1809),('DZ', 213),('EC', 593),('EE', 372),('EG', 20),('EH', NULL),('ER', 291),('ES', 34),
					('ET', 251),('FI', 358),('FJ', 679),('FK', 500),('FM', 691),('FO', 298),('FR', 33),('GA', 241),('GB', 44),('GD', 1473),
					('GE', 995),('GF', 594),('GG', NULL),('GH', 233),('GI', 350),('GL', 299),('GM', 220),('GN', 224),('GP', 590),('GQ', 240),
					('GR', 30),('GS', NULL),('GT', 502),('GU', 1671),('GW', 245),('GY', 592),('HK', 852),('HM', NULL),('HN', 504),('HR', 385),
					('HT', 509),('HU', 36),('ID', 62),('IE', 353),('IL', 972),('IM', 44),('IN', 91),('IO', 1284),('IQ', 964),('IR', 98),
					('IS', 354),('IT', 39),('JE', 44),('JM', 1876),('JO', 962),('JP', 81),('KE', 254),('KG', 996),('KH', 855),('KI', 686),
					('KM', 269),('KN', 1869),('KP', 850),('KR', 82),('KW', 965),('KY', 1345),('KZ', 7),('LA', 856),('LB', 961),('LC', 1758),
					('LI', 423),('LK', 94),('LR', 231),('LS', 266),('LT', 370),('LU', 352),('LV', 371),('LY', 218),('MA', 212),('MC', 377),
					('MD', 373),('ME', 382),('MF', 1599),('MG', 261),('MH', 692),('MK', 389),('ML', 223),('MM', 95),('MN', 976),('MO', 853),
					('MP', 1670),('MQ', 596),('MR', 222),('MS', 1664),('MT', 356),('MU', 230),('MV', 960),('MW', 265),('MX', 52),('MY', 60),
					('MZ', 258),('NA', 264),('NC', 687),('NE', 227),('NF', 672),('NG', 234),('NI', 505),('NL', 31),('NO', 47),('NP', 977),
					('NR', 674),('NU', 683),('NZ', 64),('OM', 968),('PA', 507),('PE', 51),('PF', 689),('PG', 675),('PH', 63),('PK', 92),
					('PL', 48),('PM', 508),('PN', 870),('PR', 1),('PS', NULL),('PT', 351),('PW', 680),('PY', 595),('QA', 974),('RE', 262),
					('RO', 40),('RS', 381),('RU', 7),('RW', 250),('SA', 966),('SB', 677),('SC', 248),('SD', 249),('SE', 46),('SG', 65),
					('SI', 386),('SJ', NULL),('SK', 421),('SL', 232),('SM', 378),('SN', 221),('SO', 252),('SR', 597),('ST', 239),('SV', 503),
					('SY', 963),('SZ', 268),('TC', 1649),('TD', 235),('TF', NULL),('TG', 228),('TH', 66),('TJ', 992),('TK', 690),('TL', 670),
					('TM', 993),('TN', 216),('TO', 676),('TR', 90),('TT', 1868),('TV', 688),('TW', 886),('TZ', 255),('UA', 380),('UG', 256),
					('US', 1),('UY', 598),('UZ', 998),('VA', 379),('VC', 1784),('VE', 58),('VG', 1284),('VI', 1340),('VN', 84),('VU', 678),
					('WF', 681),('WS', 685),('YE', 967),('YT', 262),('ZA', 27),('ZM', 260),('ZW', 263);"
        );
        return true;
    }

    private function dropDB()
    {
        Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'bulksms_phone_prefix`');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'bulksms_history`');
        return true;
    }

    private function createMenu()
    {
        $tab = new Tab();
        foreach (Language::getLanguages() as $language)
            $tab->name[$language['id_lang']] = 'SMS';

        $tab->class_name = 'envelope';
        $tab->module = $this->name;
        $tab->id_parent = 0;
        if (!$tab->save())
            return false;
        else
        {
            $id_tab = $tab->id;

            //$id_en = Language::getIdByIso('en');
            foreach ($this->tabs_array as $tab_key => $name)
            {
                $tab = new Tab();
                foreach (Language::getLanguages() as $language)
                {
                    //$tmp = $this->l2($name, (int)$language['id_lang']);
                    //$tab->name[$language['id_lang']] = isset($tmp) && !empty($tmp) ? $tmp : $this->l2($name, $id_en);
                    $tab->name[$language['id_lang']] = $name;
                }
                $tab->class_name = $tab_key;
                $tab->module = $this->name;
                $tab->id_parent = $id_tab;
                if (!$tab->save())
                    return false;
            }
        }
        return true;
    }

    private function removeMenu()
    {
        foreach ($this->tabs_array as $tab_key => $name)
        {
            $name;
            $id_tab = Tab::getIdFromClassName($tab_key);
            if ($id_tab != 0)
            {
                $tab = new Tab($id_tab);
                $tab->delete();
            }
        }

        $id_tab = Tab::getIdFromClassName('envelope');
        if ($id_tab != 0)
        {
            $tab = new Tab($id_tab);
            $tab->delete();
        }
        return true;
    }

    private function removeSetting()
    {
        Db::getInstance()->Execute('
				DELETE FROM `'._DB_PREFIX_.'configuration`
				WHERE `name` like \'BULKSMS_%\'');
        return true;
    }

    private function createHooks()
    {
        foreach ($this->hooks_array as $hook)
        {

            if (!$this->registerHook($hook['name']))
                return false;
        }
        return true;
    }

    private function removeHooks()
    {
        return Db::getInstance()->Execute('
				DELETE FROM `'._DB_PREFIX_.'hook`
				WHERE `name` like \'bulksms%\'');

    }

    public function hookActionCustomerAccountAdd($params)
    {
        $text = Configuration::get('BULKSMS_ZREGISTER_ALERT_ADMIN');
        $text_to_customer = Configuration::get('BULKSMS_ZREGISTER_ALERT_CUST');
        $host = 'http://'.Tools::getHttpHost(false, true);

        $values = array(
            '{firstname}' => $params['newCustomer']->firstname,
            '{lastname}' => $params['newCustomer']->lastname,
            '{email}' => $params['newCustomer']->email,
            '{password}' => Tools::getValue('passwd'),
            '{shopname}' => Configuration::get('PS_SHOP_NAME'),
            '{shopurl}' => $host.__PS_BASE_URI__
        );

        if (!empty($text_to_customer))
        {
            if (Configuration::get('PS_REGISTRATION_PROCESS_TYPE'))
            {
                $text_to_customer = str_replace(array_keys($values), array_values($values), $text_to_customer);
                $dest = pssmsapi::getPhone(Address::getFirstCustomerAddressId($params['newCustomer']->id), false);
                pssmsapi::sendMessage($dest, $text_to_customer, $params);
            }
        }

        if (!empty($text))
        {
            $text = str_replace(array_keys($values), array_values($values), $text);
            $dest = explode(',', Configuration::get('BULKSMS_FORALL_HPADMIN'));
            foreach ($dest as $destination)
                pssmsapi::sendMessage($destination, $text, $params);
        }
    }
    public function hookDisplayAdminOrder($params)
    {


        $order = new Order((int)$params['id_order']);
        $delivery = new Address((int)$order->id_address_delivery);
        //$invoice = new Address((int)$order->id_address_invoice);
        //$url = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/bulksmsforall/sendsms.php';
        $statuz = 1;
      $text = Tools::getValue('smsmessage');
        if(Tools::getValue('sendsms') == 1){
            if(empty(Tools::getValue('smsmessage'))){
                $errmessage = $this->l('Message cant be empty');
                $statuz = 0;
            }
            if($statuz == 1){
                $destination = pssmsapi::getPhone($delivery->id, false);
                pssmsapi::sendMessage($destination, $text, $params);
                $errmessage = $this->l('SMS Sent!');
                $text = '';
            }
        }else{
            $statuz = -1;
        }
        $idcountry = $delivery->id_country;
        $countryz=new Country($idcountry);

        $getthephone = $delivery->phone_mobile;
        if($getthephone == ''){
            $getthephone = $delivery->phone;
        }
        $getthephone = str_replace(" ", "", $getthephone);
        $getthephonefordelivery = $getthephone;
        $getthephonefirst = substr($getthephone, 0, 1);
        if($getthephonefirst == '+'){
            $getthephone = substr($getthephone, 1);
            $getthephonefordelivery = substr($getthephonefordelivery, 1);
        }
        $getthephonefirst = substr($getthephone, 0, 1);
        if($getthephonefirst == '0'){
            $getthephone = substr($getthephone, 1);
            $getthephonefordelivery = substr($getthephonefordelivery, 1);
        }
        if(substr($getthephone, 0, strlen($countryz->call_prefix)) === $countryz->call_prefix){

            $getthephonefordelivery = substr($getthephonefordelivery, strlen($countryz->call_prefix));
        }else{
            $getthephone = $countryz->call_prefix.$getthephone;
        }
        if(empty($delivery->mahalle)){ $mah = ''; }else
        { $mah = $delivery->mahalle.' Mah. '; }
        if(empty($delivery->cadde)){ $cad = ''; }else
        {$cad = $delivery->cadde.' Cad. '; }
        if(empty($delivery->sokak)){ $sok = ''; }else
        {$sok = $delivery->sokak.' Sk. '; }
        $statez=new State($delivery->id_state);
        $adresal = $mah.$cad.$sok.$delivery->address1.' '.$delivery->address2.' '.$delivery->postcode.' '.$delivery->city.' '.$statez->name;

        $this->smarty->assign(array(
            'delivery_fulladres' => $adresal,
            'delivery_company' => $delivery->company,
            'delivery_firstname' => $delivery->firstname,
            'delivery_lastname' => $delivery->lastname,
            'delivery_address1' => $delivery->address1,
            'delivery_address2' => $delivery->address2,
            'delivery_city' => $delivery->city,
            'delivery_postal_code' => $delivery->postcode,
            'delivery_country' => $delivery->country,
            'delivery_state' => $delivery->id_state ? $delivery->name : '',
            'delivery_phone' => $getthephonefordelivery,
            'waphone' => $getthephone,
            'delivery_other' => $delivery->other,
            'ip' => (string) Tools::getRemoteAddr(),
            'id_order' => $params['id_order'],
            'status' => $statuz,
            'error' => $errmessage,
            'message' => $text
        ));
        //return 0;
        return $this->display(__FILE__, 'order_detail.tpl');
        //return print_r($params);
    }

    public function hookActionValidateOrder($params)
    {

        $text = Configuration::get('BULKSMS_ZORDER_ALERT_CUST');
        $texttoadmin = Configuration::get('BULKSMS_ZORDER_ALERT_ADMIN');

        $host = 'http://'.Tools::getHttpHost(false, true);
        $id_lang = (int)Context::getContext()->language->id;
        $currency = $params['currency'];
        $order = $params['order'];
        $customer = $params['customer'];
        $delivery = new Address((int)$order->id_address_delivery);
        $invoice = new Address((int)$order->id_address_invoice);
        $order_date_text = Tools::displayDate($order->date_add, (int)$id_lang);
        $carrier = new Carrier((int)$order->id_carrier);
        $message = $order->getFirstMessage();

        $items = '';
        $products = $params['order']->getProducts();
        $customized_datas = Product::getAllCustomizedDatas((int)$params['cart']->id);
        Product::addCustomizationPrice($products, $customized_datas);
        foreach ($products as $key => $product)
        {
            $key;
            $unit_price = $product['product_price_wt'];
            //$ref = $product['product_reference'];

            $customization_text = '';
            if (isset($customized_datas[$product['product_id']][$product['product_attribute_id']]))
            {
                foreach ($customized_datas[$product['product_id']][$product['product_attribute_id']] as $customization)
                {
                    if (isset($customization['datas'][_CUSTOMIZE_TEXTFIELD_]))
                        foreach ($customization['datas'][_CUSTOMIZE_TEXTFIELD_] as $text)
                            $customization_text .= $text['name'].': '.$text['value'].'\n';
                }

                $customization_text = rtrim($customization_text, '\n');
            }

            $items .= (int)$product['product_quantity'].'x '.$product['product_name'].(isset($product['attributes_small']) ? ' '.
                    $product['attributes_small'] : '').(!empty($customization_text) ? '<br />'.$customization_text : '').
                ' ('.Tools::displayPrice($unit_price, $currency, false).') = '.
                Tools::displayPrice(($unit_price * $product['product_quantity']), $currency, false).'\n';

        }

        $values = array(
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{email}' => $customer->email,
            '{delivery_company}' => $delivery->company,
            '{delivery_firstname}' => $delivery->firstname,
            '{delivery_lastname}' => $delivery->lastname,
            '{delivery_address1}' => $delivery->address1,
            '{delivery_address2}' => $delivery->address2,
            '{delivery_city}' => $delivery->city,
            '{delivery_postal_code}' => $delivery->postcode,
            '{delivery_country}' => $delivery->country,
            '{delivery_state}' => $delivery->id_state ? $delivery->name : '',
            '{delivery_phone}' => $delivery->phone_mobile,
            '{delivery_other}' => $delivery->other,
            '{invoice_company}' => $invoice->company,
            '{invoice_firstname}' => $invoice->firstname,
            '{invoice_lastname}' => $invoice->lastname,
            '{invoice_address2}' => $invoice->address2,
            '{invoice_address1}' => $invoice->address1,
            '{invoice_city}' => $invoice->city,
            '{invoice_postal_code}' => $invoice->postcode,
            '{invoice_country}' => $invoice->country,
            '{invoice_state}' => $invoice->id_state ? $invoice->name : '',
            '{invoice_phone}' => $invoice->phone_mobile,
            '{invoice_other}' => $invoice->other,
            '{order_name}' => sprintf('%06d', $order->id),
            '{date}' => $order_date_text,
            '{carrier}' => (($carrier->name == '0') ? Configuration::get('PS_SHOP_NAME') : $carrier->name),
            '{ref}' => $order->reference,
            '{payment}' => Tools::substr($order->payment, 0, 32),
            '{items}' => $items,
            '{total_paid}' => Tools::displayPrice($order->total_paid, $currency),
            '{total_products}' => Tools::displayPrice($order->getTotalProductsWithTaxes(), $currency),
            '{total_discounts}' => Tools::displayPrice($order->total_discounts, $currency),
            '{total_shipping}' => Tools::displayPrice($order->total_shipping, $currency),
            '{total_wrapping}' => Tools::displayPrice($order->total_wrapping, $currency),
            '{currency}' => $currency->sign,
            '{message}' => $message,
            '{shopname}' => Configuration::get('PS_SHOP_NAME'),
            '{shopurl}' => $host.__PS_BASE_URI__
        );

        if (!empty($text))
        {
            $text = pssmsapi::replaceUnwanted(str_replace(array_keys($values), array_values($values), $text));
           // $destination = pssmsapi::getPhone(Address::getFirstCustomerAddressId($customer->id), false);
            $destination = pssmsapi::getPhone($delivery->id, false);
            pssmsapi::sendMessage($destination, $text, $params);
        }

        if (!empty($texttoadmin))
        {
            $texttoadmin = pssmsapi::replaceUnwanted(str_replace(array_keys($values), array_values($values), $texttoadmin));
            $dest = explode(',', Configuration::get('BULKSMS_FORALL_HPADMIN'));
            foreach ($dest as $destination)
                pssmsapi::sendMessage($destination, $texttoadmin, $params);
        }
    }

    public function hookActionOrderStatusUpdate($params)
    {
        //
        $text = Configuration::get('BULKSMS_ZSTATUSO_ALERT_CUST-'.$params['newOrderStatus']->id);
        if (!empty($text))
        {
            $order = new Order((int)$params['id_order']);
            $current_order_state = $order->getCurrentOrderState();


            if ($current_order_state)
            {
                $state = $params['newOrderStatus']->name;
                $customer = new Customer((int)$order->id_customer);
                $carrier = new Carrier((int)$order->id_carrier);
                $delivery = new Address((int)$order->id_address_delivery);
                $values = array(
                    '{firstname}' => $customer->firstname,
                    '{lastname}' => $customer->lastname,
                    '{ref}' => $order->reference,
                    '{tracking_number}' => $order->shipping_number,
                    '{carrier}' => (($carrier->name == '0') ? Configuration::get('PS_SHOP_NAME') : $carrier->name),
                    '{carrier_url}' => $carrier->url,
                    '{order_id}' => sprintf('%06d', $order->id),
                    '{order_state}' => $state,
                    '{shopname}' => Configuration::get('PS_SHOP_NAME')
                );
                $text = pssmsapi::replaceUnwanted(str_replace(array_keys($values), array_values($values), $text));
                //$destination = pssmsapi::getPhone(Address::getFirstCustomerAddressId($customer->id), false);
                $destination = pssmsapi::getPhone($delivery->id, false);
                pssmsapi::sendMessage($destination, $text, $params);
            }
        }
    }

    public function hookActionAdminOrdersTrackingNumberUpdate($params)
    {

        $text = Configuration::get('BULKSMS_ZTRACKING_ALERT_CUST');
        $order = $params['order'];
        $customer = new Customer((int)$order->id_customer);
        //$currency = new Currency($order->id_currency);
        $carrier = new Carrier((int)$order->id_carrier);

        $delivery = new Address((int)$order->id_address_delivery);
        $values = array(
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{tracking_number}' => $order->shipping_number,
            '{carrier}' => (($carrier->name == '0') ? Configuration::get('PS_SHOP_NAME') : $carrier->name),
            '{carrier_url}' => $carrier->url,
            '{ref}' => $order->reference,
            '{order_id}' => sprintf('%06d', $order->id),
            '{shopname}' => Configuration::get('PS_SHOP_NAME')
        );

        $text = pssmsapi::replaceUnwanted(str_replace(array_keys($values), array_values($values), $text));
        //$destination = pssmsapi::getPhone(Address::getFirstCustomerAddressId($customer->id), false);
        $destination = pssmsapi::getPhone($delivery->id, false);
        pssmsapi::sendMessage($destination, $text, $params);
    }

    public function hookBulksmsContactForm($params)
    {

        $host = 'http://'.Tools::getHttpHost(false, true);
        $text = Configuration::get('BULKSMS_ZCONTACT_ALERT_ADMIN');
        $texttocust = Configuration::get('BULKSMS_ZCONTACT_ALERT_CUST');

        $values = array(
            '{contact_subject}' => $params['contact_name'],
            '{subject_mail}' => $params['contact_mail'],
            '{from}' => $params['from'],
            '{shopname}' => Configuration::get('PS_SHOP_NAME'),
            '{shopurl}' => $host.__PS_BASE_URI__
        );

        if (!empty($text))
        {
            $text = pssmsapi::replaceUnwanted(str_replace(array_keys($values), array_values($values), $text));
            $dest = explode(',', Configuration::get('BULKSMS_FORALL_HPADMIN'));

            foreach ($dest as $destination)
            {
                if (!empty($destination))
                    pssmsapi::sendMessage($destination, $text, $params);
            }
        }

        if (!empty($texttocust))
        {
            $customer = $params['customer'];
            $texttocust = pssmsapi::replaceUnwanted(str_replace(array_keys($values), array_values($values), $texttocust));

            $destination = pssmsapi::getPhone(Address::getFirstCustomerAddressId($customer->id), false);
            if (!empty($destination))
                pssmsapi::sendMessage($destination, $texttocust, $params);
        }
    }
}
?>