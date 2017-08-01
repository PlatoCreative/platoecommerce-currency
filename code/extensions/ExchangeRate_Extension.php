<?php

class ExchangeRate_Extension extends DataExtension {

	public function updatePrice($amount) {

		if ($currency = Session::get('SWS.Currency')) {

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
