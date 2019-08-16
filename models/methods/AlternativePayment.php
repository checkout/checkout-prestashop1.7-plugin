<?php


class AlternativePayment {


	const FILENAME = 'alternatives';

	protected static $index = array();
	protected static $definition = array();


	public static function getKeys() {

		static::load();
		return array_keys(static::$index);

	}



	protected static function load() {

		if(!static::$definition) {

			$data = json_decode(Utilities::getConfig(static::FILENAME), true);
			static::$definition = $data ? $data : array();

			static::$index = array();
			for($i = 0; $i < sizeof(static::$definition); $i++) {
				static::$index[static::$definition['name']] = $i;
			}

		}

	}

}