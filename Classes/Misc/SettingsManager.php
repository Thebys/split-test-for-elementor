<?php

namespace SplitTestForElementor\Classes\Misc;

class SettingsManager {

	const PREFIX = "rocket_split_test";
	const VARIANT_DISTRIBUTION_TYPE = "variant_distribution_type";

	private static $config = [
		SettingsManager::VARIANT_DISTRIBUTION_TYPE => ['default' => 'random', 'type' => 'string'],
	];

	public function getValue($key) {
		return esc_attr($this->getRawValue($key));
	}

	public function registerSettings() {
		add_option( self::PREFIX."_".self::VARIANT_DISTRIBUTION_TYPE, self::$config[self::VARIANT_DISTRIBUTION_TYPE]['default']);
		register_setting( 'split_test_for_elementor_options_group', self::PREFIX."_".self::VARIANT_DISTRIBUTION_TYPE, ['type' => self::$config[self::VARIANT_DISTRIBUTION_TYPE]['type']]);
	}

    public function setValue($key, $value) {
        $fullKey = self::PREFIX."_".$key;
        update_option($fullKey, $value);
    }

	public function getRawValue($key) {
		if (!isset(self::$config[$key])) {
			return null;
		}
		$value = get_option(self::PREFIX."_".$key, null);
		if ($value == null || empty(trim($value))) {
			return self::$config[$key]['default'];
		}
		return $value;
	}

}
