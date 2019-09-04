<?php

namespace CheckoutCom\PrestaShop\Models;

use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Helpers\Utilities;

class Config extends \Configuration{

	/**
     * Path location of configurations.
     *
     * @var        string
     */
    const CHECKOUTCOM_CONFIGS = CHECKOUTCOM_ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;


    /**
     * Save configutation.
     *
     * @var        array
     */
	static $configs = array();

	/**
	 * Setup module variables.
	 */
	public static function install() {
		static::load();
Debug::write('Config.install()');

		foreach(static::defaults() as $key => $value) {
			\Configuration::updateValue($key, $value);
		}

		//include(dirname(__FILE__).'/sql/install.php');

	}

	/**
	 * Clear module variables.
	 */
	public static function uninstall() {
		static::load();
Debug::write('Config.uninstall()');

		foreach(static::keys() as $key) {
			\Configuration::deleteByName($key);
		}

		//include(dirname(__FILE__).'/sql/uninstall.php');

	}

	/**
	 * Load settings to memory.
	 */
	public static function load() {
Debug::write('Config.loadConfigs()');

		if(!static::$configs) {
Debug::write('Config.loadConfigs().$configs is empty');
			$files = scandir(static::CHECKOUTCOM_CONFIGS);
			foreach ($files as $file) {
	            if(strpos($file, '.json') !== false) {
	            	$filename = basename($file, '.json');
					Config::$configs[$filename] = Utilities::getConfig($filename);
	            }
	        }

	    }

	}

	/**
	 * Get values from settings.
	 *
	 * @param      string  $name   The name
	 *
	 * @return     array
	 */
	public static function values($name = '') {
		static::load();
Debug::write('Config.values('.$name.')');

		$forms = array();
		$fields = array();

		if($name) {
Debug::write('Config.values().' . $name);
			$forms = static::$configs[$name];
		} else {
Debug::write('Config.values().null');
			foreach (static::$configs as $key => $configuration) {
				$forms = array_merge($forms, $configuration);
			}
		}

		foreach ($forms as $form) {
			foreach ($form as $field) {
Debug::write($field['name'] . ' -> ' . \Configuration::get($field['name'], Utilities::getValueFromArray($field, 'default')));
				$fields[$field['name']] = \Configuration::get($field['name'], Utilities::getValueFromArray($field, 'default'));
			}
		}

		return $fields;

	}

	/**
	 * Get defaults of the settings.
	 *
	 * @param      string  $name   The name
	 *
	 * @return     array
	 */
	public static function defaults($name = '') {
		static::load();
Debug::write('Config.defaults('.$name.')');

		$forms = array();
		$fields = array();

		if($name) {
Debug::write('Config.defaults().' . $name);
			$forms = static::$configs[$name];
		} else {
Debug::write('Config.defaults().null');
			foreach (static::$configs as $key => $configuration) {
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
	 * @param      string  $name   The name
	 *
	 * @return     array
	 */
	public static function keys($name = '') {
		static::load();
Debug::write('Config.defaults('.$name.')');

		$forms = array();
		$keys = array();

		if($name) {
Debug::write('Config.keys().' . $name);
			$forms = static::$configs[$name];
		} else {
Debug::write('Config.keus().null');
			foreach (static::$configs as $key => $configuration) {
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
	 * @return     array
	 */
	public static function definition($name = '') {
		static::load();

		if($name) {
Debug::write('Config.definition().' . $name);
			return static::$configs[$name];
		} else {
Debug::write('Config.definition().null');
			return static::$configs;
		}

	}


	/**
	 * Helper methods.
	 */

	/**
     * Determines if the payment method needs auto capture.
     *
     * @return bool
     */
    public static function needsAutoCapture()
    {
    	return (static::get('CHECKOUTCOM_PAYMENT_ACTION') || static::get('CHECKOUTCOM_CARD_MADA_CHECK_ENABLED'));
    }

}