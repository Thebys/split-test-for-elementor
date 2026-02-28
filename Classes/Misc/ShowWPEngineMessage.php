<?php

namespace SplitTestForElementor\Classes\Misc;

class ShowWPEngineMessage {

	public function run() {
		add_action('admin_notices', [$this, 'showMessage']);
	}

	function showMessage() {
		?>
		<div class="notice notice-error is-dismissible">
			<h2>Split test for Elementor Error - ACT NOW!</h2>
            <p>We have detected that you are using WP Engine as a hosting provider. In order for Split test for Elementor to work properly some extra steps are needed.</p>
            <p>In order for setting up everything correctly we have written an article in order to assist you: <a href="https://www.rocketelements.io/wp-engine-setup/" target="_blank">https://www.rocketelements.io/wp-engine-setup/</a></p>
		</div>
		<?php
	}


}