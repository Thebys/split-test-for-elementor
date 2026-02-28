<?php

namespace SplitTestForElementor\Classes\Services;

use SplitTestForElementor\Classes\Misc\SettingsManager;
use SplitTestForElementor\Classes\Services\ConversionTracker;
use SplitTestForElementor\Classes\Misc\Constants;
use SplitTestForElementor\Classes\Misc\Util;
use SplitTestForElementor\Classes\Repo\PostTestManager;
use SplitTestForElementor\Classes\Repo\PostTestRepo;
use SplitTestForElementor\Classes\Repo\TestRepo;

class ExternalPageTrackingService {

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

	public function registerHooks() {

        add_filter( 'query_vars', function( $query_vars ){
            $query_vars[] = 'test_id';
            $query_vars[] = 'rocket-split-test-action';
            return $query_vars;
        } );
        
        add_action( 'template_redirect', function(){
            if (get_query_var('rocket-split-test-action') != "") {
                if (get_query_var('rocket-split-test-action') == "track-conversion") {
                    $testId = intval( get_query_var( 'test_id' ) );
					$this->process($testId);
				}
            }
        });

		add_action('send_headers', function () {
			if (strpos($_SERVER['REQUEST_URI'], 'split-test-for-elementor/v1') === false) {
				return;
			}

			if (strpos($_SERVER['REQUEST_URI'], 'track-conversion') === false) {
				return;
			}

			$matching = preg_match('/split-test-for-elementor\/v1\/tests\/([0-9]*)\/track-conversion/', trim($_SERVER['REQUEST_URI'], '/'), $matches, PREG_OFFSET_CAPTURE);

			if ($matching === false) {
				return;
			}

			$testId = intval($matches[1][0]);
			$this->process($testId);
		});

    }

    public function trackConversion($testId) {
        global $rocketSplitTestClientId;

        $test = self::$testRepo->getTest($testId);
        if ($test == null) {
            return;
        }
        $cookieName = "elementor_split_test_".$test->id."_variation";
        if(!isset($_COOKIE[$cookieName])) {
            return;
        }

        $variationId = (int) $_COOKIE[$cookieName];
        foreach ($test->variations as $variation) {
            if ($variationId == $variation->id) {
                self::$conversionTrack->trackConversion($test->id, $variationId, $rocketSplitTestClientId);
            }
        }
    }

	/**
	 * @param int $testId
	 * @return void
	 */
	function process(int $testId)
	{
		$this->trackConversion($testId);
		header("Access-Control-Allow-Origin: *");

		if (self::$settingsManager->getRawValue(SettingsManager::CACHE_BUSTER_ACTIVE)) {
			header('Cache-Control: no-store, private, no-cache, must-revalidate');     // HTTP/1.1
			header('Cache-Control: pre-check=0, post-check=0, max-age=0, max-stale=0', false);  // HTTP/1.1
			header('Pragma: public');
			header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');                  // Date in the past
			header('Expires: 0', false);
			header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
			header('Pragma: no-cache');
			header('Vary: *');
			header("Connection: close");
		}

		header('Content-Type: image/gif');
		echo base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
		die();
	}

}