<?php

namespace SplitTestForElementor\Classes\Update;

class UpdateToVersion_1_1 {

	public function run() {
		add_action('admin_notices', [$this, 'showUpdateMessage']);
	}

	function showUpdateMessage() {
		?>
		<div class="notice notice-success is-dismissible">
			<h1>Split test for Elementor - Your new version</h1>
			<p>Our next release is out. Now you can test button / image / html / heading / text widgets independently from the section and select the testing target page through a drop down Menu.</p>
			<p>Have fun with! Your Rocket Elements Team</p>
		</div>
		<?php
	}

}