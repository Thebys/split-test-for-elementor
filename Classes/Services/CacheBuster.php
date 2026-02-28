<?php

namespace SplitTestForElementor\Classes\Services;

use Elementor\Plugin;
use SplitTestForElementor\Classes\Misc\SettingsManager;
use SplitTestForElementor\Classes\Repo\TestRepo;
use SplitTestForElementor\Classes\Misc\Constants;

class CacheBuster
{

	private static $settingsManager;
	private static $testService;
	private static $conversionTrack;

	/**
	 * WidgetRenderContentEvent constructor.
	 */
	public function __construct() {
		if (self::$settingsManager == null) {
			self::$settingsManager = new SettingsManager();
			self::$testService = new TestService();
			self::$conversionTrack = new ConversionTracker();
		}
	}

	public function renderContent($content, $element) {
		if (Plugin::$instance->editor->is_edit_mode()) {
			return $content;
		}

		global $targetVariations;
		$testRepo = new TestRepo();

		$testId = $element->get_settings('split_test_control_test_id');
		if (!filter_var($testId, FILTER_VALIDATE_INT) || $testId == null) {
			return $content;
		}

		$test = $testRepo->getTest($testId);
		if ($test == null) {
			return $content;
		}

		$variationId = $element->get_settings('split_test_control_variation_id');

		if (self::$settingsManager->getRawValue(SettingsManager::CACHE_BUSTER_ACTIVE)) {
			$content = $this->RenderCacheBusterContentJs($content, $element, $test, $testId, $variationId);
		} else {
			$targetVariation = isset($targetVariations[$test->id]) ? $targetVariations[$test->id] : null;
			if ($targetVariation == null) {
				$targetVariation = self::$testService->getActiveVariation($test->id);
				$targetVariations[$test->id] = $targetVariation;

				$cookieName = "elementor_split_test_" . $test->id . "_variation";
				if (!isset($_COOKIE[$cookieName])) {
					$content = $content . $this->RenderSetCookieJs($cookieName, $targetVariation);
					$_COOKIE[$cookieName] = $targetVariation->id;

					$clientId = $_COOKIE[Constants::$SPLIT_TEST_CLIENT_ID_COOKIE];
					self::$conversionTrack->trackView($test->id, $targetVariation->id, $clientId);
				}
			}
			foreach ($test->variations as $variation) {
				if ($variation->id != $targetVariation->id && $variation->id == $variationId) {
					return $this->RenderElementHideCss($element);
				}
			}
		}

		return $content;
	}

	/**
	 * @param $cookieName
	 * @param $targetVariation
	 * @return false|string
	 */
	public function RenderSetCookieJs($cookieName, $targetVariation)
	{
		ob_start();
		?>
		<script type="text/javascript">
			try {
				window.rocketSplitTest.cookie.create("<?php echo($cookieName); ?>", <?php  echo($targetVariation->id); ?>, 365);
			} catch (e) {
				console.log(e);
			}
		</script>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * @param $content
	 * @param $element
	 * @param $testId
	 * @param $variationId
	 * @return false|string
	 */
	public function RenderCacheBusterContentJs($content, $element, $test, $testId, $variationId)
	{
		$placeholderId = "rocket-test-placeholder-" . rand(10000000, 99999999) . "-" . $element->get_id();
		ob_start();
		?>
		<div id="<?php echo($placeholderId); ?>"></div>
		<script type="text/javascript">
			try {
				window.rocketSplitTest.addTest(<?php echo(json_encode(self::$testService->getTestDataForJs($test))); ?>);
			} catch (e) {
				console.log(e);
			}
		</script>
		<script type="text/javascript">
			try {
				var content = decodeURIComponent(('<?php echo urlencode(str_replace(array("\r", "\n", "\t"), '', $content)); ?>').replace(/\+/g, " "));
                window.rocketSplitTest.maybeRenderTestVariant(<?php echo($testId); ?>, <?php echo($variationId); ?>, content, "<?php echo($placeholderId); ?>");
			} catch (e) {
				console.log(e);
			}
		</script>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * @param $element
	 * @return false|string
	 */
	public function RenderElementHideCss($element)
	{
		ob_start();
		?>
		<style>
            .elementor-element-<?php echo($element->get_id()); ?> {
                display: none !important;
                height: 0 !important;
            }
		</style>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

}