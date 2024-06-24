<?php
class AdminpssmsHistorysController extends ModuleAdminController
{
    protected $_defaultOrderBy = 'id_bulksms_history';
    protected $_defaultOrderWay = 'DESC';
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'bulksms_history';
        $this->className = 'bulksms_history';
        $this->lang = false;
        $this->identifier = 'id_bulksms_history';
        $this->list_no_link = true;
        $this->addRowAction('delete');

        $this->bulk_actions = array(
            'delete' => array(
                'text' => 'Delete Selected',
                'confirm' => 'Are you sure you want to delete selected ones?',
                'icon' => 'icon-trash'
            )
        );

        $icon_array = array(
            1 => array('class' => 'icon-check list-action-enable  action-enabled', 'alt' => 'Success'),
            0 => array('class' => 'icon-remove list-action-enable  action-disabled', 'alt' => 'Failed'),
        );

        $this->fields_list = array(
            'id_bulksms_history' => array(
                'title' => 'ID',
                'width' => 50
            ),
            'date_add' => array(
                'title' => 'Date',
                'type' => 'datetime',
                'align' => 'center'
            ),
            'message' => array(
                'title' => 'Message',
                'width' => 500
            ),
            'recipient' => array(
                'title' => 'Number',
                'align' => 'center',
                'width' => 100
            ),
            'status' => array(
                'title' => 'Status',
                'type' => 'bool',
                'icon' => $icon_array,
                'width' => 25,
                'align' => 'center'
            ),
            'error' => array(
                'title' => 'API Return Message',
                'width' => 100,
                'align' => 'center'
            )
        );

        parent::__construct();
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
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
        if (Tools::isSubmit('delete'.$this->table))
        {
            Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'bulksms_history` WHERE `id_bulksms_history` = '.(int)Tools::getValue('id_bulksms_history'));
            Tools::redirectAdmin(self::$currentIndex.'&conf=1&token='.$this->token);
            $this->confirmations[] = $this->l('Deleted Succesfully');
        }

        else if (Tools::isSubmit('submitBulkdelete'.$this->table))
        {
            foreach (Tools::getValue($this->table.'Box') as $selection)
                Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'bulksms_history` WHERE `id_bulksms_history` = '.(int)$selection);

            $this->confirmations[] = $this->l('Deleted Succesfully');
        }

    }
}
?>