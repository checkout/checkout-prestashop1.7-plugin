<?php


class Utilities {

	public static function getValueFromArray(array $arr, $field, $default = null) {

		return isset($arr[$field]) ? $arr[$field] : $default;

	}

	public static function getConfig($name) {

		return json_decode(static::getFile(CHECKOUTCOM_ROOT . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . $name . '.json'), true);

	}

	public static function getFile($path) {

		return is_readable($path) ? file_get_contents($path) : null;

	}



}
