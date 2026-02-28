<?php

namespace SplitTestForElementor\Classes\Update;

class UpdateToVersion_1_3_0 {

	public function run() {
		add_action('admin_notices', [$this, 'showUpdateMessage']);
	}

	function showUpdateMessage() {
		?>
		<div class="notice notice-success is-dismissible">
			<h1>Split test for Elementor - Your new version</h1>
			<p>It is now possible to track conversions on external pages. Have made you a tutorial to show you how to use this feature
				<a href="https://youtu.be/Ix6fRi9X-Vc" target="_blank">open tutorial</a>.
			</p>
			<p>Have fun with! Your Rocket Elements Team</p>
		</div>
		<?php
	}


}