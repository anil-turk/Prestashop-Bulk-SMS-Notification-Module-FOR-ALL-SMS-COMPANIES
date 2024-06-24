<?php
class pssmsapi extends ObjectModel
{

    public static function replaceUnwanted($txt)
    {
        $search  = array('RP?', 'Rp?', 'rp?', 'rP?');
        $replace = array('Rp', 'Rp', 'Rp', 'Rp');
        return str_replace($search, $replace, $txt);
    }
    public static function getPhone($address_id, $is_admin)
    {
        $phone = '';
        if ($is_admin)
            $phone = Configuration::get('BULKSMS_FORALL_HPADMIN');
        else if (!empty($address_id))
        {
            $address = new Address($address_id);
            if (!empty($address->phone_mobile) && !empty($address->id_country))
                $phone = $address->phone_mobile;
        }
        return $phone;
    }

    private static function convertPhoneToInternational($phone, $id_country)
    {
        $phone = preg_replace('/[^+0-9]/', '', $phone);
        $iso = Country::getIsoById($id_country);

        $result = Db::getInstance()->getRow('SELECT prefix FROM `'._DB_PREFIX_."bulksms_phone_prefix` WHERE `iso_code` = '".$iso."'");
        $prefix = $result['prefix'];
        if (empty($prefix))
            return null;
        else
        {
            if (Tools::substr($phone, 0, 1) == '+')
                return Tools::substr($phone, 1, Tools::strlen($phone));
            else if (Tools::substr($phone, 0, 2) == '00')
            {
                $phone = Tools::substr($phone, 2);
                if (strpos($phone, $prefix) === 0)
                    return $phone;
                else
                    return null;
            }
            else if (Tools::substr($phone, 0, 1) == '0')
                return $prefix.Tools::substr($phone, 1);
            else if (strpos($phone, $prefix) === 0)
                return $phone;
            else
                return $prefix.$phone;
        }
    }

    private static function toUnicode($text)
    {
        $backslash = '\ ';
        $backslash = trim($backslash);
        $uni_code = Array
        (
            'i' => 'i',
            'İ' => 'I',
            'ü' => 'u',
            'Ü' => 'U',
            'ç' => 'c',
            'Ç' => 'C',
            'ö' => 'o',
            'Ö' => 'O',
            'ş' => 's',
            'Ş' => 'S',
            'ğ' => 'g',
            'Ğ' => 'G',
            'ö' => 'o',
            'Ö' => 'O',
            'ı' => 'i',
        );

        $result = '';
        $str_len = Tools::strlen($text);
        for ($i = 0; $i < $str_len; $i++)
        {

            $currect_char = Tools::substr($text, $i, 1);

            if (array_key_exists($currect_char, $uni_code))
            {$result .= $uni_code[$currect_char]; } else {
                $result .= $currect_char;
            }

        }

        return $result;

    }

    private static function hexChars($data)
    {
        $mb_hex = '';
        for ($i = 0; $i < mb_strlen($data, 'UTF-8'); $i++)
        {
            $c = mb_substr($data, $i, 1, 'UTF-8');
            $o = unpack('N', mb_convert_encoding($c, 'UCS-4BE', 'UTF-8'));
            $mb_hex .= sprintf('%04X', $o[1]);
        }
        return $mb_hex;
    }

    public static function sendMessage($destination, $message, $gateway)
    {
        //if (empty($gateway)) {
            $gateway = Configuration::get('BULKSMS_FORALL_TYPE');
       // }
        if (!empty($gateway))
        {

                mb_internal_encoding('UTF-8');
                mb_http_output('UTF-8');

                $userkey = Configuration::get('BULKSMS_FORALL_USERKEY-'.$gateway);
                $passkey = Configuration::get('BULKSMS_FORALL_PASSKEY-'.$gateway);
                $from = Configuration::get('BULKSMS_FORALL_SENDERID-'.$gateway);
                $url = Configuration::get('BULKSMS_FORALL_URL-'.$gateway);
            $xml = Configuration::get('BULKSMS_FORALL_APIXML-'.$gateway);
            $xml =  str_replace(array('(=-','-=)'), array('<','>'), $xml);
            $destination =  str_replace(array('(=-','-=)'), array('<','>'), $destination);
                //$message = self::toUnicode($message);
                $text = $message;
                $year = date('Y', time());
            $month = date('m', time());
            $day = date('d', time());
            $hour = date('H', time());
            $minute = date('i', time());
            $second = date('d', time());
            $xml =  str_replace(array('{userkey}','{userpass}','{senderid}','{message}','{phones}','{year}','{month}','{day}','{hour}','{minute}','{second}'), array($userkey,$passkey,$from,$text,$destination,$year,$month,$day,$hour,$minute,$second), $xml);
            $destinationzxc =  str_replace(array('(=-','-=)'), array('<','>'),Configuration::get('BULKSMS_FORALL_SPLIT-'. Configuration::get('BULKSMS_FORALL_TYPE')));
            /*$xml = '
	<SMS>
	   <oturum>
		  <kullanici>'.$userkey.'</kullanici>
		  <sifre>'.$passkey.'</sifre>
	   </oturum>
	   <mesaj>
		  <baslik>'.$from.'</baslik>
		  <metin>'.$text.'</metin>
		  <alicilar>'.$destination.'</alicilar>
		  <tarih></tarih>
	   </mesaj>
	</SMS>';*/
//echo $destination;

                $ch=curl_init();
                $header_type = array('Content-Type: text/xml; charset=UTF-8');
                curl_setopt($ch, CURLOPT_URL,$url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS,$xml);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch, CURLOPT_HTTPHEADER,$header_type);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120);

