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
		global $globalRenderingSection;

		return true;

		if (!self::$settingsManager->getRawValue(SettingsManager::CACHE_BUSTER_ACTIVE)) {
			return true;
		}

		if ($element->get_name() != "section") {
			return true;
		}

		if ($globalRenderingSection) {
			return true;
		}

		$elementorBeforeRenderHooks = $GLOBALS['wp_filter']['elementor/frontend/before_render'];
		unset($GLOBALS['wp_filter']['elementor/frontend/before_render']);
		$elementorSectionBeforeRenderHooks = $GLOBALS['wp_filter']['elementor/frontend/section/before_render'];
		unset($GLOBALS['wp_filter']['elementor/frontend/section/before_render']);

		$globalRenderingSection = true;
		ob_start();
		$element->print_element();
		$content = ob_get_contents();
		ob_end_clean();

		$GLOBALS['wp_filter']['elementor/frontend/before_render'] = $elementorBeforeRenderHooks;
		$GLOBALS['wp_filter']['elementor/frontend/section/before_render'] = $elementorSectionBeforeRenderHooks;
		$globalRenderingSection = false;

		echo $this->cacheBuster->renderContent($content, $element);

		return false;
	}

}