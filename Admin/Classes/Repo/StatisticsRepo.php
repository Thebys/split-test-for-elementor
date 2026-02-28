<?php

namespace SplitTestForElementor\Admin\Classes\Repo;

use SplitTestForElementor\Classes\Repo\TestRepo;

class StatisticsRepo {

	// LOW@kberlau Runs only under mysql???

	public function getStats($testId, $startDate = null, $endDate = null) {

		if ($startDate == null) {
			$startDate = time() - 90 * 24 * 60 * 60;
		} else {
			$startDate = strtotime($startDate);
		}
		if ($endDate == null) {
			$endDate = time();
		} else {
			$endDate = strtotime($endDate);
		}

		global $wpdb;

		$testRepo = new TestRepo();
		$variations = $testRepo->getVariations($testId);
		$variationsById = [];

		foreach ($variations as $key => $variation) {
			$variation->stats = $this->generateEmptyStats($startDate, $endDate);
			$variationsById[$variation->id] = $variation;
		}

		if (!filter_var($testId, FILTER_VALIDATE_INT)) {
			// LOW@kberlau Log / Show error
			return $variations;
		}

		// LOW use prepared statement
		$query = [];
		$query[] = "SELECT COUNT(*) as count, type, variation_id, Concat(YEAR(created_at), '-', MONTH(created_at), '-', DAY(created_at)) as date, ";
		$query[] = "YEAR(created_at) as dateYear, MONTH(created_at) as dateMonth, DAY(created_at) as dateDay";
		$query[] = "FROM ".$wpdb->prefix."elementor_splittest_interactions";
		$query[] = "WHERE splittest_id = ".$testId." GROUP BY type, variation_id, date";
		$query[] = "ORDER BY variation_id, date";

		$results = $wpdb->get_results(implode(" ", $query));

		foreach ($results as $result) {
			$dateString = $result->dateYear."-".str_pad($result->dateMonth, 2, "0", STR_PAD_LEFT)."-".str_pad($result->dateDay, 2, "0", STR_PAD_LEFT);

			if (!isset($variationsById[$result->variation_id])) {
				continue;
			}
			$variation = $variationsById[$result->variation_id];

			if (!isset($variation->stats['dates'][$dateString])) {
				continue;
			}

			if ($result->type == "view") {
				$variation->stats['dates'][$dateString]['views'] += (int) $result->count;
				$variation->stats['allViews'] += (int) $result->count;
			} else if ($result->type == "conversion") {
				$variation->stats['dates'][$dateString]['views'] += (int) $result->count;
				$variation->stats['allViews'] += (int) $result->count;
				$variation->stats['dates'][$dateString]['conversions'] += (int) $result->count;
				$variation->stats['allConversions'] += (int) $result->count;
			}
		}

		return $variations;
	}

	private function generateEmptyStats($startDate, $endDate) {
		$calcDate = $startDate - 24 * 60 * 60;

		$stats = ['allViews' => 0, 'allConversions' => 0];

		while($endDate - $calcDate > 0) {
			$calcDate = $calcDate + 24 * 60 * 60;
			$isoString = date("Y-m-d", $calcDate);
			$stats['dates'][$isoString] = ['views' => 0, 'conversions' => 0];
		}

		return $stats;
	}

}