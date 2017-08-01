<?php

/**
 * So that {@link ExchangeRate}s can be created in {@link SiteConfig}.
 */
class ExchangeRate_ShopConfigExtension extends DataExtension {

	/**
	 * Attach {@link ExchangeRate}s to {@link SiteConfig}.
	 *
	 * @see DataObjectDecorator::extraStatics()
	 */
	private static $has_many = array(
		'ExchangeRates' => 'ExchangeRate'
	);

}
