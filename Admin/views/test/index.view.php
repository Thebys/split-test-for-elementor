<?php
$licenceManager = new \SplitTestForElementor\classes\misc\LicenceManager();
?>

<div class="wrap split-test-for-elementor-test-index">

	<div class="messages">
		<?php if ($licenceManager->isLiteTestCountReached()) { ?>
			<div class="notice notice-warning is-dismissible">
				<p><a href="<?php echo(SPLIT_TEST_FOR_ELEMENTOR_PRO_VERSION_LINK); ?>" target="_blank"><?php esc_html_e( 'To add more Tests buy the pro version', 'split-test-for-elementor' ); ?></a></p>
			</div>
		<?php } ?>

		<?php if (isset($_GET['message']) && $_GET['message'] == "delete_success") { ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Test successfully deleted', 'split-test-for-elementor' ); ?></p>
			</div>
		<?php } ?>

		<?php if (isset($_GET['message']) && $_GET['message'] == "error_store_data_missing") { ?>
			<div class="notice notice-warning is-dismissible">
				<p><a href="<?php echo(SPLIT_TEST_FOR_ELEMENTOR_SUPPORT_LINK); ?>" target="_blank"><?php esc_html_e( 'The test could not be saved. Form data missing. Contact support.', 'split-test-for-elementor' ); ?></a></p>
			</div>
		<?php } ?>

		<?php if (isset($_GET['message']) && $_GET['message'] == "security_error") { ?>
			<div class="notice notice-warning is-dismissible">
				<p>Security Error</p>
			</div>
		<?php } ?>

		<?php if (isset($_GET['message']) && $_GET['message'] == "error_conversion_page_missing") { ?>
			<div class="notice notice-warning is-dismissible">
				<p><?php esc_html_e( 'The test could not be saved. Conversion page missing.', 'split-test-for-elementor' ); ?></p>
			</div>
		<?php } ?>

		<?php if (isset($_GET['message']) && $_GET['message'] == "error_update_data_missing") { ?>
			<div class="notice notice-warning is-dismissible">
				<p><a href="<?php echo(SPLIT_TEST_FOR_ELEMENTOR_SUPPORT_LINK); ?>" target="_blank"><?php esc_html_e( 'The test could not be saved. Form data missing. Contact support.', 'split-test-for-elementor' ); ?></a></p>
			</div>
		<?php } ?>

		<?php if (isset($_GET['message']) && $_GET['message'] == "error_conversion_url_missing") { ?>
			<div class="notice notice-warning is-dismissible">
				<p><?php esc_html_e( 'The test could not be saved. Conversion url missing.', 'split-test-for-elementor' ); ?></p>
			</div>
		<?php } ?>

		<?php if (isset($_GET['message']) && $_GET['message'] == "error_delete") { ?>
			<div class="notice notice-warning is-dismissible">
				<p><a href="<?php echo(SPLIT_TEST_FOR_ELEMENTOR_SUPPORT_LINK); ?>" target="_blank"><?php esc_html_e( 'The test could not be deleted. Contact support.', 'split-test-for-elementor' ); ?></a></p>
			</div>
		<?php } ?>

		<?php if (isset($_GET['message']) && $_GET['message'] == "reset_success") { ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Successfully reset statistics for test', 'split-test-for-elementor' ); ?></p>
			</div>
		<?php } ?>
	</div>

	<h1><?= esc_html( get_admin_page_title() ); ?></h1>

	<div class="content-wrapper">
		<div class="padding-wrapper">
			<h2><?php esc_html_e('Split Tests', 'split-test-for-elementor'); ?></h2>
			<?php if ($licenceManager->isLiteTestCountReached()) { ?>
				<a class="button add-test-button" href="<?php echo(SPLIT_TEST_FOR_ELEMENTOR_PRO_VERSION_LINK); ?>" target="_blank">
				<?php esc_html_e( 'Buy Pro', 'split-test-for-elementor' ); ?>
			</a>
			<?php } else { ?>
				<a class="button-primary add-test-button" href="<?php echo admin_url( 'admin.php?page=splittest-for-elementor&scope=test&action=create'); ?>">
				<?php esc_html_e( 'Add Test', 'split-test-for-elementor' ); ?>
			</a>
			<?php } ?>
			<table class="wp-list-table widefat fixed striped posts">
				<thead>
				<tr>
					<th scope="col" id="order_number" class="manage-column column-order_number column-primary sortable desc name-col">
						<span><?php esc_html_e( 'Name', 'split-test-for-elementor' ); ?></span>
					</th>
					<th></th>
				</tr>
				</thead>

				<tbody id="the-list">
				<?php foreach ($tests as $test) { ?>
					<tr id="test-<?php echo( $test->id ); ?>" class="iedit hentry">
						<td class="name-col">
							<a href="<?php echo admin_url( 'admin.php?page=splittest-for-elementor&scope=test&action=edit&id=' . $test->id ); ?>"><?php echo( $test->name ); ?></a>
						</td>
						<td class="button-col">
							<a class="button" href="<?php echo admin_url( 'admin.php?page=splittest-for-elementor&scope=statistics&action=index&id=' . $test->id ); ?>"><?php esc_html_e( 'Statistics', 'split-test-for-elementor' ); ?></a>
							<a class="button" href="<?php echo admin_url( 'admin.php?page=splittest-for-elementor&scope=test&action=edit&id=' . $test->id ); ?>"><?php esc_html_e( 'Edit', 'split-test-for-elementor' ); ?></a>

							<?php // LOW@kberlau: Implement ?>
<!--							<a class="button wc-action-button wc-action-button-complete complete"-->
<!--							   href="--><?php //echo admin_url( 'admin.php?page=splittest-for-elementor&scope=statistics&action=deactivate&id=' . $test->id ); ?><!--">Deactivate</a>-->
							<?php // LOW@kberlau: Implement ?>
							<form action="<?php echo admin_url( 'admin.php?page=splittest-for-elementor&scope=test&action=delete&id=' . $test->id ); ?>" style="display: inline;" method="post">
								<input name="nonce" type="hidden" value="<?php echo(wp_create_nonce('test-nonce')); ?>" />
								<input type="submit" class="button" value="<?php esc_html_e( 'Delete', 'split-test-for-elementor' ); ?>">
							</form>
                        </td>
					</tr>
				<?php } ?>
				<?php if (sizeof($tests) == 0) {?>
					<tr id="new-test" class="iedit hentry">
						<td class="name-col" colspan="2" align="center">
							<?php esc_html_e( 'Add your first test', 'split-test-for-elementor' ); ?>
						</td>
					</tr>
				<?php } ?>
				</tbody>

			</table>
		</div>
	</div>

</div>