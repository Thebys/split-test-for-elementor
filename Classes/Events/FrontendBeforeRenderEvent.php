<?php

namespace SplitTestForElementor\Classes\Events;

use \Elementor\Element_Base;
use Elementor\Element_Section;
use Elementor\Plugin;
use Elementor\Widget_Base;
use SplitTestForElementor\Classes\Misc\SettingsManager;
use SplitTestForElementor\Classes\Repo\TestRepo;
use SplitTestForElementor\Classes\Services\CacheBuster;
use SplitTestForElementor\Classes\Services\TestService;

class FrontendBeforeRenderEvent {

	private static $settingsManager;
	/**
	 * @var TestService
	 */
	private static $testService;
	/**
	 * @var TestRepo
	 */
	private static $testRepo;

	/**
	 * WidgetRenderContentEvent constructor.
	 */
	public function __construct() {
		if (self::$settingsManager == null) {
			self::$settingsManager = new SettingsManager();
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

//		if (!self::$settingsManager->getRawValue(SettingsManager::CACHE_BUSTER_ACTIVE)) {
//			if ($element instanceof Element_Section) {
//				$children = $element->get_children();
//				foreach ($children as $child) {
//					$this->setSplitTestSettingsOnChildren($child, $testId, $variationId);
//				}
//			}
//		}


		// TODO@kberlau JS Testing
		if (!self::$settingsManager->getRawValue(SettingsManager::CACHE_BUSTER_ACTIVE)) {

			$targetVariation = $targetVariations[$test->id];
			foreach ($test->variations as $variation) {
				if ($variation->id != $targetVariation->id && $variation->id == $variationId) {
					echo('<style> .elementor-split-test-' . $testId . '-variation-' . $variation->id . ' { display:none !important; height: 0 !important; } </style>');
				}
			}

			if ($targetVariation == null) {
				$targetVariation = self::$testService->getActiveVariation($test->id);
				$targetVariations[$test->id] = $targetVariation;

				$cookieName = "elementor_split_test_" . $test->id . "_variation";
				if (!isset($_COOKIE[$cookieName])) {
					echo (new CacheBuster())->RenderSetCookieJs($cookieName, $targetVariation);
					$_COOKIE[$cookieName] = $targetVariation->id;
				}
			}
		}

		if (self::$settingsManager->getRawValue(SettingsManager::CACHE_BUSTER_ACTIVE)) {
			$element->add_render_attribute('_wrapper', [
				'class' => 'elementor-split-test-hidden-'.$element->get_id()
			]);
			?>
				<script type="text/javascript">
					try {
						window.rocketSplitTest.addTest(<?php echo(json_encode(self::$testService->getTestDataForJs($test))); ?>);
					} catch (e) {
						console.log(e);
					}
				</script>
			<?php
		}

		$element->add_render_attribute('_wrapper', [
			'class' => 'elementor-split-test-'.$testId.'-variation-'.$variationId,
			'data-test-variation-id' => $variationId,
			'data-test-test-id' => $testId
		]);

	}

	/**
	 * @param Element_Base $child
	 * @param $testId
	 * @param $variationId
	 */
	public function setSplitTestSettingsOnChildren($child, $testId, $variationId) {
		$children = $child->get_children();
		if (sizeof($children) > 0) {
			foreach ($children as $child) {
				$this->setSplitTestSettingsOnChildren($child, $testId, $variationId);
			}
		}

		if ($child instanceof Widget_Base) {
			// LOW@kberlau: check if setting is set
			$child->set_settings('split_test_control_test_id', $testId);
			$child->set_settings('split_test_control_variation_id', $variationId);
		}
	}

}