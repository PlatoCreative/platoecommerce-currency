<?php

class ExchangeRate_PageControllerExtension extends Extension {

	private static $allowed_actions = array(
		'CurrencyForm',
		'setCurrency'
	);

	public function CurrencyForm() {

		//Get the currencies
		$config = ShopConfig::current_shop_config();

		if ($config && $config->exists()) {
			$exchangeRates = $config->ExchangeRates();

			//If a rate does not exist for base currency
			if (!in_array($config->BaseCurrency, $exchangeRates->column('Currency'))) {
				Session::clear('SWS.Currency');
				return;
			}

			$currencies = array_combine($exchangeRates->column('Currency'), $exchangeRates->column('Title'));

			$currency = Session::get('SWS.Currency');
			if (!$currency) {
				$currency = $config->BaseCurrency;
			}

			$fields = FieldList::create(
				ExchangeRateField::create('Currency', ' ', $currencies, $currency)
			);

			$actions = FieldList::create(
				FormAction::create('setCurrency', _t('GridFieldDetailForm.Save', 'Save'))
			);

			return new Form(
				$this->owner,
				'CurrencyForm',
				$fields,
				$actions
			);
		}
	}

	public function setCurrency($data, $form) {

		$data = Convert::raw2sql($data);
		$currency = isset($data['Currency']) ? $data['Currency'] : null;

		$config = ShopConfig::current_shop_config();
		$exchangeRates = $config->ExchangeRates();
		$currencies = $exchangeRates->column('Currency');

		if (in_array($currency, $currencies)) {
			Session::set('SWS.Currency', $currency);
		}
		$this->owner->redirectBack();
	}
}
