<?php


class AlternativePayment {


	const FILENAME = 'alternatives';


	protected static $definition = array();


	public static function getKeys() {

		if(!static::$definition) {
			static::$definition = static::load();
		}


		foreach ($variable as $key => $value) {
			# code...
		}


	}



	protected static function load() {

		return json_decode(Utilities::getConfig(static::FILENAME), true);

	}



}