<?php

namespace SplitTestForElementor\Classes\Events;

use SplitTestForElementor\Classes\Misc\SettingsManager;
use SplitTestForElementor\Classes\Services\CacheBuster;

class SectionShouldRenderEvent
{

	/** @var CacheBuster */
	private $cacheBuster = null;
	private static $settingsManager;

	public function __construct() {
		$this->cacheBuster = new CacheBuster();

		if (self::$settingsManager == null) {
			self::$settingsManager = new SettingsManager();
		}

		global $globalRenderingSection;
		$globalRenderingSection = false;
	}


	public function fire($shouldRender, $element) {
		return true;
	}

}