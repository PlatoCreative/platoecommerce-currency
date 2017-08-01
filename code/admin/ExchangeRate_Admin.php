<?php

class ExchangeRate_Admin extends ShopAdmin {

	private static $tree_class = 'ShopConfig';

	private static $allowed_actions = array(
		'ExchangeRateSettings',
		'ExchangeRateSettingsForm',
		'saveExchangeRateSettings'
	);

	private static $url_rule = 'ShopConfig/ExchangeRate';
	protected static $url_priority = 150;
	private static $menu_title = 'Shop Exchange Rates';

	private static $url_handlers = array(
		'ShopConfig/ExchangeRate/ExchangeRateSettingsForm' => 'ExchangeRateSettingsForm',
		'ShopConfig/ExchangeRate' => 'ExchangeRateSettings'
	);

	public function init() {
		parent::init();
		if (!in_array(get_class($this), self::$hidden_sections)) {
			$this->modelClass = 'ShopConfig';
		}
	}

	public function Breadcrumbs($unlinked = false) {

		$request = $this->getRequest();
		$items = parent::Breadcrumbs($unlinked);

		if ($items->count() > 1) $items->remove($items->pop());

		$items->push(new ArrayData(array(
			'Title' => 'Exchange Rate Settings',
			'Link' => $this->Link(Controller::join_links($this->sanitiseClassName($this->modelClass), 'ExchangeRate'))
		)));

		return $items;
	}

	public function SettingsForm($request = null) {
		return $this->ExchangeRateSettingsForm();
	}

	public function ExchangeRateSettings($request) {

		if ($request->isAjax()) {
			$controller = $this;
			$responseNegotiator = new PjaxResponseNegotiator(
				array(
					'CurrentForm' => function() use(&$controller) {
						return $controller->ExchangeRateSettingsForm()->forTemplate();
					},
					'Content' => function() use(&$controller) {
						return $controller->renderWith('ShopAdminSettings_Content');
					},
					'Breadcrumbs' => function() use (&$controller) {
						return $controller->renderWith('CMSBreadcrumbs');
					},
					'default' => function() use(&$controller) {
						return $controller->renderWith($controller->getViewer('show'));
					}
				),
				$this->response
			);
			return $responseNegotiator->respond($this->getRequest());
		}

		return $this->renderWith('ShopAdminSettings');
	}

	public function ExchangeRateSettingsForm() {

		$shopConfig = ShopConfig::get()->First();

		if(singleton($this->modelClass)->hasMethod('getCMSValidator')) {
			$detailValidator = singleton($this->modelClass)->getCMSValidator();
			$listField->getConfig()->getComponentByType('GridFieldDetailForm')->setValidator($detailValidator);
		}

		$config = GridFieldConfig_HasManyRelationEditor::create();
		$detailForm = $config->getComponentByType('GridFieldDetailForm')->setValidator(
			singleton('ExchangeRate')->getCMSValidator()
		);
		if (class_exists('GridFieldSortableRows')) {
			$config->addComponent(new GridFieldSortableRows('SortOrder'));
		}

		$fields = new FieldList(
			$rootTab = new TabSet('Root',
				$tabMain = new Tab('ExchangeRates',
					GridField::create(
						'ExchangeRates',
						'ExchangeRates',
						$shopConfig->ExchangeRates(),
						$config
					)
				)
			)
		);

		$actions = new FieldList();
		$actions->push(FormAction::create('saveExchangeRateSettings', _t('GridFieldDetailForm.Save', 'Save'))
			->setUseButtonTag(true)
			->addExtraClass('ss-ui-action-constructive')
			->setAttribute('data-icon', 'add'));

		$form = new Form(
			$this,
			'EditForm',
			$fields,
			$actions
		);

		$form->setTemplate('ShopAdminSettings_EditForm');
		$form->setAttribute('data-pjax-fragment', 'CurrentForm');
		$form->addExtraClass('cms-content cms-edit-form center ss-tabset');
		if($form->Fields()->hasTabset()) $form->Fields()->findOrMakeTab('Root')->setTemplate('CMSTabSet');
		$form->setFormAction(Controller::join_links($this->Link($this->sanitiseClassName($this->modelClass)), 'ExchangeRate/ExchangeRateSettingsForm'));

		$form->loadDataFrom($shopConfig);

		return $form;
	}

	public function saveExchangeRateSettings($data, $form) {

		//Hack for LeftAndMain::getRecord()
		self::$tree_class = 'ShopConfig';

		$config = ShopConfig::get()->First();
		$form->saveInto($config);
		$config->write();
		$form->sessionMessage('Saved Exchange Rate Settings', 'good');

		$controller = $this;
		$responseNegotiator = new PjaxResponseNegotiator(
			array(
				'CurrentForm' => function() use(&$controller) {
					//return $controller->renderWith('ShopAdminSettings_Content');
					return $controller->ExchangeRateSettingsForm()->forTemplate();
				},
				'Content' => function() use(&$controller) {
					//return $controller->renderWith($controller->getTemplatesWithSuffix('_Content'));
				},
				'Breadcrumbs' => function() use (&$controller) {
					return $controller->renderWith('CMSBreadcrumbs');
				},
				'default' => function() use(&$controller) {
					return $controller->renderWith($controller->getViewer('show'));
				}
			),
			$this->response
		);
		return $responseNegotiator->respond($this->getRequest());
	}

	public function getSnippet() {

		if (!$member = Member::currentUser()) return false;
		if (!Permission::check('CMS_ACCESS_' . get_class($this), 'any', $member)) return false;

		return $this->customise(array(
			'Title' => 'Exchange Rates Management',
			'Help' => 'Create exchange rates',
			'Link' => Controller::join_links($this->Link('ShopConfig'), 'ExchangeRate'),
			'LinkTitle' => 'Edit exchange rates'
		))->renderWith('ShopAdmin_Snippet');
	}

}
