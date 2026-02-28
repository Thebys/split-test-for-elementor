<?php

namespace SplitTestForElementor\Classes\Events;

use Elementor\Element_Base;
use SplitTestForElementor\Classes\Misc\SettingsManager;
use SplitTestForElementor\Classes\Repo\TestRepo;

class FrontendAfterRenderSectionEvent
{

	private static $settingsManager;
	private static $testRepo;

	/**
	 * WidgetRenderContentEvent constructor.
	 */
	public function __construct() {
		if (self::$settingsManager == null) {
			self::$settingsManager = new SettingsManager();
			self::$testRepo = new TestRepo();
		}
	}

	public function fire(Element_Base $element) {

		$testId = $element->get_settings('split_test_control_test_id');
		if (!filter_var($testId, FILTER_VALIDATE_INT) || $testId == null) {
			return;
		}
		$variationId = $element->get_settings('split_test_control_variation_id');
		if (!filter_var($variationId, FILTER_VALIDATE_INT) || $variationId == null) {
			return;
		}

		if (!self::$settingsManager->getRawValue(SettingsManager::CACHE_BUSTER_ACTIVE)) {
			return;
		}

		$class = 'elementor-split-test-hidden-'.$element->get_id();

		?>

		<script type="text/javascript">
			try {
				var sections = document.getElementsByClassName("<?php echo($class); ?>");
				if (sections.length > 0) {
					if (window.window.rocketSplitTest.isActiveVariation(<?php echo($testId); ?>, <?php echo($variationId); ?>)) {
						sections[0].className = sections[0].className.replace(/\b<?php echo($class); ?>\b/g, "");
					} else {
						sections[0].innerHTML = "";
						sections[0].parentNode.removeChild(sections[0]);
					}
				}
			} catch (e) {
				console.log(e);
			}
		</script>

		<?php
	}
}