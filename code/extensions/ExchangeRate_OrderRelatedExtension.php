<?php

class ExchangeRate_OrderRelatedExtension extends DataExtension {

	public function updatePrice($amount) {

		//If the order is processed and the currency saved, use that
		//If the order is processed and no currency saved, do nothing
		//If the order is not processed and the currency in Session, use that

		$order = $this->owner->Order();

		if ($order->Status != 'Cart') {

			if ($order->Currency && $order->ExchangeRate) {
				$amount->setAmount($amount->getAmount() * $order->ExchangeRate);
				$amount->setCurrency($order->Currency);
				$amount->setSymbol($order->CurrencySymbol);
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
