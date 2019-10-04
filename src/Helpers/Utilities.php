<?php

namespace CheckoutCom\PrestaShop\Helpers;

use CheckoutCom\PrestaShop\Models\Config;

class Utilities {

	/**
	 * Gets the value from array.
	 *
	 * @param      array   $arr      The arr
	 * @param      <type>  $field    The field
	 * @param      <type>  $default  The default
	 *
	 * @return     <type>  The value from array.
	 */
	public static function getValueFromArray(array $arr, $field, $default = null) {

		return isset($arr[$field]) ? $arr[$field] : $default;

	}

	/**
	 * Gets the configuration.
	 *
	 * @param      string  $name   The name
	 *
	 * @return     <type>  The configuration.
	 */
	public static function getConfig($name) {

		return json_decode(static::getFile(Config::CHECKOUTCOM_CONFIGS . $name . '.json'), true);

	}

	/**
	 * Gets the file.
	 *
	 * @param      string  $path   The path
	 *
	 * @return     <type>  The file.
	 */
	public static function getFile($path) {

		return is_readable($path) ? file_get_contents($path) : null;

	}

	/**
	 * Format timestamp to gateway-like format.
	 *
	 * @param      integer  $timestamp  The timestamp
	 *
	 * @return     string
	 */
    public function formatDate($timestamp)
    {
        return gmdate("Y-m-d\TH:i:s\Z", $timestamp);
    }

}
