<?php

namespace SplitTestForElementor\Classes\Events;

use SplitTestForElementor\Classes\Misc\SettingsManager;
use SplitTestForElementor\Classes\Services\ConversionTracker;
use SplitTestForElementor\Classes\Misc\Constants;
use SplitTestForElementor\Classes\Misc\Util;
use SplitTestForElementor\Classes\Repo\PostTestManager;
use SplitTestForElementor\Classes\Repo\PostTestRepo;
use SplitTestForElementor\Classes\Repo\TestRepo;
use SplitTestForElementor\Classes\Services\TestService;

class SendHeadersEvent {

	private static $postTestManager;
	/**
	 * @var PostTestRepo
	 */
	private static $postTestRepo;
	private static $testRepo;
	private static $conversionTrack;
	private static $settingsManager;
	private static $testService;

	public function __construct() {
		if (self::$postTestManager == null) {
			self::$postTestManager = new PostTestManager();
			self::$testRepo = new TestRepo();
			self::$conversionTrack = new ConversionTracker();
			self::$postTestRepo = new PostTestRepo();
			self::$settingsManager = new SettingsManager();
			self::$testService = new TestService();
		}
	}

	public function fire() {

		if (isset($_GET['elementor-preview'])) {
			return;
		}

		$postId = url_to_postid($_SERVER['REQUEST_URI']);
		$currentLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$currentLink = explode("?", $currentLink)[0];
		$currentLink = trim($currentLink, "/");
		$isHomepage = site_url() == $currentLink;
		if ($isHomepage && $postId == 0) {
			$postId = (int) get_option('page_on_front');
		}

		// TODO: Change this to $rocketSplitTestClientId
		global $clientId;
		global $rocketSplitTestClientId;
		$splitTestClientIdCookieName = Constants::$SPLIT_TEST_CLIENT_ID_COOKIE;
		if (!isset($_COOKIE[$splitTestClientIdCookieName])) {
			$clientId = Util::generateV4UUID();
            if (!self::$settingsManager->getRawValue(SettingsManager::CACHE_BUSTER_ACTIVE)) {
			    Util::setCookie($splitTestClientIdCookieName, $clientId);
            }
		} else {
			$clientId = $_COOKIE[$splitTestClientIdCookieName];
		}
		$rocketSplitTestClientId = $clientId;

		// TODO@kberlau: Move this to class or something
		if (empty($postId) || $postId == null || $postId == 0 || $isHomepage) {
			$this->progressTestsForRedirect($clientId);
		}
		if (self::$settingsManager->getRawValue(SettingsManager::CACHE_BUSTER_ACTIVE)) {
			// TODO add url conversions
			$this->prepareJsTestsForPage( $postId, $clientId );
		} else {
			$this->progressConversions( $postId, $clientId, $currentLink );
			$this->progressTestsForPage( $postId, $clientId );
		}
	}

	private function progressConversions($postId, $clientId, $currentLink) {
		$tests = self::$testRepo->getTestsByConversionPagePostId($postId);
		$this->progressConversionsForTests($tests, $clientId);

		$tests = self::$testRepo->getTestsByConversionUrl($currentLink);
		$this->progressConversionsForTests($tests, $clientId);
	}

	private function progressConversionsForTests($tests, $clientId) {
		foreach ($tests as $test) {
			$cookieName = "elementor_split_test_".$test->id."_variation";
			if(!isset($_COOKIE[$cookieName])) {
				continue;
			}

			$variationId = (int) $_COOKIE[$cookieName];
			// TODO@kberlau: Is this necessary
			foreach ($test->variations as $variation) {
				if ($variationId == $variation->id) {
					self::$conversionTrack->trackConversion($test->id, $variationId, $clientId);
				}
			}
		}
	}

	private function progressTestsForRedirect($clientId) {

        $urlBase = home_url();
        $requestUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $relativePath = str_replace($urlBase, "", $requestUrl);
        $relativePath = explode("?", $relativePath)[0];
        $relativePath = trim($relativePath, "/");

		$tests = self::$testRepo->getRedirectTestsByUri($relativePath);
		if (sizeof($tests) == 0) {
			return;
		}
		$test = $tests[0];

		$variations = $this->progressTests($tests, $clientId);

		$targetVariation = $variations[$test->id];
		$urlQueryParams = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

		if (self::$settingsManager->getRawValue(SettingsManager::CACHE_BUSTER_ACTIVE)) {

			$splitTestClientIdCookieName = Constants::$SPLIT_TEST_CLIENT_ID_COOKIE;
			Util::setCookie($splitTestClientIdCookieName, $clientId);

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


        // TODO@kberlau: Add no cache
		foreach ($test->variations as $variation) {
			if ($variation->id == $targetVariation->id) {
				if ($test->test_type == "pages") {
					if ($urlQueryParams == "") {
						wp_redirect(get_permalink($variation->post_id), 302);
					} else {
						wp_redirect(get_permalink($variation->post_id).'?'.$urlQueryParams, 302);
					}
				} else if ($test->test_type == "urls") {
					if ($urlQueryParams == "") {
						wp_redirect( $variation->url, 302 );
					} else {
						wp_redirect( $variation->url .'?'.$urlQueryParams, 302 );
					}
				}
				exit;
			}
		}
	}

	private function progressTestsForPage($postId, $clientId) {
		$testIds = self::$postTestRepo->getTestIdsForPost($postId);
		if (sizeof($testIds) == 0) {
			return;
		}
		$tests = self::$testRepo->getTests($testIds);
		$this->progressTests($tests, $clientId);
	}

	/**
	 * @param $tests
	 * @param $clientId
	 *
	 * @return array|void
	 * @internal param $postId
	 */
	private function progressTests($tests, $clientId) {

		global $targetVariations;
		$targetVariations = [];

		foreach ($tests as $test) {
			$cookieName = "elementor_split_test_" . $test->id . "_variation";
			$targetVariation = null;

			$splitTestId = null;
			if (isset($_COOKIE[$cookieName])) {
				$splitTestId = $_COOKIE[$cookieName];
			}
			if (isset($_GET['stid'])) {
				$splitTestId = $_GET['stid'];
			}

			if ($splitTestId != null)  {
				if (filter_var($splitTestId, FILTER_VALIDATE_INT)) {
					foreach ($test->variations as $variation) {
						if ((int) $splitTestId == $variation->id) {
							$targetVariation = $variation;
							break;
						}
					}
				}
			}

			if ($targetVariation == null) {
				$targetVariation = self::$testService->getTargetVariation($test);
			}

			if ($targetVariation != null) {
				Util::setCookie($cookieName, $targetVariation->id);
			} else {
				// LOW@kberlau ERROR!
			}

			$targetVariations[$test->id] = $targetVariation;
			self::$conversionTrack->trackView($test->id, $targetVariation->id, $clientId);
		}

		return $targetVariations;
	}

	private function prepareJsTestsForPage($postId, $clientId) {
		global $rocketSplitTestRunningTests;
		$rocketSplitTestRunningTests = [];
		$testIds = self::$postTestRepo->getTestIdsForPost($postId);
		if (sizeof($testIds) == 0) {
			return;
		}

		$tests = self::$testRepo->getTests($testIds);
		foreach ($tests as $test) {
			$rocketSplitTestRunningTests[] = self::$testService->getTestDataForJs($test);
		}
	}


}