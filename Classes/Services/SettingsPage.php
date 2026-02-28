<?php

namespace SplitTestForElementor\Classes\Services;

use SplitTestForElementor\Classes\Misc\SettingsManager;

class SettingsPage
{

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
				<p><?php esc_html_e( 'Please also clear all active caches in order for activating the feature to take effect.', 'split-test-for-elementor' ); ?></p>
				<table>
					<?php $option = SettingsManager::PREFIX."_".SettingsManager::CACHE_BUSTER_ACTIVE; ?>
					<tr valign="top">
						<th scope="row"><label for="<?php echo($option); ?>"><?php esc_html_e( 'Cache Buster active', 'split-test-for-elementor' ); ?></label></th>
						<td>
							<input id="<?php echo($option); ?>" name="<?php echo($option); ?>" type="checkbox" value="true" <?php checked('true', get_option($option, 'true')); ?> />
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}


}
