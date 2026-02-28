<?php

namespace SplitTestForElementor\Classes\Update;

class UpdateToVersion_1_5_4 {

	public function run() {
		add_action('admin_notices', [$this, 'showUpdateMessage']);
	}

	function showUpdateMessage() {
		?>
		<div class="notice notice-success is-dismissible">
			<h1>Split test for Elementor - Your new version</h1>
			<p>It is now possible to track conversions for external links. Therefor select "external link" as a conversion type and insert your external link into the test setup form and save. After that you will get a tracking link you can use (e.x. as a link for a button).</p>
			<p>Have fun with! Your Rocket Elements Team</p>
		</div>
		<?php
	}


}