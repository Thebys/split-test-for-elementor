<?php

namespace SplitTestForElementor\Classes\Endpoints;

use SplitTestForElementor\Classes\Misc\Errors;
use SplitTestForElementor\Classes\Repo\TestRepo;
use SplitTestForElementor\Classes\Services\TestService;

class TestController {

    private static $testService;

	public function __construct() {
		if (self::$testService == null) {
            self::$testService = new TestService();
		}
	}

	public function store() {
		if(!current_user_can('publish_pages')) {
			return ['success' => false, 'errors' => [
				['key' => Errors::$MISSING_RIGHTS, 'message' => esc_html__( 'Could not save test. Current user has insufficient rights.', 'split-test-for-elementor' )]
			]];
		}

		$testName = $_POST['name'];
		if (!isset($_POST['conversionType'])) {
			return ['success' => false, 'errors' => [
				['key' => Errors::$CONVERSION_TYPE_MISSING, 'message' => esc_html__( 'Could not save test. Conversion type missing.', 'split-test-for-elementor' )]
			]];
		}

		if ($_POST['conversionType'] == "page") {
			if (!isset($_POST['conversionPageId']) || $_POST['conversionPageId'] == null || $_POST['conversionPageId'] == "" || $_POST['conversionPageId'] == "null") {
				return ['success' => false, 'errors' => [
					[
						'key' => Errors::$CONVERSION_PAGE_MISSING,
						'message' => esc_html__( 'Could not save test. Conversion page missing.', 'split-test-for-elementor' )
					]
				]];
			}
		}

		if ($_POST['conversionType'] == "url") {
			if (!isset($_POST['conversionUrl']) || $_POST['conversionUrl'] == null || $_POST['conversionUrl'] == "" || $_POST['conversionUrl'] == "null") {
				return ['success' => false, 'errors' => [
					[
						'key' => Errors::$CONVERSION_URL_MISSING,
						'message' => esc_html__( 'Could not save test. Conversion url missing.', 'split-test-for-elementor' )
					]
				]];
			}
		}

		$repo = new TestRepo();
		$newTestId = $repo->createTest([
			'name' => $testName,
			'testType' => 'elements',
			'conversionType' => $_POST['conversionType'],
			'conversionPageId' => (int) $_POST['conversionPageId'],
			'conversionUrl' => $_POST['conversionUrl']
		]);

		return ['success' => true, 'id' => $newTestId, 'name' => $testName];
	}

    public function getVariationToDisplay() {

        if (!is_numeric($_GET['testId'])) {
            return [];
        }

        $testId = intval($_GET['testId']);
        $result = self::$testService->getActiveVariation($testId);

        return [
            'variant' => [
                'id' => intval($result->id)
            ]
        ];

    }

}
