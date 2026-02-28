<?php

namespace SplitTestForElementor\Classes\Misc;

class ShowCacheWarningMessage {

	public function run() {
		add_action('admin_notices', [$this, 'showMessage']);
	}

	function showMessage() {
		?>
		<div class="notice notice-error is-dismissible">
			<h2>Split test for Elementor - Cache detected</h2>
            <p>We have detected that you have an active caching system / plugin on your page. The free version of split test for elementor does not support caching system / plugins.</p>
            <p>However, you can request a free trial of the pro version of the plugin here: <a href="https://www.rocketelements.io/contact-us/" target="_blank">https://www.rocketelements.io/contact-us/</a></p>
            <p>If you already have a pro licence, please install the pro plugin and activate it in order for split testing to work properly.</p>
		</div>
		<?php
	}


}