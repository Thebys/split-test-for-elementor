<?php

namespace SplitTestForElementor\Classes\Endpoints;

use SplitTestForElementor\Classes\Misc\Errors;
use SplitTestForElementor\Classes\Misc\LicenceManager;
use SplitTestForElementor\Classes\Repo\TestRepo;

class VariationController {

	private static $licenceManager;

	/**
	 * TestController constructor.
	 */
	public function __construct() {
		if (self::$licenceManager == null) {
			self::$licenceManager = new LicenceManager();
		}
	}

	public function index() {

	}

	public function create() {

	}

	public function store() {
		if(!current_user_can('publish_pages')) {
			return ['success' => false, 'errors' => [
				['key' => Errors::$MISSING_RIGHTS, 'message' => esc_html__( 'Could not save variation. Current user has insufficient rights.', 'split-test-for-elementor' )]
			]];
		}

		//TODO@kberlau: Validate input
		$testId = $_POST['testId'];
		$variationName = $_POST['name'];
		$variationPercentage = $_POST['percentage'];

        if (!is_numeric($testId) || !is_numeric($variationPercentage)) {
            return ['success' => false, 'errors' => [
                ['key' => Errors::$INVALID_INPUT, 'message' => esc_html__( 'Could not save variation. Invalid input.', 'split-test-for-elementor' )]
            ]];
        }

		if (self::$licenceManager->isLiteVariationCountReached($testId)) {
			return ['success' => false, 'errors' => [
				[
					'key' => Errors::$MAXIMUM_VARIATION_COUNT_REACHED,
					'message' => esc_html__( 'Could not save test. Maximum variation count for pro version reached. Please buy licence.', 'split-test-for-elementor' ),
					'payload' => ['link' => SPLIT_TEST_FOR_ELEMENTOR_PRO_VERSION_LINK]
				]
			]];
		}

		$repo = new TestRepo();
		$newVariationId = $repo->createTestVariation($testId, [
			'name' => $variationName,
			'percentage' => $variationPercentage
		]);

		return ['success' => true, 'id' => $newVariationId, 'name' => $variationName];
	}

	public function show() {

	}

	public function edit() {

	}

	public function update() {

	}

	public function delete() {

	}

}