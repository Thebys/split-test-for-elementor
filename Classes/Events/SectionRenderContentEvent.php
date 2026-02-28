<?php

namespace SplitTestForElementor\Classes\Events;

use SplitTestForElementor\Classes\Misc\SettingsManager;
use SplitTestForElementor\Classes\Services\CacheBuster;

class SectionRenderContentEvent {

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

}