<?php

namespace SplitTestForElementor\Classes\Update;

class UpdateToVersion_1_1_8 {

	public function run() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'elementor_splittest';
		$wpdb->query("UPDATE ".$table_name." SET test_type = 'elements' WHERE test_type = '' OR test_type IS NULL;");

	}

}