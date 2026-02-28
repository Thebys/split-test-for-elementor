<?php

namespace SplitTestForElementor\Classes\Services;

class ConversionTracker {

	public function trackView($testId, $variationId, $clientId) {

		global $wpdb;

		$query = [];
		$query[] = "SELECT * FROM ".$this->getInteractionsTable($wpdb);
		$query[] = "WHERE splittest_id = ".$testId;
		$query[] = "AND client_id = '".$clientId."'";

		$interactions = $wpdb->get_results(implode(" ", $query), OBJECT);

		if (sizeof($interactions) == 0) {
			$result = $wpdb->insert($this->getInteractionsTable($wpdb), array(
				'splittest_id' => $testId,
				'variation_id' => $variationId,
				'type' => 'view',
				'client_id' => $clientId,
				'created_at' => current_time( 'mysql' )
			), array('%d', '%d', '%s', '%s', '%s'));
		}

	}

	public function trackConversion($testId, $variationId, $clientId) {

		global $wpdb;

		$query = [];
		$query[] = "SELECT * FROM ".$this->getInteractionsTable($wpdb);
		$query[] = "WHERE splittest_id = ".$testId;
		$query[] = "AND client_id = '".$clientId."'";

		$interactions = $wpdb->get_results(implode(" ", $query), OBJECT);

		if (sizeof($interactions) > 0) {
			$query = [];
			$query[] = "UPDATE ".$this->getInteractionsTable($wpdb);
			$query[] = "SET type = 'conversion', variation_id = ".$variationId;
			$query[] = "WHERE splittest_id = ".$testId;
			$query[] = "AND client_id = '".$clientId."'";

			$wpdb->query(implode(" ", $query));
			// LOW@kberlau Error Logging
		}

	}


	private function getInteractionsTable($wpdb) {
		return $splitTestPostTable = $wpdb->prefix . "elementor_splittest_interactions";
	}

}