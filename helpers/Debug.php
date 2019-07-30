<?php

class Debug {

	/**
	 * Name of the target file.
	 *
	 * @var        string
	 */
	const FILENAME = 'checkoutcom.log';

	/**
	 * Path of the target file.
	 *
	 * @var        string
	 */
	const PATH = _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;

	/**
	 * Append to log file.
	 *
	 * @param      mixed  $data   The data
	 */
	public static function write($data) {

		file_put_contents(static::PATH . static::FILENAME, print_r($data, 1) . "\n", FILE_APPEND);

	}

}