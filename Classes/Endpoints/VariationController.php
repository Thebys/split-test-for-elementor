<?php

namespace SplitTestForElementor\Classes\Endpoints;

use SplitTestForElementor\Classes\Misc\Errors;
use SplitTestForElementor\Classes\Repo\TestRepo;

class VariationController {

	public function store() {
		if(!current_user_can('publish_pages')) {
			return ['success' => false, 'errors' => [
				['key' => Errors::$MISSING_RIGHTS, 'message' => esc_html__( 'Could not save variation. Current user has insufficient rights.', 'split-test-for-elementor' )]
			]];
		}

		$testId = $_POST['testId'];
		$variationName = $_POST['name'];
		$variationPercentage = $_POST['percentage'];

        if (!is_numeric($testId) || !is_numeric($variationPercentage)) {
            return ['success' => false, 'errors' => [
                ['key' => Errors::$INVALID_INPUT, 'message' => esc_html__( 'Could not save variation. Invalid input.', 'split-test-for-elementor' )]
            ]];
        }

		$repo = new TestRepo();
		$newVariationId = $repo->createTestVariation($testId, [
			'name' => $variationName,
			'percentage' => $variationPercentage
		]);

		return ['success' => true, 'id' => $newVariationId, 'name' => $variationName];
	}

}
