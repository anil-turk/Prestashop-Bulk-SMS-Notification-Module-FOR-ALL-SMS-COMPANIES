<?php
include_once (__DIR__.'/../../classes/crystalapi.php');

class AdminSendMessageController extends ModuleAdminController
{
    public $_htmlcode = '';
public function __construct()
{
$this->bootstrap = true;
$this->display = 'view';

parent::__construct();
}

public function postProcess()
{

if (Tools::isSubmit('sendMessage'))
{
$this->beforeUpdateOptions();

if (!count($this->errors))
{
$numbers = array();
switch (Tools::getValue('to'))
{

case 'nohp':
    $newexplode = explode(',',Tools::getValue('nohp'));
    foreach ($newexplode as $numberz){
        $numbers[] = $numberz;
    }
break;

case 'allcustomer':
foreach (Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS
('SELECT phone_mobile FROM `'._DB_PREFIX_.'address` WHERE id_customer <> 0 AND phone_mobile<>""') as $results)
$numbers[] = $results['phone_mobile'];
break;

case 'group':
foreach (Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS
('SELECT a.`id_customer`, pm.`phone_mobile` FROM `'._DB_PREFIX_.'customer_group` a LEFT JOIN `'._DB_PREFIX_.'address` pm ON
(a.`id_customer` = pm.`id_customer`) WHERE a.`id_group`='.Tools::getValue('idgroup')) as $results)
$numbers[] = $results['phone_mobile'];
break;
}
if(!empty(Tools::getValue('idorders'))) {
    $idler = explode(',', Tools::getValue('idorders'));
    foreach ($idler as $keyc => $valuec) {
        $orderdetail = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'orders WHERE id_order = ' . $valuec);
        $addresses = $orderdetail[0]['id_address_invoice'];
        $addressesx = $orderdetail[0]['id_address_delivery'];
        if ($addresses == $addressesx) {
            $adres = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'address WHERE id_address = ' . $addresses);
            $numbers[] = $adres[0]['phone_mobile'];
        } else {
            $adresx = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'address WHERE id_address = ' . $addressesx);
            if (!in_array($adresx[0]['phone_mobile'], $numbers)) {
                $numbers[] = $adresx[0]['phone_mobile'];
            }
        }
    }
}


if ($numbers)
{
    $destinationx = '';
foreach ($numbers as $deskey=>$destination){
    if($deskey != 0){
        $newlistspkit =  str_replace(array('(=-','-=)'), array('<','>'), Configuration::get('BULKSMS_FORALL_SPLIT-'. Configuration::get('BULKSMS_FORALL_TYPE')));
        if($newlistspkit == ""){
            $destinationx .= ',';
        }else{
            $destinationx .= $newlistspkit;
        }
    }
    $destinationx .= $destination;
}
    $status = pssmsapi::sendMessage($destinationx, Tools::getValue('isipesan'), '');
}
else
$status = $this->l('Phone Number is empty or wrong');

if ($status == 'Sent')
$this->confirmations[] = $this->l(count($idler).' sipariş,'.count($numbers).' telefon numarasına gönderildi.');
else
$this->errors[] = Tools::displayError($this->l('SMS couldnt send ').$destinationx.': '.$status);

}
}
}

public function initToolbarTitle()
{
$this->toolbar_title = array_unique($this->breadcrumbs);
}

public function renderView()
{
$this->badan();
return $this->_htmlcode;
}

