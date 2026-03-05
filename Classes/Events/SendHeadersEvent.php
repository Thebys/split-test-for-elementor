<?php

namespace SplitTestForElementor\Classes\Events;

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
	private static $testService;

	public function __construct() {
		if (self::$postTestManager == null) {
			self::$postTestManager = new PostTestManager();
			self::$testRepo = new TestRepo();
			self::$conversionTrack = new ConversionTracker();
			self::$postTestRepo = new PostTestRepo();
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

		global $clientId;
		global $rocketSplitTestClientId;
		$splitTestClientIdCookieName = Constants::$SPLIT_TEST_CLIENT_ID_COOKIE;
		if (!isset($_COOKIE[$splitTestClientIdCookieName])) {
			$clientId = Util::generateV4UUID();
			Util::setCookie($splitTestClientIdCookieName, $clientId);
		} else {
			$clientId = $_COOKIE[$splitTestClientIdCookieName];
		}
		$rocketSplitTestClientId = $clientId;

		if (empty($postId) || $postId == null || $postId == 0 || $isHomepage) {
			$this->progressTestsForRedirect($clientId);
		}
		$this->progressConversions( $postId, $clientId, $currentLink );
		$this->progressTestsForPage( $postId, $clientId );
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

		// Redirect responses must never be cached
		header('Cache-Control: no-store, private, no-cache, must-revalidate');
		header('Pragma: no-cache');

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

		// Fallback: check Elementor templates whose display conditions
		// match the current page's post type (covers template-loaded tests).
		if (sizeof($testIds) == 0 && $postId > 0) {
			$postType = get_post_type($postId);
			if ($postType) {
				$testIds = self::$postTestRepo->getTestIdsFromMatchingTemplates($postType);
			}
		}

		if (sizeof($testIds) == 0) {
			return;
		}

		// Prevent CDN/proxy caching on pages with active split tests
		header('Cache-Control: no-store, private, no-cache, must-revalidate');
		header('Pragma: no-cache');

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
			if (isset($_GET['stid']) && filter_var($_GET['stid'], FILTER_VALIDATE_INT)) {
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
			}

			$targetVariations[$test->id] = $targetVariation;
			if ($targetVariation !== null) {
				self::$conversionTrack->trackView($test->id, $targetVariation->id, $clientId);
			}
		}

		return $targetVariations;
	}

}
