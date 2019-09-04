<?php

namespace CheckoutCom\PrestaShop\Classes;

use Checkout\CheckoutApi;
use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Models\Config;
use CheckoutCom\PrestaShop\Helpers\Utilities;

class CheckoutApiHandler
{

	protected static $api = null;

	/**
	 * Initialize API.
	 */
	public static function init() {

		CheckoutApiHandler::$api = new CheckoutApi(	Config::get('CHECKOUTCOM_SECRET_KEY'),
													!Config::get('CHECKOUTCOM_LIVE_MODE'),
													Config::get('CHECKOUTCOM_PUBLIC_KEY'));

	}


	/**
	 * Access API.
	 *
	 * @return     CheckoutApi
	 */
	public static function api() {
		if(!static::$api) {
			static::init();
		}

		return static::$api;

	}

}