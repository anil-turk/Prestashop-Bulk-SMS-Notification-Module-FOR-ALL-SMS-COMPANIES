<?php
include_once (__DIR__.'/../../classes/crystalapi.php');
class AdminAccountSettingsController extends ModuleAdminController
{
    public $html_code = '';
public function __construct()
{
$this->bootstrap = true;
$this->display = 'view';
parent::__construct();
}

public function postProcess()
{
if (Tools::isSubmit('submitApisetting'))
{
    // Get the logged in user's email
    $userEmail = $this->context->employee->email;

    // Check if the user's email is "test@test.com"
    if ($userEmail == "test@test.com") {
        // If it is, do not allow them to change settings or send something
        $this->errors[] = Tools::displayError($this->l('You are not allowed to change settings or send something.'));
        return;
    }
$this->beforeUpdateOptions();

if (!count($this->errors))
{
Configuration::updateValue('BULKSMS_FORALL_TYPE', Tools::getValue('BULKSMS_FORALL_TYPE'));

$newxml =  str_replace(array('<','>'), array('(=-','-=)'), Tools::getValue('BULKSMS_FORALL_APIXML-'.Tools::getValue('BULKSMS_FORALL_TYPE')));
$newcreditxml =  str_replace(array('<','>'), array('(=-','-=)'), Tools::getValue('BULKSMS_FORALL_CREDITXML-'.Tools::getValue('BULKSMS_FORALL_TYPE')));
$splitterxml =  str_replace(array('<','>'), array('(=-','-=)'), Tools::getValue('BULKSMS_FORALL_SPLIT-'.Tools::getValue('BULKSMS_FORALL_TYPE')));



Configuration::updateValue('BULKSMS_FORALL_USERKEY-'.Tools::getValue('BULKSMS_FORALL_TYPE'), Tools::getValue('BULKSMS_FORALL_USERKEY-'.Tools::getValue('BULKSMS_FORALL_TYPE')));
Configuration::updateValue('BULKSMS_FORALL_PASSKEY-'.Tools::getValue('BULKSMS_FORALL_TYPE'), Tools::getValue('BULKSMS_FORALL_PASSKEY-'.Tools::getValue('BULKSMS_FORALL_TYPE')));
Configuration::updateValue('BULKSMS_FORALL_SENDERID-'.Tools::getValue('BULKSMS_FORALL_TYPE'), Tools::getValue('BULKSMS_FORALL_SENDERID-'.Tools::getValue('BULKSMS_FORALL_TYPE')));
Configuration::updateValue('BULKSMS_FORALL_HPADMIN', Tools::getValue('BULKSMS_FORALL_HPADMIN'));
Configuration::updateValue('BULKSMS_FORALL_URL-'.Tools::getValue('BULKSMS_FORALL_TYPE'),  Tools::getValue('BULKSMS_FORALL_URL-'.Tools::getValue('BULKSMS_FORALL_TYPE')));
Configuration::updateValue('BULKSMS_FORALL_APIXML-'.Tools::getValue('BULKSMS_FORALL_TYPE'),  $newxml);
    Configuration::updateValue('BULKSMS_FORALL_SPLIT-'.Tools::getValue('BULKSMS_FORALL_TYPE'),  $splitterxml);
Configuration::updateValue('BULKSMS_FORALL_CREDITURL-'.Tools::getValue('BULKSMS_FORALL_TYPE'),  Tools::getValue('BULKSMS_FORALL_CREDITURL-'.Tools::getValue('BULKSMS_FORALL_TYPE')));
    Configuration::updateValue('BULKSMS_FORALL_CREDITXML-'.Tools::getValue('BULKSMS_FORALL_TYPE'),  $newcreditxml);


$this->confirmations[] = $this->l('API Settings Applied.');
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
return $this->html_code;
}

public function badan()
{


$sms_type = Configuration::get('BULKSMS_FORALL_TYPE');

$this->html_code .= '
<script>
    $(function(){
        $("select[name=\'BULKSMS_FORALL_TYPE\']").on("change", function() {
            $("#configuration_form .BULKSMS_FORALL_TYPE").hide();
            $("#configuration_form #BULKSMS_FORALL_TYPE-" + $(this).val()).show();
        });
        $("select[name=\'BULKSMS_FORALL_TYPE\']").trigger("change");
    });
</script>

<form action="'.$_SERVER['REQUEST_URI'].'"	id="configuration_form"	method="post"	enctype="multipart/form-data">
    <div class="panel " id="configuration_fieldset_general">

        <div class="panel-heading">
            <i class="icon-cogs"></i> '.$this->l('API Settings').'
        </div>

        <div style="clear: both; padding-top:15px;" id="conf_id_BULKSMS_FORALL_TYPE" >
            <label class="control-label col-lg-2 required">
						<span title="" data-toggle="tooltip" class="label-tooltip"
                              data-original-title="'.$this->l('Choose API Setting (for add,update,use settings').'"
                              data-html="true">
							'.$this->l('Choose API Setting (for add,update,use settings)').'
						</span>
            </label>

            <div class="col-lg-6">
                <select name="BULKSMS_FORALL_TYPE" id="tipe" required>
                    <option value="">'.$this->l('Choose API Setting').'</option>.';
                for($z = 1; $z <=5; $z++){
                    $this->html_code .= '<option value="smscomp'.$z.'" '.($sms_type == 'smscomp'.$z.'' ? 'selected' : '').'>
                    '.$this->l('Setting').' '.$z.' - '.Configuration::get('BULKSMS_FORALL_URL-smscomp'.$z).'
                    </option>';
                }
    $this->html_code .= '
                </select>
            </div>
        </div>';
        for($z = 1; $z <=5; $z++) {
           $newlistxml =  str_replace(array('(=-','-=)'), array('<','>'), Configuration::get('BULKSMS_FORALL_APIXML-smscomp'. $z));
            $newlistcreditxml =  str_replace(array('(=-','-=)'), array('<','>'), Configuration::get('BULKSMS_FORALL_CREDITXML-smscomp'. $z));
            $newlistspkit =  str_replace(array('(=-','-=)'), array('<','>'), Configuration::get('BULKSMS_FORALL_SPLIT-smscomp'. $z));
            $this->html_code .= '<div id="BULKSMS_FORALL_TYPE-smscomp' . $z . '" class="BULKSMS_FORALL_TYPE" xmlns="http://www.w3.org/1999/html">
            <div style="clear: both; padding-top:15px;" id="conf_id_BULKSMS_FORALL_USERKEY" >
                <label class="control-label col-lg-2 required">
							<span title="" data-toggle="tooltip" class="label-tooltip"
                                  data-original-title="' .$this->l('User KEY/Name (on your sms companies api information) API KEY').'"
                                  data-html="true">
								'.$this->l('User KEY').'
							</span>
                </label>
                <div class="col-lg-6">
                    <input type="text" name="BULKSMS_FORALL_USERKEY-smscomp' . $z . '"
                           value="' . Tools::getValue('BULKSMS_FORALL_USERKEY-smscomp'. $z, Configuration::get('BULKSMS_FORALL_USERKEY-smscomp'. $z)) . '"
                           size="50"/>
                </div>
            </div>
            <div class="clear"></div>
            <div style="clear: both; padding-top:15px;" id="conf_id_BULKSMS_FORALL_PASSKEY" >
                <label class="control-label col-lg-2 required">
							<span title="" data-toggle="tooltip" class="label-tooltip"
                                  data-original-title="'.$this->l('User PASS/SECRET (on your sms companies api or user information) API SECRET').'"
                                  data-html="true">
								'.$this->l('User PASS/Secret').'
							</span>
                </label>
                <div class="col-lg-6">
                    <input type="password" name="BULKSMS_FORALL_PASSKEY-smscomp' . $z . '"
                           value="' . Tools::getValue('BULKSMS_FORALL_PASSKEY-smscomp'. $z, Configuration::get('BULKSMS_FORALL_PASSKEY-smscomp'. $z)) . '"
                           size="50"/>
                </div>
            </div>
            <div class="clear"></div>
            <div style="clear: both; padding-top:15px;" id="conf_id_BULKSMS_FORALL_SENDERID" >
                <label class="control-label col-lg-2 required">
							<span title="" data-toggle="tooltip" class="label-tooltip"
                                  data-original-title="'.$this->l('Sender ID').'"
                                  data-html="true">
								'.$this->l('Sender ID').'
							</span>
                </label>
                <div class="col-lg-6">
                    <input type="text" name="BULKSMS_FORALL_SENDERID-smscomp' . $z . '"
                           value="' . Tools::getValue('BULKSMS_FORALL_SENDERID-smscomp' . $z, Configuration::get('BULKSMS_FORALL_SENDERID-smscomp'. $z)) . '"
                           size="11"/>
                    <div class="preference_description">'.$this->l('Sender ID min:3 max:11 chars').'</div>
                </div>
            </div>
            <div style="clear: both; padding-top:15px;" id="conf_id_BULKSMS_FORALL_URL" >
                <label class="control-label col-lg-2 required">
							<span title="" data-toggle="tooltip" class="label-tooltip"
                                  data-original-title="'.$this->l('SMS Send Api URL').'"
                                  data-html="true">
								'.$this->l('SMS Send Api URL').'
							</span>
                </label>
                <div class="col-lg-6">
                    <input type="text" name="BULKSMS_FORALL_URL-smscomp' . $z . '"
                           value="' . Tools::getValue('BULKSMS_FORALL_URL-smscomp' . $z, Configuration::get('BULKSMS_FORALL_URL-smscomp'. $z)) . '"
                           size="50"/>
                    <div class="preference_description">'.$this->l('For example: https://www.smscompany.com/sendsmsapi.php').'</div>
                </div>
            </div>
             <div style="clear: both; padding-top:15px;" id="conf_id_BULKSMS_FORALL_APIXML" >
                <label class="control-label col-lg-2 required">
							<span title="" data-toggle="tooltip" class="label-tooltip"
                                  data-original-title="'.$this->l('SMS Send Api XML').'"
                                  data-html="true">
								'.$this->l('SMS Send Api XML').'
							</span>
                </label>
                <div class="col-lg-6">
                    <TEXTAREA name="BULKSMS_FORALL_APIXML-smscomp' . $z . '" >' . $newlistxml . '</textarea> 
                    <div class="preference_description">'.$this->l('You need to use these variables inside the xml: {userkey},{userpass},{senderid},{message},{phones},{year},{month},{day},{hour},{minute},{second}').'</div>
                </div>
            </div>
            <div style="clear: both; padding-top:15px;" id="conf_id_BULKSMS_FORALL_SPLIT" >
                <label class="control-label col-lg-2 required">
							<span title="" data-toggle="tooltip" class="label-tooltip"
                                  data-original-title="'.$this->l('When you send SMS from Admin panel').'"
                                  data-html="true">
								'.$this->l('Number Splitter (for Send SMS on Admin Panel)').'
							</span>
                </label>
                <div class="col-lg-6">
                    <input type="text" name="BULKSMS_FORALL_SPLIT-smscomp' . $z . '"
                           value="' . $newlistspkit . '"
                           size="50"/>
                    <div class="preference_description">'.$this->l('You can use one request for all customers. If your api does not support comma numbers you can change to tags. For Example: </number><number>').'</div>
                </div>
            </div>
            <div style="clear: both; padding-top:15px;" id="conf_id_BULKSMS_FORALL_CREDITURL" >
                <label class="control-label col-lg-2 required">
							<span title="" data-toggle="tooltip" class="label-tooltip"
                                  data-original-title="'.$this->l('SMS Credit Check Api URL').'"
                                  data-html="true">
								'.$this->l('SMS Credit Check Api URL').'
							</span>
                </label>
                <div class="col-lg-6">
                    <input type="text" name="BULKSMS_FORALL_CREDITURL-smscomp' . $z . '"
                           value="' . Tools::getValue('BULKSMS_FORALL_CREDITURL-smscomp' . $z, Configuration::get('BULKSMS_FORALL_CREDITURL-smscomp'. $z)) . '"
                           size="50"/>
                    <div class="preference_description">'.$this->l('For example: https://www.smscompany.com/checkcreditapi.php').'</div>
                </div>
            </div>
             <div style="clear: both; padding-top:15px;" id="conf_id_BULKSMS_FORALL_CREDITXML" >
                <label class="control-label col-lg-2 required">
							<span title="" data-toggle="tooltip" class="label-tooltip"
                                  data-original-title="'.$this->l('SMS Credit Check Api XML').'"
                                  data-html="true">
								'.$this->l('SMS Credit Check Api XML').'
							</span>
                </label>
                <div class="col-lg-6">
                    <TEXTAREA name="BULKSMS_FORALL_CREDITXML-smscomp' . $z . '" >' . $newlistcreditxml . '</textarea> 
                    <div class="preference_description">'.$this->l('You need to use these variables inside the xml: {userkey},{userpass},{senderid} (Sender ID might not be required)').'</div>
                </div>
            </div>
        </div>';
        }
    $this->html_code .= '<div style="clear: both; padding-top:15px;" id="conf_id_BULKSMS_FORALL_HPADMIN" >
                <label class="control-label col-lg-2 required">
						<span title="" data-toggle="tooltip" class="label-tooltip"
                              data-original-title="'.$this->l('Admin Phone Number for Notification').'"
                              data-html="true">
							'.$this->l('Admin Phone Number/s').'
						</span>
                </label>
        
                <div class="col-lg-6">
                    <input type="text" name="BULKSMS_FORALL_HPADMIN"
                           value="'.Tools::getValue('BULKSMS_FORALL_HPADMIN', Configuration::get('BULKSMS_FORALL_HPADMIN')).'"
                           size="50"/>
                    <div class="preference_description">'.$this->l('You can add more than one number with comma (,) between numbers.').'</div>
                </div>
            </div>
        




        <div class="clear" style="clear: both; padding-top:15px;">
            <label class="control-label col-lg-2 required">
						<span title="" data-toggle="tooltip" class="label-tooltip"
                              data-original-title="'.$this->l('Required fields can not be empty.').'"
                              data-html="true">
							'.$this->l('Required Fields').'
						</span>
            </label>
        </div>

        <div class="clear" style="clear: both; padding-top:15px;"></div>

        <div class="panel-footer" style="clear: both; padding-top:15px;">
            <button type="submit"  class="btn btn-default pull-left" name="submitApisetting" id="configuration_form_submit_btn">
                <i class="process-icon-save" ></i> '.$this->l('Save Settings').'</button>
        </div>

    </div>

</form>';
}

public function beforeUpdateOptions()
{

if (Tools::getValue('BULKSMS_FORALL_TYPE'))
{


if (!Tools::getValue('BULKSMS_FORALL_USERKEY-'.Tools::getValue('BULKSMS_FORALL_TYPE')) || !Tools::getValue('BULKSMS_FORALL_PASSKEY-'.Tools::getValue('BULKSMS_FORALL_TYPE')))

$this->errors[] = Tools::displayError($this->l('Please fill all required fields!'));

}
else {
    $this->errors[] = Tools::displayError($this->l('Please fill all required fields!'));
}
}
}
?>