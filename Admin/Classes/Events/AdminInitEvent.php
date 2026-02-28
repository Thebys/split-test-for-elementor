<?php

namespace SplitTestForElementor\Admin\Classes\Events;

use SplitTestForElementor\Admin\Classes\Controllers\SplitTestController;
use SplitTestForElementor\Classes\Misc\LicenceManager;

class AdminInitEvent {

	/**
	 * @var LicenceManager
	 */
	private static $licenceManager;

	/**
	 * AdminInitEvent constructor.
	 */
	public function __construct() {
		if (self::$licenceManager == null) {
			self::$licenceManager = new LicenceManager();
		}
	}

	public function fire() {

		if (!is_admin()) {
			return;
		}

		if (!isset($_GET['page'])) {
			return;
		}

		if ($_GET['page'] != "splittest-for-elementor") {
			return;
		}

		wp_enqueue_script('split-test-for-elementor-chartjs', plugins_url('Admin/assets/js/chartjs/Chart.bundle.min.js', SPLIT_TEST_FOR_ELEMENTOR_MAIN_FILE), array(), SPLIT_TEST_FOR_ELEMENTOR_VERSION, false);
		wp_enqueue_style('split-test-for-elementor-google-fonts', "//fonts.googleapis.com/css?family=Roboto", array());
		wp_enqueue_style('split-test-for-elementor-css', plugins_url('Admin/assets/css/style.css', SPLIT_TEST_FOR_ELEMENTOR_MAIN_FILE), array(), SPLIT_TEST_FOR_ELEMENTOR_VERSION, 'all');

		if (!isset($_GET['scope'])) {
			return;
		}

		if ($_GET['scope'] == "test") {
			$controller = new SplitTestController();
			if ($_GET['action'] == "delete") {
				$controller->delete();
			} else if ($_GET['action'] == "store") {
				$controller->store();
			} else if ($_GET['action'] == "update") {
				$controller->update();
			} else if ($_GET['action'] == "resetStatistics") {
				$controller->resetStatistics();
			}
		}

	}

}