<?php

namespace SplitTestForElementor\Classes\Misc;

class SettingsManager {

	const PREFIX = "rocket_split_test";
	const CACHE_BUSTER_ACTIVE = "cache_buster_active";
	const VARIANT_DISTRIBUTION_TYPE = "variant_distribution_type";

	private static $config = [
		SettingsManager::CACHE_BUSTER_ACTIVE => ['default' => false, 'type' => 'boolean'],
		SettingsManager::VARIANT_DISTRIBUTION_TYPE => ['default' => 'random', 'type' => 'string'],
	];

	/** @var LicenceManager */
	private $licenceManager;

	public function __construct()
	{
		$this->licenceManager = new LicenceManager();
	}
	public function getValue($key) {
		return esc_attr($this->getRawValue($key));
	}

	public function registerSettings() {
		add_option( self::PREFIX."_".self::CACHE_BUSTER_ACTIVE, self::$config[self::CACHE_BUSTER_ACTIVE]['default']);
		register_setting( 'split_test_for_elementor_options_group', self::PREFIX."_".self::CACHE_BUSTER_ACTIVE, ['type' => self::$config[self::CACHE_BUSTER_ACTIVE]['type']]);

		add_option( self::PREFIX."_".self::VARIANT_DISTRIBUTION_TYPE, self::$config[self::VARIANT_DISTRIBUTION_TYPE]['default']);
		register_setting( 'split_test_for_elementor_options_group', self::PREFIX."_".self::VARIANT_DISTRIBUTION_TYPE, ['type' => self::$config[self::VARIANT_DISTRIBUTION_TYPE]['type']]);
	}

    public function setValue($key, $value) {
        $fullKey = self::PREFIX."_".$key;
        update_option($fullKey, $value);
    }

	public function getRawValue($key) {
		if (!isset(self::$config[$key])) {
			// TODO@kberlau: return false if boolean
			return null;
		}
		// TODO@kberlau: Return defaults if pro is not active
		$value = get_option(self::PREFIX."_".$key, null);
		if ($value == null || empty(trim($value))) {
			return self::$config[$key]['default'];
		}
		return $value;
	}

}