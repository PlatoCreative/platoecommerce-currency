<?php

class ExchangeRateField extends DropdownField {

	public function FieldHolder($properties = array()) {

		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-entwine/dist/jquery.entwine-dist.js');
		Requirements::javascript('plato-ecommerce-currency/javascript/ExchangeRateField.js');

		return parent::FieldHolder();
	}

	public function Type() {
		return 'exchangerate';
	}
}

