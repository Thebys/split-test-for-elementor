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
            <p>We have detected that you have an active caching system / plugin on your page. Please enable the Cache Buster in Settings &rarr; Split Test to ensure split testing works properly with caching.</p>
		</div>
		<?php
	}


}