public function badan()
{

$output = '';
$gateway = Configuration::get('BULKSMS_FORALL_TYPE');
    if($gateway == ""){
    $gateway = $this->l('API is not defined');
}
    $getawaynum = substr($gateway, -1);
    $gateway = $this->l('Setting').' '.$getawaynum.' - '.Configuration::get('BULKSMS_FORALL_URL-'.$gateway);
/*switch ($gateway)
{

case 'hizlisms':
$gateway = '<b>www.hizlisms.com.tr</b>';
break;
default:
$gateway = '<b>Geçerli bir api ayalanmamış!';
    }*/
    foreach (Group::getGroups($this->context->language->id, true) as $group)
    $output .= '<option value="'.$group['id_group'].'">'.$group['name'].'</option>';
    if(!empty(Tools::getValue('idorders'))) {
        $idlerx = explode(',', Tools::getValue('idorders'));
    $newtext = $this->l(count($idlerx).' sipariş seçildi');
    $newclass= 'display:none;';
}else{
$newtext = $this->l('Send Type');
$newclass= '';
}
    $this->_htmlcode .= '
    <script>
       $(function(){
    $("select[name=\'to\']").on("change", function() {
        $("#sms .to").hide();
        $("#sms #to-" + $(this).val()).show();
    });
    $("select[name=\'to\']").trigger("change");
});
    </script>

    <form action="'.$_SERVER['REQUEST_URI'].'"	method="post">
        <div class="panel " id="configuration_fieldset_general">

            <div class="panel-heading">
                <i class="icon-envelope"></i> '.$this->l('Send SMS').'
            </div>

            <div style="clear: both; padding-top:15px;" >

                <label class="control-label col-lg-3 ">
						<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">
							'.$this->l('Sms Setting').'
						</span>
                </label>
                <div class="col-lg-3">'
                    .$gateway.
                    '</div>
            </div>
            '.$newtext.'
            <div style="clear: both; padding-top:15px; '.$newclass.'" >

                <label class="control-label col-lg-3 ">
						<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Gönderim şeklini seçiniz." data-html="true">
							'.$this->l('-- Send Type --').'
						</span>
                </label>
                <div class="col-lg-3">
                    <select name="to" id="to">
                        <option value=""> '.$this->l('-- Send Type --').' </option>
                        <option value="nohp">'.$this->l('Input Numbers').'</option>
                        <option value="allcustomer">'.$this->l('All Customers').'</option>
                        <option value="group">'.$this->l('Groups').'</option>
                    </select>
                </div>
            </div>
            <div id="sms">
                <div id="to-nohp" class="to" style="clear: both; padding-top:15px; display:none;" >

                    <label class="control-label col-lg-3 ">
								<span title="" data-toggle="tooltip" class="label-tooltip"
                                      data-original-title="Gönderilecek telefon numarasını giriniz."
                                      data-html="true">
									'.$this->l('Phone Number/s (use comma (,) for multiple phone number)').'
								</span>
                    </label>
                    <div class="col-lg-3">
                        <input type="text" name="nohp" value="" size="43"/>
                    </div>
                </div>
                <div class="clear"></div>

                <div id="to-group" class="to" style="clear: both; padding-top:15px; display:none;" >
                    <label class="control-label col-lg-3 ">
								<span title="" data-toggle="tooltip" class="label-tooltip"
                                      data-original-title="'.$this->l('Choose Group to Send').'"
                                      data-html="true">
									'.$this->l('Group').'
								</span>
                    </label>
                    <div class="col-lg-3">
                        <select name="idgroup">
                            <option value=""> '.$this->l('-- Choose Group --').' </option>
                            '.$output.'
                        </select>
                    </div>
                </div>
                <div class="clear"></div>
            </div>

            <div style="clear: both; padding-top:15px;" >
                <label class="control-label col-lg-3 ">
						<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="'.$this->l('Input your message').'" data-html="true">
							'.$this->l('Message').'
						</span>
                </label>
                <div class="col-lg-3">
                    <textarea name="isipesan" cols="40" rows="5">'.(count($this->errors) ? Tools::getValue('isipesan') : '').'</textarea>
                </div>
            </div>

            <div class="clear" style="clear: both; padding-top:15px;"></div>

            <div class="panel-footer" style="clear: both; padding-top:15px;">
                <button type="submit" class="btn btn-default pull-left" name="sendMessage" id="configuration_form_submit_btn">
                    <i class="process-icon-envelope" ></i> '.$this->l('Send SMS').'</button>
            </div>

        </div>
        <br />
    </form>';
    }

    public function beforeUpdateOptions()
    {
        // Get the logged in user's email
        $userEmail = $this->context->employee->email;

        // Check if the user's email is "test@test.com"
        if ($userEmail == "test@test.com") {
            // If it is, do not allow them to change settings or send something
            $this->errors[] = Tools::displayError($this->l('You are not allowed to change settings or send something.'));
            return;
        }

    if (Configuration::get('BULKSMS_FORALL_TYPE'))
    {
        if (Tools::getValue('to') == 'nohp') {
            $newexplodez = explode(',', Tools::getValue('nohp'));
            foreach ($newexplodez as $numberzz) {
                if (!$numberzz || !Validate::isPhoneNumber($numberzz))
                {
                    $this->errors[] = Tools::displayError($this->l('Phone Number is empty or wrong'));
                }
            }
        }


    if (!Tools::getValue('isipesan'))
    $this->errors[] = Tools::displayError($this->l('Message cant be empty'));

    }
    else
    $this->errors[] = Tools::displayError($this->l('API is not defined'));
    }
    }
    ?>