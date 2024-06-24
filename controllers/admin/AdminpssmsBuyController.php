<?php
include_once (__DIR__.'/../../classes/crystalapi.php');

class AdminpssmsBuyController extends ModuleAdminController
{
    public $_htmlcode = '';
	public function __construct()
	{
		$this->bootstrap = true;
		$this->display = 'view';

		parent::__construct();
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
		$gateway =  pssmsapi::checkcredit('');

		$this->_htmlcode .= '


<div style="clear: both; padding-top:15px;" >
					
					<label class="control-label col-lg-1 ">
						<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">
      '.$this->l('Credit Information:').'
						</span>
					</label>
					<div class="col-lg-3">'
						.$gateway.
					'</div>
				</div>
				<div style="clear: both; padding-top:15px;" >


				
				<div class="panel-footer" style="clear: both; padding-top:15px;">
				<a href="https://github.com/anil-turk" target="_blank">
					<button type="submit" target="_blank" class="btn btn-default" name="buysms" id="configuration_form_submit_btn">
    Anıl Türk</button><a>
				</div>

';
	}


}
?>