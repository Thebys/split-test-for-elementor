<?php

use SplitTestForElementor\Admin\Classes\Controllers\SplitTestController;
use SplitTestForElementor\Admin\Classes\Controllers\StatisticsController;
use SplitTestForElementor\Admin\Classes\Elementor\ConversionWidget;
use SplitTestForElementor\Admin\Classes\Events\AdminInitEvent;
use SplitTestForElementor\Admin\Classes\Events\AfterSectionEndEvent;
use SplitTestForElementor\Classes\Endpoints\TestController;
use SplitTestForElementor\Classes\Endpoints\VariationController;
use SplitTestForElementor\Classes\Events\FormNewRecordEvent;
use SplitTestForElementor\Classes\Events\FormSubmitEvent;
use SplitTestForElementor\Classes\Events\FrontendBeforeRenderEvent;
use SplitTestForElementor\Classes\Events\SendHeadersEvent;
use SplitTestForElementor\Classes\Events\WidgetRenderContentEvent;
use SplitTestForElementor\Classes\Events\WpHeaderEvent;
use SplitTestForElementor\Classes\Events\SectionShouldRenderEvent;
use SplitTestForElementor\Classes\Install\DB;
use SplitTestForElementor\Classes\Repo\PostTestManager;
use SplitTestForElementor\Classes\Services\ExternalLinkTrackingService;
use SplitTestForElementor\Classes\Services\SettingsPage;
use SplitTestForElementor\Classes\Update\UpdateManager;
use SplitTestForElementor\Classes\Services\ExternalPageTrackingService;
use SplitTestForElementor\Classes\Services\CacheCheckService;

/**
 * @package SplitTestForElementor
 * @version 1.8.4
 * @copyright Copyright (C) 2025 Rocket Elements
 * @license Free for use if not bundle as a product. Code changes forbidden. Bundling and / or selling parts of this or as a whole is forbidden if not explicitly allowed by the author.

 * Plugin Name: Split Test For Elementor
 * Plugin URI: https://wordpress.org/plugins/split-test-for-elementor/
 * Description: Split Test For Elementor
 * Author: Rocket Elements
 * Version: 1.8.4
 * Author URI: https://www.rocketelements.io
 * License: Free for use if not bundle as a product. Code changes forbidden. Bundling and / or selling parts of this or as a whole is forbidden if not explicitly allowed by the author.
 * Text Domain: split-test-for-elementor
 * Elementor tested up to: 4.4.0
 * Elementor Pro tested up to: 4.4.0
 *
 */

define('SPLIT_TEST_FOR_ELEMENTOR_MAIN_FILE', __FILE__);
define('SPLIT_TEST_FOR_ELEMENTOR_VERSION', "1.8.4");
define('SPLIT_TEST_FOR_ELEMENTOR_VERSION_OPTION_NAME', "split_test_for_elementor_version");
define('SPLIT_TEST_FOR_ELEMENTOR_PRO_VERSION_LINK', 'https://www.rocketelements.io/splittest-pro/?utm_source=plugin');
define('SPLIT_TEST_FOR_ELEMENTOR_LITE_MAX_TEST_COUNT', 5);
define('SPLIT_TEST_FOR_ELEMENTOR_LITE_MAX_VARIATION_COUNT', 2);
define('SPLIT_TEST_FOR_ELEMENTOR_SUPPORT_LINK', 'https://www.rocketelements.io/support/');

define('SPLIT_TEST_FOR_ELEMENTOR_PRO_PLUGIN_PATH', "split-test-for-elementor-pro/plugin.php");

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
	add_rewrite_rule(
		'split-test-for-elementor/v1/check-cache/?$',
		'index.php?test_id=$matches[1]&rocket-split-test-action=check-cache',
		'top'
	);
	$wp_rewrite->flush_rules();
});

register_deactivation_hook( __FILE__, function () {
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
});

// =====================================================================================================================
/*
add_action('elementor/widgets/register', function(\Elementor\Widgets_Manager $widgets_manager) {
	$widgets_manager->register(new ConversionWidget());
});
*/
// Decide witch test variation to show and tracks conversions
add_action('send_headers', [new SendHeadersEvent(), 'fire']);

add_action('wp_head', [new WpHeaderEvent(), 'fire']);

// Adding css for hiding / showing split test elements
add_action('elementor/frontend/section/before_render', [new FrontendBeforeRenderEvent(), 'fire']);
// Removing widgets from content from output
add_action('elementor/widget/render_content', [new WidgetRenderContentEvent(), 'fire'], 10, 2);

add_action('elementor/frontend/section/after_render', [new \SplitTestForElementor\Classes\Events\FrontendAfterRenderSectionEvent(), 'fire']);
add_action('elementor/frontend/section/should_render', [new SectionShouldRenderEvent(), 'fire'], 10, 2);

add_action('elementor/frontend/container/before_render', [new FrontendBeforeRenderEvent(), 'fire']);
add_action('elementor/frontend/container/after_render', [new \SplitTestForElementor\Classes\Events\FrontendAfterRenderSectionEvent(), 'fire']);
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
        'permission_callback' => '__return_true'
	]);
});

add_action('rest_api_init', function () {
	register_rest_route('splitTestForElementor/v1', '/tests/getVariationToDisplay/', [
		'methods' => 'GET',
		'callback' => array(new TestController(), 'getVariationToDisplay'),
        'permission_callback' => '__return_true'
	]);
});

add_action('rest_api_init', function () {
	register_rest_route('splitTestForElementor/v1', '/variations/', [
		'methods' => 'POST',
		'callback' => array(new VariationController(), 'store'),
        'permission_callback' => '__return_true'
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
	if (is_admin()) {
		(new CacheCheckService())->runCheck();
    }
});

// Other startup stuff
(new ExternalLinkTrackingService())->registerHooks();
(new ExternalPageTrackingService())->registerHooks();
(new CacheCheckService())->registerHooks();

(new SettingsPage)->registerSettingsPage();

add_action( 'elementor_pro/forms/new_record', [new FormNewRecordEvent(), 'fire'], 10, 2 );

// Updates
add_action('plugins_loaded', function() {
	if (is_admin()) {
		UpdateManager::runUpdates();
	}
});

/*
add_action( 'wp_footer', function() {
	if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
		return;
	}
	?>
	<script>
		jQuery( function( $ ) {
			// Add space for Elementor Menu Anchor link
			if ( window.elementorFrontend ) {
				jQuery(document).ready(function () {
					elementor.hooks.addAction( 'panel/open_editor/widget', function( panel, model, view ) {
						console.log("init split test "  + view);
						// console.log(window);
						document.splitTestForElementor.init();
					});
					jQuery(".elementor-tab-control-advanced").click(function () {
						console.log("est");
					});
				});
			}
		} );
	</script>
	<?php
} );
*/
