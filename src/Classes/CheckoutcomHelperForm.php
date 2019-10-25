<?php

namespace CheckoutCom\PrestaShop\Classes;

use CheckoutCom\PrestaShop\Helpers\Utilities;
use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Models\Config;

class CheckoutcomHelperForm extends \HelperForm
{

    /**
     * Path location of configurations.
     *
     * @var        string
     */
    const CHECKOUTCOM_CONFIGS = CHECKOUTCOM_ROOT . DIRECTORY_SEPARATOR . 'configs';

    /**
     * Prefix for config panels variables.
     *
     * @var        string
     */
    const CHECKOUTCOM_CONFIGS_PREFIX = 'config_';

    /**
     * 'Title' field.
     *
     * @var        string
     */
    const FIELD_TITLE = 'title';

    /**
     * 'Title' field.
     *
     * @var        string
     */
    const FIELD_ICON = 'icon';

    /**
     * 'Fields' field.
     *
     * @var        string
     */
    const FIELD_FIELDS = 'fields';

    /**
     * 'Type' field.
     *
     * @var        string
     */
    const FIELD_TYPE = 'type';

    /**
     * 'Name' field.
     *
     * @var        string
     */
    const FIELD_NAME = 'name';

    /**
     * 'Default' field.
     *
     * @var        string
     */
    const FIELD_DEFAULT = 'default';

    /**
     * 'Id' field.
     *
     * @var        string
     */
    const FIELD_ID = 'id';

    /**
     * 'Value' field.
     *
     * @var        string
     */
    const FIELD_VALUE = 'value';

    /**
     * 'Values' field.
     *
     * @var        string
     */
    const FIELD_VALUES = 'values';

    /**
     * 'Label' field.
     *
     * @var        string
     */
    const FIELD_LABEL = 'label';

    /**
     * 'Is bool' field.
     *
     * @var        string
     */
    const FIELD_IS_BOOL = 'is_bool';

    /**
     * 'Desc' field.
     *
     * @var        string
     */
    const FIELD_DESC = 'desc';

    /**
     * 'Required' field.
     *
     * @var        string
     */
    const FIELD_REQUIRED = 'required';

    /**
     * 'Options' field.
     *
     * @var        string
     */
    const FIELD_OPTIONS = 'options';

    /**
     * 'Column' field.
     *
     * @var        string
     */
    const FIELD_COL = 'col';

    /**
     * List of settings.
     *
     * @var        array
     */
    protected $settings = array();

    /**
     * List of references.
     *
     * @var        array
     */
    protected $dictionary = array();

    /**
     * Setup HelperForm.
     */
    public function __construct() {
        parent::__construct();
    }


    /**
     * Methods
     */

    /**
     * Add to smarty template variable.
     *
     * @param      mixed  $smarty  The smarty
     */
    public function addToSmarty(&$smarty) {
//Debug::write('CheckoutcomHelperForm.addToSmarty($smarty)');
        foreach (Config::definition() as $key => $forms) {
//Debug::write('CheckoutcomHelperForm.addToSmarty($smarty).' . $key);
            $smarty->assign(static::CHECKOUTCOM_CONFIGS_PREFIX . $key, $this->generateForm($forms));
        }

    }

    /**
     * Generate HTML for form.
     *
     * @overriden
     * @param      array  $forms
     * @return     string
     */
    public function generateForm($forms) {
//Debug::write('CheckoutcomHelperForm.generateForm($smarty)');
        $list = array();

        foreach ($forms as $form) {

            $current = array('legend' => array(static::FIELD_TITLE => ''),
                            'input' => array(),
                            'submit' => array(static::FIELD_TITLE => $this->l('Save')));

            foreach ($form as $field) {

                $input = $this->{$field[static::FIELD_TYPE]}($field);
                if($input) {
                    $current['input'] []= $input;
                }

            }
//Debug::write($form);
            $list []= array('form' => $current);

        }

        return parent::generateForm($list);

    }


    /**
     * Adpat fields
     */

    /**
     * Generate prestashop switch from configuration.
     * @param      array $field
     * @return     array
     */
    protected function bswitch(array &$field) {

        $options = function(array $options) {
            $arr = array();
            foreach ($options as $option) {
                $arr []= array(
                    static::FIELD_ID => $option[static::FIELD_ID],
                    static::FIELD_VALUE => $option[static::FIELD_VALUE],
                    static::FIELD_LABEL => $this->l($option[static::FIELD_LABEL])
                );
            }
            return $arr;
        };

        return array(
                static::FIELD_TYPE => 'switch',
                static::FIELD_LABEL => $this->l($field[static::FIELD_LABEL]),
                static::FIELD_NAME => $field[static::FIELD_NAME],
                static::FIELD_REQUIRED => Utilities::getValueFromArray($field, static::FIELD_REQUIRED, false),
                static::FIELD_IS_BOOL => Utilities::getValueFromArray($field, static::FIELD_IS_BOOL, true),
                static::FIELD_DESC => $this->l(Utilities::getValueFromArray($field, static::FIELD_DESC)),
                static::FIELD_VALUES => $options($field['options']),
            );

    }

