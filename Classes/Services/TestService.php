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

		if (isset($_GET['stid'])) {
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


		if (self::$settingsManager->getRawValue(SettingsManager::VARIANT_DISTRIBUTION_TYPE) === 'database') {
			global $wpdb;

			$query = [];
			$query[] = "SELECT COUNT(*) as count, variation_id";
			$query[] = "FROM " . $wpdb->prefix . "elementor_splittest_interactions";
			$query[] = "WHERE splittest_id = " . $test->id . " GROUP BY variation_id";

			$viewsAndConversions = $wpdb->get_results(implode(" ", $query));

			if (sizeof($viewsAndConversions) > 0) {
				$lowestCount = $this->getVariationViewsAndConversionsCountById($viewsAndConversions, $variations[0]->id);
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

		$rnd = rand(1, 100);
		$counter = 0;

		foreach ($variations as $variation) {
			if ($rnd > $counter && $rnd <= $counter + (double)$variation->normalizedPercentage) {
				$targetVariation = $variation;
				break;
			} else {
				$counter += (double)$variation->normalizedPercentage;
			}
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

	public function getTestDataForJs($test)
	{
		// TODO@kberlau: Skip Page Tests
		$variations = [];
		foreach ($this->normalizePercentages($test->variations) as $variation) {
			$variations[] = [
				'id' => (int) $variation->id,
				'percentage' => $variation->normalizedPercentage,
			];
		}
		return [
			'id' => (int) $test->id,
			'variations' => $variations
		];
	}

}