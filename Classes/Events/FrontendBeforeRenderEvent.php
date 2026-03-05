<?php

namespace SplitTestForElementor\Classes\Events;

use \Elementor\Element_Base;
use Elementor\Plugin;
use SplitTestForElementor\Classes\Repo\TestRepo;
use SplitTestForElementor\Classes\Services\CacheBuster;
use SplitTestForElementor\Classes\Services\TestService;

class FrontendBeforeRenderEvent {

	/**
	 * @var TestService
	 */
	/**
	 * @var TestService
	 */
	private static $testService;
	/**
	 * @var TestRepo
	 */
	private static $testRepo;
	private static $noCacheHeaderSent = false;

	/**
	 * WidgetRenderContentEvent constructor.
	 */
	public function __construct() {
		if (self::$testService == null) {
			self::$testService = new TestService();
			self::$testRepo = new TestRepo();
		}
	}

	public function fire(Element_Base $element) {

		if (!$element->get_settings('split_test_control_test_id') || !$element->get_settings('split_test_control_variation_id')) {
			return;
		}

		if (Plugin::$instance->editor->is_edit_mode()) {
			return;
		}

		global $targetVariations;

		$testRepo = new TestRepo();

		$testId = $element->get_settings('split_test_control_test_id');
		if (!filter_var($testId, FILTER_VALIDATE_INT) || $testId == null) {
			return;
		}
		$variationId = $element->get_settings('split_test_control_variation_id');
		$test = $testRepo->getTests([$testId]);
		if (sizeof($test) == 0) {
			return;
		} else {
			$test = $test[0];
		}

		// Send no-cache headers when a split test is detected on the page.
		// This covers template-loaded tests that SendHeadersEvent can't detect.
		self::sendNoCacheHeaders();

		$targetVariation = $targetVariations[$test->id] ?? null;

		// Resolve variation BEFORE the hiding loop so display:none is never
		// emitted for the winning variant.  Fixes template-loaded tests where
		// SendHeadersEvent doesn't populate $targetVariations by page post ID.
		if ($targetVariation === null) {
			$targetVariation = self::$testService->getActiveVariation($test->id);
			$targetVariations[$test->id] = $targetVariation;

			$cookieName = "elementor_split_test_" . $test->id . "_variation";
			if (!isset($_COOKIE[$cookieName])) {
				echo (new CacheBuster())->RenderSetCookieJs($cookieName, $targetVariation);
				$_COOKIE[$cookieName] = $targetVariation->id;
			}
		}

		if ($targetVariation !== null) {
			foreach ($test->variations as $variation) {
				if ($variation->id != $targetVariation->id && $variation->id == $variationId) {
					echo('<style> .elementor-split-test-' . intval($testId) . '-variation-' . intval($variation->id) . ' { display:none !important; height: 0 !important; } </style>');
				}
			}
		}

		$element->add_render_attribute('_wrapper', [
			'class' => 'elementor-split-test-'.$testId.'-variation-'.$variationId,
			'data-test-variation-id' => $variationId,
			'data-test-test-id' => $testId
		]);

	}

	public static function sendNoCacheHeaders() {
		if (!self::$noCacheHeaderSent && !headers_sent()) {
			header('Cache-Control: no-store, private, no-cache, must-revalidate');
			header('Pragma: no-cache');
			self::$noCacheHeaderSent = true;
		}
	}

}