                $result = curl_exec($ch);
                curl_close($ch);
                //echo $xml;

                /*$result2 =$result;
                $output = $result;
                $pos = explode('|', $output);
                $results = $pos[0];*/

            libxml_use_internal_errors(true);

            $x = simplexml_load_string($result, "SimpleXMLElement", LIBXML_NOCDATA);

            if ($x === false) {
                $x = array();
                $x['status'] = array();
                $x['status']['message'] = null;
                $x['status']['code'] = null;
                //echo "Failed loading XML: ";
                foreach(libxml_get_errors() as $errorx) {
                    $x['status']['message'] .= $errorx->message;
                }
            } else {
                $x = json_encode($x);
                $x = json_decode($x,TRUE);
            }
            //print_r($x);
            $results = $x['status']['code'];
                if ($results==200 || substr( $result, 0, 2 ) === "00" ){
                    $status ='Sent';
                }else {
                    $status = $x['status']['message'].$results." / ".$result;
                }



        }
        else
            $status = 'Unable to connect!';

        if ($status =='Sent')
        {
            $status_code = 1;
            $err_msg = '--';
        }
        else
        {
            $status_code = 0;
            $err_msg = $status;
        }
        $destination =  str_replace($destinationzxc, ',', $destination);
        Db::getInstance()->Execute(
            'INSERT INTO `'._DB_PREFIX_."bulksms_history` (`recipient`, `phone`, `event`, `message`, `status`, `error`, `date_add`) VALUES
				('$destination', '$destination', 'campaign', '$message', '$status_code', '$status', NOW());"
        );

        return $status;
    }
    public static function checkcredit($gateway)
    {
        if (empty($gateway)) {
            $gateway = Configuration::get('BULKSMS_FORALL_TYPE');
        }
        if (!empty($gateway))
        {

                mb_internal_encoding('UTF-8');
                mb_http_output('UTF-8');

                $userkey = Configuration::get('BULKSMS_FORALL_USERKEY-'.$gateway);
                $passkey = Configuration::get('BULKSMS_FORALL_PASSKEY-'.$gateway);
                $from = Configuration::get('BULKSMS_FORALL_SENDERID-'.$gateway);
                $url = Configuration::get('BULKSMS_FORALL_CREDITURL-'.$gateway);
            $xml = Configuration::get('BULKSMS_FORALL_CREDITXML-'.$gateway);
            $xml =  str_replace(array('(=-','-=)'), array('<','>'), $xml);
            $xml =  str_replace(array('{userkey}','{userpass}','{senderid}'), array($userkey,$passkey,$from), $xml);

                /*$xml = '
<RAPOR>
   <oturum>
      <kullanici>'.$userkey.'</kullanici>
      <sifre>'.$passkey.'</sifre>
   </oturum>
</RAPOR>';*/

//echo $destination;
                $ch=curl_init();
                $header_type = array('Content-Type: text/xml');
                curl_setopt($ch, CURLOPT_URL,$url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS,$xml);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch, CURLOPT_HTTPHEADER,$header_type);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120);
                $result = curl_exec($ch);
                curl_close($ch);

                $result2 =$result;
                $output = $result;
                $pos = explode('"', $output);
                //preg_match_all('/\((.*?)\)/', $result,$x);
//preg_match("#\"(.*?)\"#si",$result,$x);
            libxml_use_internal_errors(true);

            $x = simplexml_load_string($result, "SimpleXMLElement", LIBXML_NOCDATA);

            if ($x === false) {
                $x[] =  "Failed loading XML: ";
                foreach(libxml_get_errors() as $error) {
                    $x[] =  "<br>". $error->message;
                }
            } else {
                $x = json_encode($x);
                $x = json_decode($x,TRUE);
            }

                $ch=curl_init();
                curl_setopt($ch,CURLOPT_URL,$url);
                curl_setopt($ch,CURLOPT_TIMEOUT,5);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

                $y=curl_exec($ch);
                curl_close($ch);


                //$status = $x[1][1];
                $status = '<br><textarea rows="20" style="width: 100%;" readonly>'.print_r($x,true).' '.$y.'</textarea>';
                //$status = $xml;
        }
        else
            $status = 'Unable to connect!';


        return $status;
    }
}
?>