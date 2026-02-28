<?php

namespace SplitTestForElementor\Classes\Services;

use SplitTestForElementor\Classes\Misc\SettingsManager;
use SplitTestForElementor\Classes\Misc\ShowCacheWarningMessage;
use SplitTestForElementor\Classes\Misc\ShowWPEngineMessage;
use SplitTestForElementor\Classes\Repo\TestRepo;

class CacheCheckService {

    private static $settingsManager;

	public function __construct() {
        if (self::$settingsManager == null) {
            self::$settingsManager = new SettingsManager();
        }
    }

	function isHostedOnWPEngine() {

		if (is_dir(ABSPATH."wp-content/mu-plugins/")) {
			if ($handle = opendir(ABSPATH."wp-content/mu-plugins/")) {
				while (false !== ($entry = readdir($handle))) {
					if ($entry != "." && $entry != "..") {
						if (strpos($entry, "wpengine") !== false) {
							closedir($handle);
							return true;
						}
					}
				}
				closedir($handle);
			}
		}
		return false;
	}

	public function runCheck() {
        // TODO we might have a better place "to check this"
        $url = $_SERVER["REQUEST_URI"];
        if (!str_contains($url, "page=splittest-for-elementor")) {
            return;
        }

		/*
		if ($this->isHostedOnWPEngine()) {
			$this->validateCacheOnWPEngine();
			return;
		}
		*/

        if (self::$settingsManager->getRawValue(SettingsManager::CACHE_BUSTER_ACTIVE)) {
            return;
        }

		$result = $this->hasCacheActive();

		if ($result) {
			// TODO check if the pro plugin is activated
			if ($this->check_plugin_installed(SPLIT_TEST_FOR_ELEMENTOR_PRO_PLUGIN_PATH)) {
                self::$settingsManager->setValue(SettingsManager::CACHE_BUSTER_ACTIVE, true);
			} else {
				$message = new ShowCacheWarningMessage();
				$message->run();
			}
		}
	}

	public function registerHooks() {

        add_filter( 'query_vars', function( $query_vars ){
            $query_vars[] = 'rocket-split-test-action';
            return $query_vars;
        } );

        add_action( 'template_redirect', function(){
            if (get_query_var('rocket-split-test-action') != "") {
                if (get_query_var('rocket-split-test-action') == "check-cache") {
					die(rand(1000000000, 1000000000 * 10)."");
				}
            }
        });

		add_action('send_headers', function () {
			if (strpos($_SERVER['REQUEST_URI'], 'split-test-for-elementor/v1') === false) {
				return;
			}

			if (strpos($_SERVER['REQUEST_URI'], 'check-cache') === false) {
				return;
			}

			die(rand(1000000000, 1000000000 * 10) . "");
		});

    }

	function check_plugin_installed( $plugin ) {
		$plugin_file = ABSPATH . 'wp-content/plugins/'.$plugin;
		return file_exists($plugin_file);
	}

	private function validateCacheOnWPEngine()
	{
		$result = $this->hasCacheActive();

		if (!$result) {
			return;
		}

		if ($this->check_plugin_installed(SPLIT_TEST_FOR_ELEMENTOR_PRO_PLUGIN_PATH)) {
			self::$settingsManager->setValue(SettingsManager::CACHE_BUSTER_ACTIVE, true);
			$message = new ShowWPEngineMessage();
			$message->run();
		} else {
			$message = new ShowCacheWarningMessage();
			$message->run();
		}

	}

	private function hasCacheActive()
	{
		$responses = [];

		for ($i = 0; $i < 4; $i++) {
			$response = $this->getTestResponse();
			if (in_array($response, $responses)) {
				return true;
			}
			$responses[] = $response;
		}

		return false;
	}

	public function getTestResponse()
	{
		$homeUrl = get_home_url(null, "split-test-for-elementor/v1/check-cache/");

		$curlHandle = curl_init($homeUrl);
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);

		$curlResponse = curl_exec($curlHandle);
		curl_close($curlHandle);

		return $curlResponse;
	}

}