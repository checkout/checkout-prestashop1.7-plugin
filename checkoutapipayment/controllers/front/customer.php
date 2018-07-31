<?php

class CheckoutapipaymentCustomerModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    /**
     * @see FrontController::initContent()
     */

    protected $customer;

    public function initContent()
    {

        $this->display_column_left = false;
        parent::initContent();
        $checkoutApiModule =  models_FactoryInstance::getInstance( 'checkoutapipayment' );
        $smartyParam = $checkoutApiModule->getInstanceMethod()->hookPaymentOptions(array());
        $smartyParam['local_path'] = $this->module->getPathUri();
        $smartyParam['module_dir'] = $this->module->getPathUri();
        $this->context->smarty->assign($smartyParam);
        $customer = $this->context->customer;
        $cardList = $this->getCustomerCardList($customer->id);
        $cardLists = array();

        if(!empty($cardList)){
            foreach ($cardList as $key) {
                    $test[] = $key;
            }

            $this->context->smarty->assign('cardLists', $test);
        }

        $this->setTemplate('module:checkoutapipayment/views/templates/front/customer-info.tpl');
    }

    public function getCustomerCardList($customerId) {
        $db = Db::getInstance();
        $sql = 'SELECT * FROM '._DB_PREFIX_."checkout_customer_cards WHERE customer_id = $customerId AND card_enabled = 1";
        $row = Db::getInstance()->executeS($sql);

        return $row;
    }

    public function process()
    {
        $customer = $this->context->customer;

        if(isset($_POST['checkoutapipayment-saved-card'])){
            $entityId = $_POST['checkoutapipayment-saved-card'];

            foreach ($entityId as $key) {
                $db = Db::getInstance();
                $sql = 'Delete from ' . _DB_PREFIX_ . 'checkout_customer_cards where customer_id='.$customer->id.' AND entity_id ='.$key;

                if (!$db->execute($sql))
                print_r('Error has occured when deleting card ');
            }
        }
    }
}