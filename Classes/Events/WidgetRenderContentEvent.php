<?php

namespace SplitTestForElementor\Classes\Events;

use Elementor\Widget_Base;
use SplitTestForElementor\Classes\Services\CacheBuster;

class WidgetRenderContentEvent {

	private static $cacheBuster;

	public function __construct() {
		if (self::$cacheBuster == null) {
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
