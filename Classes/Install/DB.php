<?php

namespace SplitTestForElementor\Classes\Install;

class DB {

	private $dbVersion = "1.3";

	function setup() {
		global $wpdb;

		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		$charset_collate = $wpdb->get_charset_collate();

		$table_name = $wpdb->prefix . 'elementor_splittest';
		$sql = "CREATE TABLE $table_name (
			id int(11) NOT NULL AUTO_INCREMENT,
			active tinyint(1) NOT NULL,
			name varchar(255) NOT NULL,
			test_type varchar(32) NOT NULL,
			test_uri varchar(255) NULL DEFAULT NULL,
			conversion_type varchar(32) NOT NULL,
			conversion_page_id int(11) NULL,
			conversion_url varchar(511) NULL DEFAULT NULL,
			external_link varchar(255) NULL DEFAULT NULL,
			created_at timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset_collate;";
		dbDelta( $sql );

		$table_name = $wpdb->prefix . 'elementor_splittest_interactions';
		$sql = "CREATE TABLE $table_name (
			id int(11) NOT NULL AUTO_INCREMENT,
			client_id varchar(46) NOT NULL,
			type enum('view','conversion') NOT NULL,
			splittest_id int(11) NOT NULL,
			variation_id int(11) NOT NULL,
			created_at timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset_collate;";
		dbDelta( $sql );

		$table_name = $wpdb->prefix . 'elementor_splittest_post';
		$sql = "CREATE TABLE $table_name (
			id int(11) NOT NULL AUTO_INCREMENT,
			splittest_id int(11) NOT NULL,
			post_id int(11) NOT NULL,
			created_at timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset_collate;";
		dbDelta( $sql );

		$table_name = $wpdb->prefix . 'elementor_splittest_variations';
		$sql = "CREATE TABLE $table_name (
			id int(11) NOT NULL AUTO_INCREMENT,
			active tinyint(1) NOT NULL,
			name varchar(255) NOT NULL,
			percentage int(3) NOT NULL,
			url varchar(255) NULL DEFAULT NULL,
			post_id int(11) NULL DEFAULT NULL,
			splittest_id int(11) NOT NULL,
			created_at timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			deleted_at timestamp NULL DEFAULT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";
		dbDelta( $sql );

		add_option( 'split_test_for_elementor_db_version', $this->dbVersion);

	}

}