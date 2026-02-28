<?php

namespace SplitTestForElementor\Classes\Events;

use Elementor\Plugin;
use Elementor\Widget_Base;
use SplitTestForElementor\Classes\Misc\SettingsManager;
use SplitTestForElementor\Classes\Repo\TestRepo;
use SplitTestForElementor\Classes\Services\CacheBuster;

class WidgetRenderContentEvent {

	private static $settingsManager;
	private static $cacheBuster;

	/**
	 * WidgetRenderContentEvent constructor.
	 */
	public function __construct() {
		if (self::$settingsManager == null) {
			self::$settingsManager = new SettingsManager();
			self::$cacheBuster = new CacheBuster();
		}
	}

	public function fire($content, Widget_Base $element) {
		if (!$element->get_settings('split_test_control_test_id') || !$element->get_settings('split_test_control_variation_id')) {
			return $content;
		}

		return self::$cacheBuster->renderContent($content, $element);
	}

}