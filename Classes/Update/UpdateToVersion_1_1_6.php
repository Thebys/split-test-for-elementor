<?php

namespace SplitTestForElementor\Classes\Update;

class UpdateToVersion_1_1_6 {

	public function run() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'elementor_splittest';
		$wpdb->query("UPDATE ".$table_name." SET test_type = 'elements';");

		add_action('admin_notices', [$this, 'showUpdateMessage']);
	}

	function showUpdateMessage() {
		?>
		<div class="notice notice-success is-dismissible">
			<h1>Split test for Elementor - Your new version</h1>
			<p>With our new version you can now test whole wordpress pages against each other</p>
			<p>Have fun with! Your Rocket Elements Team</p>
		</div>
		<?php
	}


}