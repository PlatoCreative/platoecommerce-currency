<?php
/**
 * Exchange rates that can be set in {@link SiteConfig}. Several flat rates can be set
 * for any supported shipping country.
 */
class ExchangeRate extends DataObject implements PermissionProvider {

	/**
	 * Fields for this tax rate
	 *
	 * @var Array
	 */
	private static $db = array(
		'Title' => 'Varchar',
		'Currency' => 'Varchar(3)',
		'CurrencySymbol' => 'Varchar(10)',
		'Rate' => 'Decimal(19,4)',
		'BaseCurrency' => 'Varchar(3)',
		'BaseCurrencySymbol' => 'Varchar(10)',
		'SortOrder' => 'Int'
	);

	/**
	 * Exchange rates are associated with SiteConfigs.
	 *
	 * TODO The CTF in SiteConfig does not save the SiteConfig ID correctly so this is moot
	 *
	 * @var unknown_type
	 */
	private static $has_one = array(
		'ShopConfig' => 'ShopConfig'
	);

	private static $summary_fields = array(
		'Title' => 'Title',
		'CurrencySymbol' => 'Symbol',
		'Currency' => 'Currency',
		'BaseCurrency' => 'Base Currency',
		'Rate' => 'Rate'
	);

	private static $default_sort = 'SortOrder';

    public function providePermissions()
    {
        return array(
            'EDIT_CURRENCY' => 'Edit Currency',
        );
    }

    public function canEdit($member = null)
    {
        return Permission::check('EDIT_CURRENCY');
    }

    public function canView($member = null)
    {
        return true;
    }

    public function canDelete($member = null)
    {
        return Permission::check('EDIT_CURRENCY');
    }

    public function canCreate($member = null)
    {
        return Permission::check('EDIT_CURRENCY');
    }

	public function onBeforeWrite() {
		parent::onBeforeWrite();

		$shopConfig = ShopConfig::current_shop_config();
		$this->BaseCurrency = $shopConfig->BaseCurrency;
		$this->BaseCurrencySymbol = $shopConfig->BaseCurrencySymbol;
	}

	/**
	 * Field for editing a {@link ExchangeRate}.
	 *
	 * @return FieldSet
	 */
	public function getCMSFields() {

		$shopConfig = ShopConfig::current_shop_config();
		$baseCurrency = $shopConfig->BaseCurrency;

		return new FieldList(
			$rootTab = new TabSet('Root',
				$tabMain = new Tab('ExchangeRate',
					TextField::create('Title'),
					TextField::create('Currency', _t('ExchangeRate.CURRENCY', ' Currency'))
						->setRightTitle('3 letter currency code - <a href="http://en.wikipedia.org/wiki/ISO_4217#Active_codes" target="_blank">available codes</a>'),
					TextField::create('CurrencySymbol', _t('ExchangeRate.SYMBOL', 'Symbol'))
						->setRightTitle('Symbol to use for this currency'),
					NumericField::create('Rate', _t('ExchangeRate.RATE', 'Rate'))
						->setRightTitle("Rate to convert from $baseCurrency")
				)
			)
		);
	}

	public function getCMSValidator() {
		return new RequiredFields(array(
			'Title',
			'Currency',
			'Rate'
		));
	}

	public function validate() {

		$result = new ValidationResult();

		if (!$this->Title || !$this->Currency || !$this->Rate) {
			$result->error(
				'Rate is missing a required field',
				'ExchangeRateInvalidError'
			);
		}
		return $result;
	}

}
