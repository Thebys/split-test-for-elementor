<?php

namespace SplitTestForElementor\Classes\Endpoints;

use SplitTestForElementor\Classes\Misc\Constants;
use SplitTestForElementor\Classes\Misc\Errors;
use SplitTestForElementor\Classes\Misc\LicenceManager;
use SplitTestForElementor\Classes\Repo\TestRepo;
use SplitTestForElementor\Classes\Services\TestService;

class TestController {

	private static $licenceManager;
    private static $testService;

	/**
	 * TestController constructor.
	 */
	public function __construct() {
		if (self::$licenceManager == null) {
			self::$licenceManager = new LicenceManager();
            self::$testService = new TestService();
		}
	}

	public function index() {

	}

	public function create() {

	}

	public function store() {
		if(!current_user_can('publish_pages')) {
			return ['success' => false, 'errors' => [
				['key' => Errors::$MISSING_RIGHTS, 'message' => esc_html__( 'Could not save test. Current user has insufficient rights.', 'split-test-for-elementor' )]
			]];
		}

		//TODO@kberlau: Validate input
		$testName = $_POST['name'];
		if (!isset($_POST['conversionType'])) {
			return ['success' => false, 'errors' => [
				['key' => Errors::$CONVERSION_TYPE_MISSING, 'message' => esc_html__( 'Could not save test. Conversion type missing.', 'split-test-for-elementor' )]
			]];
		}

		if (self::$licenceManager->isLiteTestCountReached()) {
			return ['success' => false, 'errors' => [
				[
					'key' => Errors::$MAXIMUM_TEST_COUNT_REACHED,
					'message' => esc_html__( 'Could not save test. Maximum test count for pro version reached. Please buy licence.', 'split-test-for-elementor' ),
					'payload' => ['link' => SPLIT_TEST_FOR_ELEMENTOR_PRO_VERSION_LINK]
				]
			]];
		}

		if ($_POST['conversionType'] == "page") {
			if (!isset($_POST['conversionPageId']) || $_POST['conversionPageId'] == null || $_POST['conversionPageId'] == "" || $_POST['conversionPageId'] == "null") {
				return ['success' => false, 'errors' => [
					[
						'key' => Errors::$CONVERSION_PAGE_MISSING,
						'message' => esc_html__( 'Could not save test. Conversion page missing.', 'split-test-for-elementor' ),
						'payload' => ['link' => SPLIT_TEST_FOR_ELEMENTOR_PRO_VERSION_LINK]
					]
				]];
			}
		}

		if ($_POST['conversionType'] == "url") {
			if (!isset($_POST['conversionUrl']) || $_POST['conversionUrl'] == null || $_POST['conversionUrl'] == "" || $_POST['conversionUrl'] == "null") {
				return ['success' => false, 'errors' => [
					[
						'key' => Errors::$CONVERSION_URL_MISSING,
						'message' => esc_html__( 'Could not save test. Conversion url missing.', 'split-test-for-elementor' ),
						'payload' => ['link' => SPLIT_TEST_FOR_ELEMENTOR_PRO_VERSION_LINK]
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

	public function show() {

	}

	public function edit() {

	}

	public function update() {

	}

	public function delete() {

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