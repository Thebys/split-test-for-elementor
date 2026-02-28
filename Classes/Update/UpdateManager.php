<?php

namespace SplitTestForElementor\Classes\Update;

use SplitTestForElementor\Classes\Install\DB;

class UpdateManager {

	public static function runUpdates() {
		$DB = new DB();
		$DB->setup();

		// Check for updates
		$option = get_option(SPLIT_TEST_FOR_ELEMENTOR_VERSION_OPTION_NAME, null);
		if ($option == null) {
			return;
		}
		if (version_compare($option, "1.0.2", '<')) {
			$update = new UpdateToVersion_1_0_2();
			$update->run();
		}
		if (version_compare($option, "1.1", '<')) {
			$update = new UpdateToVersion_1_1();
			$update->run();
		}
		if (version_compare($option, "1.1.6", '<')) {
			$update = new UpdateToVersion_1_1_6();
			$update->run();
		}
		if (version_compare($option, "1.1.8", '<')) {
			$update = new UpdateToVersion_1_1_8();
			$update->run();
		}
		if (version_compare($option, "1.3.0", '<')) {
			$update = new UpdateToVersion_1_3_0();
			$update->run();
		}

		update_option(SPLIT_TEST_FOR_ELEMENTOR_VERSION_OPTION_NAME, SPLIT_TEST_FOR_ELEMENTOR_VERSION);
	}

}