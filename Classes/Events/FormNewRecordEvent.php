<?php

namespace SplitTestForElementor\Classes\Events;

use SplitTestForElementor\Classes\Misc\Constants;
use SplitTestForElementor\Classes\Services\ConversionTracker;

class FormNewRecordEvent
{
	private static $conversionTrack;

	public function __construct() {
		if (self::$conversionTrack == null) {
			self::$conversionTrack = new ConversionTracker();
		}
	}

	public function fire($record, $handler) {
		if (!isset($_COOKIE[Constants::$SPLIT_TEST_CLIENT_ID_COOKIE])) {
			return;
		} else {
			$clientId = $_COOKIE[Constants::$SPLIT_TEST_CLIENT_ID_COOKIE];
		}

		$testId = $record->get_form_settings( 'split_test_control_test_id' );
		$variationId = $record->get_form_settings( 'split_test_control_variation_id' );

		if ($testId == null || $variationId == null) {
			return;
		}

		self::$conversionTrack->trackConversion($testId, $variationId, $clientId);
	}

}