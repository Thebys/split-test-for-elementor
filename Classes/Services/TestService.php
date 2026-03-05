<?php

namespace SplitTestForElementor\Classes\Services;

use SplitTestForElementor\Classes\Misc\SettingsManager;
use SplitTestForElementor\Classes\Repo\PostTestManager;
use SplitTestForElementor\Classes\Repo\PostTestRepo;
use SplitTestForElementor\Classes\Repo\TestRepo;

class TestService
{

	private static $postTestManager;
	/**
	 * @var PostTestRepo
	 */
	private static $postTestRepo;
	private static $testRepo;
	private static $conversionTrack;
	private static $settingsManager;

	public function __construct() {
		if (self::$postTestManager == null) {
			self::$postTestManager = new PostTestManager();
			self::$testRepo = new TestRepo();
			self::$conversionTrack = new ConversionTracker();
			self::$postTestRepo = new PostTestRepo();
			self::$settingsManager = new SettingsManager();
		}
	}

	public function getActiveVariation($testId) {

		global $rocketSplitTestTests;

		$test = self::$testRepo->getTest($testId);

		if ($test == null) {
			return null;
		}

		$cookieName = "elementor_split_test_" . $test->id . "_variation";
		$targetVariation = null;

		$splitTestVariationId = null;
		if (isset($_COOKIE[$cookieName])) {
			$splitTestVariationId = $_COOKIE[$cookieName];
		}

		if (isset($_GET['stid']) && filter_var($_GET['stid'], FILTER_VALIDATE_INT)) {
			$splitTestVariationId = $_GET['stid'];
		}

		if ($splitTestVariationId != null)  {
			if (filter_var($splitTestVariationId, FILTER_VALIDATE_INT)) {
				foreach ($test->variations as $variation) {
					if ((int) $splitTestVariationId == $variation->id) {
						$targetVariation = $variation;
						break;
					}
				}
			}
		}

		if ($targetVariation == null) {
			$targetVariation = $this->getTargetVariation($test);
		}

		return $targetVariation;
	}

	public function normalizePercentages($variations) {
		$fullPercentageCount = 0;
		foreach ($variations as $variation) {
			$fullPercentageCount += (int) $variation->percentage;
		}

		foreach ($variations as $variation) {
			$variation->normalizedPercentage = $variation->percentage * 100 / $fullPercentageCount;
		}

		return $variations;
	}

	public function getTargetVariation($test)
	{
		$targetVariation = null;
		$variations = $this->normalizePercentages($test->variations);

		if (empty($variations)) {
			return null;
		}

		if (self::$settingsManager->getRawValue(SettingsManager::VARIANT_DISTRIBUTION_TYPE) === 'database') {
			global $wpdb;

			$table = $wpdb->prefix . 'elementor_splittest_interactions';

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$viewsAndConversions = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT COUNT(*) as count, variation_id FROM {$table} WHERE splittest_id = %d GROUP BY variation_id",
					$test->id
				)
			);

			if (sizeof($viewsAndConversions) > 0) {
				$lowestCount = PHP_INT_MAX;
				$targetVariation = $variations[0];

				foreach ($variations as $variation) {
					$viewsAndConversionCount = $this->getVariationViewsAndConversionsCountById($viewsAndConversions, $variation->id);

					$count = $viewsAndConversionCount * (100 - $variation->normalizedPercentage) / 100;
					if ($count < $lowestCount) {
						$targetVariation = $variation;
						$lowestCount = $count;
					}
				}
				return $targetVariation;
			}
		}

		// Use mt_rand with 10000 range for better precision with fractional percentages.
		$rnd = mt_rand(1, 10000);
		$counter = 0;

		foreach ($variations as $variation) {
			$counter += (int) round($variation->normalizedPercentage * 100);
			if ($rnd <= $counter) {
				$targetVariation = $variation;
				break;
			}
		}

		// Fallback for floating-point edge cases (e.g. rnd=10000, counter=9999).
		if ($targetVariation === null) {
			$targetVariation = end($variations);
		}

		return $targetVariation;
	}

    private function getVariationViewsAndConversionsCountById($viewsAndConversions, $variationId)
    {
        foreach ($viewsAndConversions as $viewsAndConversion) {
            if ($viewsAndConversion->variation_id == $variationId) {
                return intval($viewsAndConversion->count);
            }
        }

        return 0;
    }

}