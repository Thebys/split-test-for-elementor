<?php

namespace SplitTestForElementor\Classes\Misc;

use SplitTestForElementor\Classes\Repo\TestRepo;

class LicenceManager {

	private $hasActiveProLicence;

	/**
	 * LicenceManager constructor.
	 *
	 */
	public function __construct() {
		$this->hasActiveProLicence = get_option("split_test_for_elementor_pro_version_license", "invalid") == "valid" ? true : false;
		// LOW@kberlau: remove this in Version 1.2.1
		if (!defined('SPLIT_TEST_FOR_ELEMENTOR_PRO_VERSION')) {
			define('SPLIT_TEST_FOR_ELEMENTOR_PRO_VERSION', $this->hasActiveProLicence);
		}
	}

	public function hasActiveProLicence() {
		return $this->hasActiveProLicence;
	}

	public function isLiteTestCountReached() {
		$testRepo = new TestRepo();
		$tests = $testRepo->getAllTests();
		if (!$this->hasActiveProLicence && sizeof($tests) >= SPLIT_TEST_FOR_ELEMENTOR_LITE_MAX_TEST_COUNT) {
			return true;
		} else {
			return false;
		}
	}

	public function isLiteVariationCountReached($testId) {
		$testRepo = new TestRepo();
		$test = $testRepo->getTest($testId);
		if (!$this->hasActiveProLicence && sizeof($test->variations) >= SPLIT_TEST_FOR_ELEMENTOR_LITE_MAX_VARIATION_COUNT) {
			return true;
		} else {
			return false;
		}
	}

}