<?php
class AdminSmsSettingsController extends ModuleAdminController
{
    public $html_code = '';
public function __construct()
{
$this->bootstrap = true;
$this->display = 'view';
$this->className = 'Configuration';
$this->table = 'configuration';
$this->context = Context::getContext();

parent::__construct();
}

public function postProcess()
{
    // Get the logged in user's email
    $userEmail = $this->context->employee->email;

    // Check if the user's email is "test@test.com"
    if ($userEmail == "test@test.com") {
        // If it is, do not allow them to change settings or send something
        $this->errors[] = Tools::displayError($this->l('You are not allowed to change settings or send something.'));
        return;
    }
if (Tools::isSubmit('submitSmsalert'))
{
Configuration::updateValue('BULKSMS_ZORDER_ALERT_ADMIN', Tools::getValue('BULKSMS_ZORDER_ALERT_ADMIN'));
Configuration::updateValue('BULKSMS_ZREGISTER_ALERT_ADMIN', Tools::getValue('BULKSMS_ZREGISTER_ALERT_ADMIN'));
Configuration::updateValue('BULKSMS_ZCONTACT_ALERT_ADMIN', Tools::getValue('BULKSMS_ZCONTACT_ALERT_ADMIN'));
Configuration::updateValue('BULKSMS_ZORDER_ALERT_CUST', Tools::getValue('BULKSMS_ZORDER_ALERT_CUST'));
Configuration::updateValue('BULKSMS_ZCONTACT_ALERT_CUST', Tools::getValue('BULKSMS_ZCONTACT_ALERT_CUST'));
Configuration::updateValue('BULKSMS_ZREGISTER_ALERT_CUST', Tools::getValue('BULKSMS_ZREGISTER_ALERT_CUST'));
$statuses = OrderState::getOrderStates((int)$this->context->language->id);
foreach ($statuses as $status)
Configuration::updateValue
('BULKSMS_ZSTATUSO_ALERT_CUST-'.$status['id_order_state'], Tools::getValue('BULKSMS_ZSTATUSO_ALERT_CUST-'.$status['id_order_state']));
Configuration::updateValue('BULKSMS_ZTRACKING_ALERT_CUST', Tools::getValue('BULKSMS_ZTRACKING_ALERT_CUST'));
$this->confirmations[] = $this->l('SMS Settings Saved');
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

private function badan()
{

$statuses = OrderState::getOrderStates((int)$this->context->language->id);
$opsi = '';
$msg = '';
foreach ($statuses as $status)
$opsi .= '<option value="'.$status['id_order_state'].'">'.$status['name'].'</option>';

foreach ($statuses as $status)
$msg .= '
<div id="BULKSMS_FORALL_STATUS-'.$status['id_order_state'].'" class="BULKSMS_FORALL_STATUS">
    <div style="clear: both; padding-top:10px;" id="conf_id_BULKSMS_FORALL_STATUS" >
        <label class="control-label col-lg-2 ">
						<span title="" data-toggle="tooltip" class="label-tooltip"
                              data-original-title="'.$this->l('Send SMS when order state changed to').' <b><i>'.$status['name'].'</i></b>"
                              data-html="true">
							<b><i>'.$status['name'].'</i></b>
						</span>
        </label>
        <div class="col-lg-5">
						<textarea name="BULKSMS_ZSTATUSO_ALERT_CUST-'.$status['id_order_state'].'" cols="45" rows="4">'
						.Configuration::get('BULKSMS_ZSTATUSO_ALERT_CUST-'.$status['id_order_state']).
						'</textarea>
            <p class="preference_description"><b>'.$this->l('If you dont want to send sms in this action. Leave empty !').'</b><br />
                <b>Değişkenler:</b> {firstname}, {lastname}, {ref}, {order_id}, {tracking_number}, {carrier}, {carrier_url}, {order_state}, {shopname}</p>
        </div>
    </div>
</div>';

$this->html_code .= '
<script>
    $(function(){
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
            <i class="icon-envelope"></i> '.$this->l('SMS to Admin').'
        </div>

        <div style="clear: both; padding-top:10px;" id="conf_id_BULKSMS_ZORDER_ALERT" >
            <label class="control-label col-lg-2 ">
						<span title="" data-toggle="tooltip" class="label-tooltip"
                              data-original-title="'.$this->l('Send SMS to Admin every new order').'"
                              data-html="true">
							'.$this->l('New Order').'
						</span>
            </label>
            <div class="col-lg-5">
                <textarea name="BULKSMS_ZORDER_ALERT_ADMIN" cols="45" rows="4">'.Configuration::get('BULKSMS_ZORDER_ALERT_ADMIN').'</textarea>
                <p class="preference_description"><b>'.$this->l('If you dont want to send sms in this action. Leave empty !').'</b>
                    <br /><b>'.$this->l('Variables').':</b> {firstname}, {lastname}, {email}, {delivery_company}, {delivery_firstname}, {delivery_lastname},
                    {delivery_address1}, {delivery_address2}, {delivery_city}, {delivery_postal_code}, {delivery_country}, {delivery_state},
                    {delivery_phone}, {delivery_other}, {invoice_company}, {invoice_firstname}, {invoice_lastname}, {invoice_address1}, {invoice_address2},
                    {invoice_city}, {invoice_postal_code}, {invoice_country}, {invoice_state}, {invoice_phone}, {invoice_other}, {order_name}, {date},
                    {carrier}, {ref}, {payment}, {items}, {total_paid}, {total_products}, {total_discounts}, {total_shipping}, {total_wrapping},
                    {currency}, {message}, {shopname}, {shopurl}</p>
            </div>
        </div>

        <div class="clear"></div>

        <div style="clear: both; padding-top:5px;" id="conf_id_BULKSMS_ZREGISTER_ALERT" >
            <label class="control-label col-lg-2 ">
						<span title="" data-toggle="tooltip" class="label-tooltip"
                              data-original-title="'.$this->l('Send SMS to Admin every new customer registered').'"
                              data-html="true">
							'.$this->l('New Customer').'
						</span>
            </label>
            <div class="col-lg-5">
                <textarea name="BULKSMS_ZREGISTER_ALERT_ADMIN" cols="45" rows="4">'.Configuration::get('BULKSMS_ZREGISTER_ALERT_ADMIN').'</textarea>
                <p class="preference_description"><b>'.$this->l('If you dont want to send sms in this action. Leave empty !').'</b><br />
                    <b>'.$this->l('Variables').':</b> {firstname}, {lastname}, {email}, {shopname}, {shopurl}</p>
            </div>
        </div>


        <div class="clear" style="clear: both; padding-top:15px;"></div>
    </div>


    <div class="panel " id="configuration_fieldset_general">

        <div class="panel-heading">
            <i class="icon-envelope"></i> '.$this->l('SMS to Customers').'
        </div>

        <div style="clear: both; padding-top:10px;" id="conf_id_BULKSMS_ZORDER_ALERT_CUST" >

            <label class="control-label col-lg-2 ">
						<span title="" data-toggle="tooltip" class="label-tooltip"
                              data-original-title="'.$this->l('SMS after order created to customer').'"
                              data-html="true">
							'.$this->l('New Order SMS to Customer').'
						</span>
            </label>
            <div class="col-lg-5">
                <textarea name="BULKSMS_ZORDER_ALERT_CUST" cols="45" rows="4">'.Configuration::get('BULKSMS_ZORDER_ALERT_CUST').'</textarea>
                <p class="preference_description"><b>'.$this->l('If you dont want to send sms in this action. Leave empty !').'</b><br />
                    <b>'.$this->l('Variables').':</b> {firstname}, {lastname}, {email}, {delivery_company}, {delivery_firstname}, {delivery_lastname},
                    {delivery_address1}, {delivery_address2}, {delivery_city}, {delivery_postal_code}, {delivery_country}, {delivery_state},
                    {delivery_phone}, {delivery_other}, {invoice_company}, {invoice_firstname}, {invoice_lastname}, {invoice_address1},
                    {invoice_address2}, {invoice_city}, {invoice_postal_code}, {invoice_country}, {invoice_state}, {invoice_phone},
                    {invoice_other}, {order_name}, {date}, {carrier}, {ref}, {payment}, {items}, {total_paid}, {total_products}, {total_discounts},
                    {total_shipping}, {total_wrapping}, {currency}, {message}, {shopname}, {shopurl}</p>
            </div>
        </div>

        <div class="clear"></div>

        <div style="clear: both; padding-top:10px;" id="conf_id_BULKSMS_ZREGISTER_ALERT_CUST" >

            <label class="control-label col-lg-2 ">
						<span title="" data-toggle="tooltip" class="label-tooltip"
                              data-original-title="'.$this->l('For this SMS type work, you need to get address info when customer creating account. Settings->Customers->Register Type').'"
                              data-html="true">
							'.$this->l('Registration SMS (Welcome SMS) to customer').'
						</span>
            </label>
            <div class="col-lg-5">
                <textarea name="BULKSMS_ZREGISTER_ALERT_CUST" cols="45" rows="4">'.Configuration::get('BULKSMS_ZREGISTER_ALERT_CUST').'</textarea>
                <p class="preference_description"><b>'.$this->l('If you dont want to send sms in this action. Leave empty !').'</b><br />
                    <b>'.$this->l('Variables').':</b> {firstname}, {lastname}, {email}, {password}, {shopname}, {shopurl}</p>
            </div>
        </div>





        <div class="clear"></div>

        <div style="clear: both; padding-top:10px;" id="conf_id_BULKSMS_ZSTATUSO_ALERT_CUST" >
            <label class="control-label col-lg-2 ">
						<span title="" data-toggle="tooltip" class="label-tooltip"
                              data-original-title="'.$this->l('SMS to Customer when Order Status Changed').'"
                              data-html="true">
							'.$this->l('Order Status Changed Notifications').'
						</span>
            </label>
        </div>

        <div class="clear"></div>
        '.$msg.'

        <div class="clear"></div>

        <div style="clear: both; padding-top:10px;" id="conf_id_BULKSMS_ZTRACKING_ALERT_CUST" >
            <label class="control-label col-lg-2 ">
						<span title="" data-toggle="tooltip" class="label-tooltip"
                              data-original-title="'.$this->l('Shipping Tracking Info Added/Updated SMS').'"
                              data-html="true">
							'.$this->l('Shipping Tracking Info Added/Updated SMS').'
						</span>
            </label>
            <div class="col-lg-5">
                <textarea name="BULKSMS_ZTRACKING_ALERT_CUST" cols="45" rows="4">'.Configuration::get('BULKSMS_ZTRACKING_ALERT_CUST').'</textarea>
                <p class="preference_description"><b>'.$this->l('If you dont want to send sms in this action. Leave empty !').'</b><br />
                    <b>'.$this->l('Variables').':</b> {firstname}, {lastname}, {tracking_number}, {carrier}, {carrier_url}, {ref}, {order_id}, {shopname}</p>
            </div>
        </div>

        <div class="clear" style="clear: both; padding-top:15px;"></div>

    </div>

    <div class="clear" style="clear: both; padding-top:15px;"></div>

    <div class="panel-footer" style="clear: both; padding-top:15px;">
        <button type="submit"  class="btn btn-default pull-left" name="submitSmsalert" id="configuration_form_submit_btn">
            <i class="process-icon-save" ></i> '.$this->l('Save Settings').'</button>
    </div>

</form>';
}
}
?>