<?php

namespace CheckoutCom\PrestaShop\Models;

use CheckoutCom\PrestaShop\Helpers\Utilities;
use PrestaShop\PrestaShop\Adapter\Entity\Db;

class Config extends \Configuration
{
    /**
     * Path location of configurations.
     *
     * @var string
     */
    const CHECKOUTCOM_CONFIGS = CHECKOUTCOM_ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;

    /**
     * Save configutation.
     *
     * @var array
     */
    protected static $configs = array();

    /**
     * Setup module variables.
     */
    public static function install()
    {
        Config::load();
        foreach (Config::defaults() as $key => $value) {
            \Configuration::updateValue($key, $value);
        }

        // create cko_cards table
        Config::createCkoSaveCardTable();

    }

    /**
     * Clear module variables.
     */
    public static function uninstall()
    {
        Config::load();
        foreach (Config::keys() as $key) {
            \Configuration::deleteByName($key);
        }

        //include(dirname(__FILE__).'/sql/uninstall.php');
    }

    /**
     * Load settings to memory.
     */
    public static function load()
    {
        if (!Config::$configs) {
            $files = scandir(Config::CHECKOUTCOM_CONFIGS);
            foreach ($files as $file) {
                if (strpos($file, '.json') !== false) {
                    $filename = basename($file, '.json');
                    Config::$configs[$filename] = Utilities::getConfig($filename);
                }
            }
        }
    }

    /**
     * Get values from settings.
     *
     * @param string $name The name
     *
     * @return array
     */
    public static function values($name = '')
    {
        Config::load();

        $forms = array();
        $fields = array();

        if ($name) {
            $forms = Config::$configs[$name];
        } else {
            foreach (Config::$configs as $key => $configuration) {
                $forms = array_merge($forms, $configuration);
            }
        }

        foreach ($forms as $form) {
            foreach ($form as $field) {
                $fields[$field['name']] = \Configuration::get($field['name'], Utilities::getValueFromArray($field, 'default'));
            }
        }

        return $fields;
    }

    /**
     * Get defaults of the settings.
     *
     * @param string $name The name
     *
     * @return array
     */
    public static function defaults($name = '')
    {
        Config::load();

        $forms = array();
        $fields = array();

        if ($name) {
            $forms = Config::$configs[$name];
        } else {
            foreach (Config::$configs as $key => $configuration) {
                $forms = array_merge($forms, $configuration);
            }
        }

        foreach ($forms as $form) {
            foreach ($form as $field) {
                $fields[$field['name']] = Utilities::getValueFromArray($field, 'default');
            }
        }

        return $fields;
    }

    /**
     * Get keys of the fields.
     *
     * @param string $name The name
     *
     * @return array
     */
    public static function keys($name = '')
    {
        Config::load();

        $forms = array();
        $keys = array();

        if ($name) {
            $forms = Config::$configs[$name];
        } else {
            foreach (Config::$configs as $key => $configuration) {
                $forms = array_merge($forms, $configuration);
            }
        }

        foreach ($forms as $form) {
            foreach ($form as $field) {
                $keys[] = $field['name'];
            }
        }

        return $keys;
    }

    /**
     * Return full settings.
     *
     * @return array
     */
    public static function definition($name = '')
    {
        Config::load();

        if ($name) {
            return Config::$configs[$name];
        } else {
            return Config::$configs;
        }
    }

     /**
     * This method is used to create checkout saved card table
     */
    public static function createCkoSaveCardTable()
    {
        $sql = "CREATE TABLE  IF NOT EXISTS "._DB_PREFIX_."cko_cards
             (
                entity_id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                customer_id INT(11) NOT NULL COMMENT 'Customer ID from PS'  ,
                source_id VARCHAR(100) NOT NULL COMMENT 'Source ID from cko' ,
                last_four INT(4) NOT NULL COMMENT 'Customer last four cc number',
                card_scheme VARCHAR(20) NOT NULL COMMENT 'Credit Card scheme',
                is_mada BIT NOT NULL DEFAULT 0 COMMENT 'Is mada card'
             ) ENGINE=InnoDB;
             ";

        $db = Db::getInstance();
        if (!$db->execute($sql))
            die('Error has occured while creating your table. Please try again.');
    }
}
