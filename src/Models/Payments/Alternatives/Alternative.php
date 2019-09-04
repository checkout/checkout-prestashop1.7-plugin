<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Models\Payments\IdSource;
use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Models\Config;
use CheckoutCom\PrestaShop\Helpers\Utilities;
use CheckoutCom\PrestaShop\Models\Payments\Method;

abstract class Alternative extends Method {

	/**
	 * Load variabels
	 *
	 * @return     array
	 */
	public static function assign() {
		return array();
	}

	public static function pay(array $params) {




die('aquila');
	}






}