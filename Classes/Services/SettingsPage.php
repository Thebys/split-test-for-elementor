<?php

namespace SplitTestForElementor\Classes\Services;

use SplitTestForElementor\Classes\Misc\LicenceManager;
use SplitTestForElementor\Classes\Misc\SettingsManager;

class SettingsPage
{

	/** @var LicenceManager */
	private $licenceManager;

	public function __construct()
	{
		$this->licenceManager = new LicenceManager();
	}

	public function registerSettingsPage() {
		add_action( 'admin_init', function() {
			(new SettingsManager())->registerSettings();
		} );

		add_action('admin_menu', function() {
			add_options_page(
				'Split Test for Elementor Settings',
				'Split Test',
				'manage_options',
				'split-test-for-elementor',
				[$this, 'settingsPage']);
		});

	}

	function settingsPage() {
		?>
		<div>
			<?php screen_icon(); ?>
			<h2><?php esc_html_e( 'Split Test for Elementor', 'split-test-for-elementor' ); ?></h2>
			<form method="post" action="options.php">
				<?php settings_fields( 'split_test_for_elementor_options_group' ); ?>
				<h3><?php esc_html_e( 'Cache Buster', 'split-test-for-elementor' ); ?></h3>
				<!--<p><?php esc_html_e( 'Please be aware that, in really rare cases, this feature that can interfere with other elementor plugins. Please contact the support info@rocketelement.io if you encounter problems.', 'split-test-for-elementor' ); ?></p>-->
				<p><?php esc_html_e( 'Please also clear all active caches in order for activating the feature to take effect.', 'split-test-for-elementor' ); ?></p>
				<?php if ($this->licenceManager->hasActiveProLicence()) { ?>
				<table>
					<?php $option = SettingsManager::PREFIX."_".SettingsManager::CACHE_BUSTER_ACTIVE; ?>
					<tr valign="top">
						<th scope="row"><label for="<?php echo($option); ?>"><?php esc_html_e( 'Cache Buster active', 'split-test-for-elementor' ); ?></label></th>
						<td>
							<input id="<?php echo($option); ?>" name="<?php echo($option); ?>" type="checkbox" value="true" <?php checked('true', get_option($option, 'true')); ?> />
						</td>
					</tr>
				</table>
			<?php } else { ?>
					<p>
						<?php esc_html_e( 'You have to buy the pro licence to get the cache buster feature', 'split-test-for-elementor' ); ?>
						<a href="<?php echo(SPLIT_TEST_FOR_ELEMENTOR_PRO_VERSION_LINK); ?>" target="_blank">
							<?php esc_html_e( 'buy the pro version', 'split-test-for-elementor' ); ?>
						</a>
					</p>
			<?php } ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}


}