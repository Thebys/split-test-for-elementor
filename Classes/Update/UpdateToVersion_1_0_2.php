<?php

namespace SplitTestForElementor\Classes\Update;

class UpdateToVersion_1_0_2 {

	public function run() {

		global $wpdb;

		$pages = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."postmeta WHERE meta_key = '_elementor_data'", OBJECT);
		foreach ($pages as $page) {
			$pageElements = json_decode($page->meta_value, true);
			for ($i = 0; $i < sizeof($pageElements); $i++) {
				$element = $pageElements[$i];

				if ($element['elType'] == "section") {
					if (isset($element['settings']['split_test_control'])) {
						if ($element['settings']['split_test_control'] == 0) {
							unset($element['settings']['split_test_control']);
							$pageElements[$i] = $element;
							continue;
						}

						$testId = $element['settings']['split_test_control'];
						unset($element['settings']['split_test_control']);
						if (!isset($element['settings']['split_test_variation_control_'.$testId])) {
							$pageElements[$i] = $element;
							continue;
						}
						$variationId = $element['settings']['split_test_variation_control_'.$testId];
						unset( $element['settings']['split_test_variation_control_'.$testId]);

						$element['settings']['split_test_control_test_id'] = $testId;
						$element['settings']['split_test_control_variation_id'] = $variationId;
					}
				}

				$pageElements[$i] = $element;
			}

			$wpdb->update(
				$wpdb->prefix."postmeta",
				['meta_value' => json_encode($pageElements)],
				['meta_id' => $page->meta_id],
				['%s'],
				['%d']
			);

		}

	}

}