<?php

namespace SplitTestForElementor\Classes\Services;

class ConversionTracker {

	public function trackView($testId, $variationId, $clientId) {

		global $wpdb;

		$table = $this->getInteractionsTable($wpdb);

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$interactions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE splittest_id = %d AND client_id = %s",
				$testId,
				$clientId
			),
			OBJECT
		);

		if (sizeof($interactions) == 0) {
			$wpdb->insert($table, array(
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

		$table = $this->getInteractionsTable($wpdb);

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$interactions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE splittest_id = %d AND client_id = %s",
				$testId,
				$clientId
			),
			OBJECT
		);

		if (sizeof($interactions) > 0) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$table} SET type = 'conversion', variation_id = %d WHERE splittest_id = %d AND client_id = %s",
					$variationId,
					$testId,
					$clientId
				)
			);
		}

	}


	private function getInteractionsTable($wpdb) {
		return $splitTestPostTable = $wpdb->prefix . "elementor_splittest_interactions";
	}

}