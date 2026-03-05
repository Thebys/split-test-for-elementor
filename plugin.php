<?php

use SplitTestForElementor\Admin\Classes\Controllers\SplitTestController;
use SplitTestForElementor\Admin\Classes\Controllers\StatisticsController;
use SplitTestForElementor\Admin\Classes\Events\AdminInitEvent;
use SplitTestForElementor\Admin\Classes\Events\AfterSectionEndEvent;
use SplitTestForElementor\Classes\Endpoints\TestController;
use SplitTestForElementor\Classes\Endpoints\VariationController;
use SplitTestForElementor\Classes\Events\FormNewRecordEvent;
use SplitTestForElementor\Classes\Events\FrontendBeforeRenderEvent;
use SplitTestForElementor\Classes\Events\SendHeadersEvent;
use SplitTestForElementor\Classes\Events\WidgetRenderContentEvent;
use SplitTestForElementor\Classes\Events\WpHeaderEvent;
use SplitTestForElementor\Classes\Events\SectionShouldRenderEvent;
use SplitTestForElementor\Classes\Install\DB;
use SplitTestForElementor\Classes\Repo\PostTestManager;
use SplitTestForElementor\Classes\Misc\SettingsManager;
use SplitTestForElementor\Classes\Services\ExternalLinkTrackingService;
use SplitTestForElementor\Classes\Update\UpdateManager;
use SplitTestForElementor\Classes\Services\ExternalPageTrackingService;

/**
 * @package SplitTestForElementor
 *
 * Plugin Name: Split Test For Elementor (Thebys Fork)
 * Plugin URI: https://github.com/Thebys/split-test-for-elementor
 * Description: Split Test For Elementor — forked with bug fixes for template-loaded tests, SQL injection patches, and distribution improvements.
 * Author: Rocket Elements / Thebys
 * Version: 1.8.4-fork.3
 * Author URI: https://github.com/Thebys
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: false
 * Text Domain: split-test-for-elementor
 * Elementor tested up to: 4.4.0
 * Elementor Pro tested up to: 4.4.0
 *
 */

define('SPLIT_TEST_FOR_ELEMENTOR_MAIN_FILE', __FILE__);
define('SPLIT_TEST_FOR_ELEMENTOR_VERSION', "1.8.4-fork.3");
define('SPLIT_TEST_FOR_ELEMENTOR_VERSION_OPTION_NAME', "split_test_for_elementor_version");
define('SPLIT_TEST_FOR_ELEMENTOR_SUPPORT_LINK', 'https://www.rocketelements.io/support/');

require_once(__DIR__."/vendor/autoload.php");

// Updates and Setup ===================================================================================================

// Setup Database
register_activation_hook(__FILE__, array(new DB(), 'setup'));
register_activation_hook(__FILE__, function () {
	global $wp_rewrite;
	add_rewrite_rule(
		'split-test-for-elementor/v1/tests/([0-9]*?)/track-conversion/?$',
		'index.php?test_id=$matches[1]&rocket-split-test-action=track-conversion',
		'top'
	);
	add_rewrite_rule(
		'split-test-for-elementor/v1/tests/([0-9]*?)/external-link-redirect/?$',
		'index.php?test_id=$matches[1]&rocket-split-test-action=external-link-redirect',
		'top'
	);
	$wp_rewrite->flush_rules();
});

register_deactivation_hook( __FILE__, function () {
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
});

// =====================================================================================================================
// Decide which test variation to show and tracks conversions
add_action('send_headers', [new SendHeadersEvent(), 'fire']);

add_action('wp_head', [new WpHeaderEvent(), 'fire']);

// Adding css for hiding / showing split test elements
add_action('elementor/frontend/section/before_render', [new FrontendBeforeRenderEvent(), 'fire']);
// Removing widgets from content from output
add_action('elementor/widget/render_content', [new WidgetRenderContentEvent(), 'fire'], 10, 2);

add_action('elementor/frontend/section/should_render', [new SectionShouldRenderEvent(), 'fire'], 10, 2);

add_action('elementor/frontend/container/before_render', [new FrontendBeforeRenderEvent(), 'fire']);
// Admin ===============================================================================================================

function splittest_for_elementor_page() {
    $capability = apply_filters('splittest_for_elementor_admin_menu_capability', 'manage_options');
	add_menu_page(
		'Split test for Elementor',
		'Split test',
        $capability,
		'splittest-for-elementor',
		'splittest_for_elementor_page_html',
		plugin_dir_url(__FILE__) . 'Admin/assets/images/icon.png',
		20
	);
}
add_action('admin_menu', 'splittest_for_elementor_page');

// Registering controllers before content is send
add_action('admin_init', [new AdminInitEvent(), 'fire']);
function splittest_for_elementor_page_html() {
	switch (isset($_GET['scope']) ? $_GET['scope'] : "test") {
		case "test"         : (new SplitTestController())->run(); break;
		case "statistics"   : (new StatisticsController())->run(); break;
		default             : break;
	}
}

add_action('elementor/editor/before_enqueue_scripts', function() {
	wp_enqueue_script(
		'split-test-for-elementor-editor',
		plugins_url('Admin/assets/js/editor.min.js', SPLIT_TEST_FOR_ELEMENTOR_MAIN_FILE),
		[],
		SPLIT_TEST_FOR_ELEMENTOR_VERSION,
		true // in_footer
	);
});

// Rest Endpoints ======================================================================================================
add_action('rest_api_init', function () {
	register_rest_route( 'splitTestForElementor/v1', '/tests/', [
		'methods' => 'POST',
		'callback' => array(new TestController(), 'store'),
        'permission_callback' => function() { return current_user_can('publish_pages'); }
	]);
});

add_action('rest_api_init', function () {
	register_rest_route('splitTestForElementor/v1', '/variations/', [
		'methods' => 'POST',
		'callback' => array(new VariationController(), 'store'),
        'permission_callback' => function() { return current_user_can('publish_pages'); }
	]);
});

// Editor ==============================================================================================================

// Add Split test controls to editor
add_action('elementor/element/after_section_end', [new AfterSectionEndEvent(), 'fire'], 10, 3);

// Synchronize split tests / posts connections after save
add_action('elementor/editor/after_save', [new PostTestManager(), 'onEditorSave'], 10, 3);

do_action('split_test_for_elementor_after_init');

add_action( 'plugins_loaded', function() {
	load_plugin_textdomain( 'split-test-for-elementor' );
});

// Other startup stuff
(new ExternalLinkTrackingService())->registerHooks();
(new ExternalPageTrackingService())->registerHooks();

add_action( 'admin_init', function() {
	(new SettingsManager())->registerSettings();
});

add_action( 'elementor_pro/forms/new_record', [new FormNewRecordEvent(), 'fire'], 10, 2 );

// Updates
add_action('plugins_loaded', function() {
	if (is_admin()) {
		UpdateManager::runUpdates();
	}
});
