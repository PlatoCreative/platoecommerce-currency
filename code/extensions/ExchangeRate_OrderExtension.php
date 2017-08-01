<?php

class ExchangeRate_OrderExtension extends DataExtension {

	private static $db = array(
		'Currency' => 'Varchar(3)',
		'CurrencySymbol' => 'Varchar(10)',
		'ExchangeRate' => 'Decimal(19,4)',
	);

	public function onBeforePayment() {

		//Set the currency for Order from the session
		if ($currency = Session::get('SWS.Currency')) {

			//Get the exchange rate, alter the amount
			$rate = ExchangeRate::get()
				->where("\"Currency\" = '$currency'")
				->limit(1)
				->first();

			if ($rate && $rate->exists()) {
				$this->owner->Currency = $rate->Currency;
				$this->owner->CurrencySymbol = $rate->CurrencySymbol;
				$this->owner->ExchangeRate = $rate->Rate;
			}
		}
		else { //Currency has not been set in the session, assume base currency
			$shopConfig = ShopConfig::current_shop_config();

			$this->owner->Currency = $shopConfig->BaseCurrency;
			$this->owner->CurrencySymbol = $shopConfig->BaseCurrencySymbol;
			$this->owner->ExchangeRate = 1.0; //1 to 1 exchange rate
		}
		$this->owner->write();
	}

	public function updatePrice($amount) {

		//Old orders that do not have the currency set, do not want to use session currency
		//Only if the order is not processed do we want to use the session currency

		//If the exchange rate is saved to the Order use that
		if ($this->owner->Status != 'Cart') {

			if ($this->owner->Currency && $this->owner->ExchangeRate) {
				$amount->setAmount($amount->getAmount() * $this->owner->ExchangeRate);
				$amount->setCurrency($this->owner->Currency);
				$amount->setSymbol($this->owner->CurrencySymbol);
			}
		}
		else if ($currency = Session::get('SWS.Currency')) {

			//Get the exchange rate, alter the amount
			$rate = ExchangeRate::get()
				->where("\"Currency\" = '$currency'")
				->limit(1)
				->first();

			if ($rate && $rate->exists()) {
				$amount->setAmount($amount->getAmount() * $rate->Rate);
				$amount->setCurrency($rate->Currency);
				$amount->setSymbol($rate->CurrencySymbol);
			}
		}
	}
}