    /**
     * Generate prestashop select from configuration.
     * @param      array $field
     * @return     array
     */
    protected function select(array &$field) {

        $select = function(array $select) {

            $options = array(
                        'query' => array(),
                        static::FIELD_ID  => 'id_option',
                        static::FIELD_NAME => static::FIELD_NAME
                    );

            foreach ($select as $s) {
                $options['query'] []= array(
                    'id_option' => $s[static::FIELD_VALUE],
                    static::FIELD_NAME => $s[static::FIELD_LABEL]
                );
            }

            return $options;

        };

        return array(
                static::FIELD_TYPE => $field[static::FIELD_TYPE],
                static::FIELD_LABEL => $this->l($field[static::FIELD_LABEL]),
                static::FIELD_NAME => $field[static::FIELD_NAME],
                static::FIELD_REQUIRED => Utilities::getValueFromArray($field, static::FIELD_REQUIRED, false),
                static::FIELD_DESC => $this->l(Utilities::getValueFromArray($field, static::FIELD_DESC)),
                'options' => $select($field['options'])
            );

    }

    /**
     * Generate prestashop text from configuration.
     * @param      array $field
     * @return     array
     */
    protected function text(array &$field) {

        return array(
                static::FIELD_COL => Utilities::getValueFromArray($field, static::FIELD_COL, 2),
                static::FIELD_TYPE => $field[static::FIELD_TYPE],
                static::FIELD_LABEL => $this->l($field[static::FIELD_LABEL]),
                static::FIELD_NAME => $field[static::FIELD_NAME],
                static::FIELD_DEFAULT => Utilities::getValueFromArray($field, static::FIELD_DEFAULT),
                static::FIELD_REQUIRED => Utilities::getValueFromArray($field, static::FIELD_REQUIRED, false),
                static::FIELD_DESC => $this->l(Utilities::getValueFromArray($field, static::FIELD_DESC)),
            );

    }

    /**
     * Generate prestashop password from configuration.
     * @param      array $field
     * @return     array
     */
    protected function password(array &$field) {

        return array(
                static::FIELD_COL => Utilities::getValueFromArray($field, static::FIELD_COL, 3),
                static::FIELD_TYPE => $field[static::FIELD_TYPE],
                static::FIELD_LABEL => $this->l($field[static::FIELD_LABEL]),
                static::FIELD_NAME => $field[static::FIELD_NAME],
                static::FIELD_REQUIRED => Utilities::getValueFromArray($field, static::FIELD_REQUIRED, false),
                static::FIELD_DESC => $this->l(Utilities::getValueFromArray($field, static::FIELD_DESC)),
            );

    }

    /**
     * Generate prestashop password from configuration.
     * @param      array $field
     * @return     array
     */
    protected function dynamic(array &$field) {

        if(strpos($field[static::FIELD_NAME], 'ORDER_STATUS') !== false) {
            $this->loadOrderStatuses($field);
        }

        $obj = array(
                static::FIELD_COL => Utilities::getValueFromArray($field, static::FIELD_COL, 3),
                static::FIELD_TYPE => $field[static::FIELD_TYPE],
                static::FIELD_LABEL => $this->l($field[static::FIELD_LABEL]),
                static::FIELD_NAME => $field[static::FIELD_NAME],
                static::FIELD_REQUIRED => Utilities::getValueFromArray($field, static::FIELD_REQUIRED, false),
                static::FIELD_DESC => $this->l(Utilities::getValueFromArray($field, static::FIELD_DESC)),
                'options' => $field['options']
            );

        return $obj;

    }

    /**
     * Dynamicaly load order status.
     *
     * @param      array  $field  The field
     * @return     void
     */
    protected function loadOrderStatuses(array &$field) {

        $field[static::FIELD_TYPE] = 'select';
        $field['options'] = array(
            'query' => array(),
            static::FIELD_ID  => 'id_option',
            static::FIELD_NAME => static::FIELD_NAME
        );

        foreach (\OrderState::getOrderStates($this->context->language->id) as $state) {
            $field['options']['query'][]= array(
                'id_option' => $state['id_order_state'],
                static::FIELD_NAME => $this->l($state[static::FIELD_NAME])
            );
        }

    }

    /**
     * Generate list for dictionary.
     * @param      array $field
     * @return     array
     */
    protected function dictionary(array &$field) {

        foreach ($field['options'] as $value) {
            $this->dictionary[$value['key']] = $value['value'];
        }

        return null;

    }

}