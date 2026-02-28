<?php

if ($scope == "edit") {
	$formUrl = admin_url('admin.php?page=splittest-for-elementor&scope=test&action=update&id='.$test->id);
} else {
	$formUrl = admin_url('admin.php?page=splittest-for-elementor&scope=test&action=store');
}

$resetStatsFormUrl = "";
if ($scope == "edit") {
	$resetStatsFormUrl = admin_url('admin.php?page=splittest-for-elementor&scope=test&action=resetStatistics&id='.$test->id);
}

?>

<div class="messages">
	<?php if (isset($_GET['message']) && $_GET['message'] == "save_success") { ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Test successfully saved', 'split-test-for-elementor' ); ?></p>
		</div>
	<?php } ?>
	<?php if (isset($_GET['message']) && $_GET['message'] == "store_success") { ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Test successfully created', 'split-test-for-elementor' ); ?></p>
		</div>
	<?php } ?>
	<?php if (isset($_GET['message']) && $_GET['message'] == "error_test_page_invalid_chars") { ?>
		<div class="notice notice-warning is-dismissible">
			<p><?php esc_html_e( 'The test could not be created. Test page URI contains invalid chars.', 'split-test-for-elementor' ); ?></p>
		</div>
	<?php } ?>
	<?php if (isset($_GET['message']) && $_GET['message'] == "error_test_page_taken") { ?>
		<div class="notice notice-warning is-dismissible">
			<p><?php esc_html_e( 'The test could not be created. Test page URL is already registered.', 'split-test-for-elementor' ); ?></p>
		</div>
	<?php } ?>
</div>

<script type="application/javascript">
	<?php // LOW@kberlau move to js file ?>

	window.homeUrl = "<?php echo( home_url('/')); ?>";

	function getInt(value) {
		var parsed = parseInt(value);
		if (isNaN(parsed)) {
			return 0;
		} else {
			return parsed;
		}
	}

	function onTestUriChanged(value) {
		var url = window.homeUrl + value;
		console.log(url);
		jQuery("#test-page-url").attr("href", url);
	}

	function registerTestVariationDelete() {
		jQuery(".split-test-for-elementor.test-form .button-delete").unbind('click').click(function () {
			var deleteRow = jQuery(this).closest(".row");
			var deleteVariationId = jQuery(deleteRow).attr('data-variation-id');

			if (deleteVariationId != "null" && deleteVariationId != "") {
				jQuery(".split-test-for-elementor.test-form form")
					.append('<input type="hidden" value="' + deleteVariationId + '" name="test-delete-variation[]" />');
			}

			jQuery(deleteRow).remove();
		});
	}

	function registerTestVariationAdd() {
		var testCount = parseInt(jQuery('input[name="variation-count"]').val());

		jQuery(".split-test-for-elementor.test-form .button-add-variation").click(function () {

			if (!<?php echo(self::$licenceManager->hasActiveProLicence() ? 'true' : 'false'); ?>) {
				var currentTestCount = jQuery(".split-test-for-elementor.test-form .test-variations .row.variation").length;
				if (currentTestCount >= <?php echo(SPLIT_TEST_FOR_ELEMENTOR_LITE_MAX_VARIATION_COUNT); ?>) {
					alert("Please buy the pro version to add more Tests");
					return;
				}
			}

			testCount++;

			var template = jQuery("#test-variation-row-template").html();
			var html = template.replace(/VARIATION_ID/g, "null").replace(/TEST_COUNT/g, testCount);

			jQuery(".split-test-for-elementor.test-form .test-variations .row").last().after(html);

			registerTestVariationDelete();

		});
	}

	function registerResetStats() {
		jQuery(".button-reset-stats").click(function () {

			var confirmMessage = "<?php esc_html_e( 'Do you really want to reset the statistics? All view and conversion data will be gone and can not be recovered.', 'split-test-for-elementor' ); ?>";
			var result = confirm(confirmMessage);
			console.log(result);

			if (result) {
				jQuery("#reset-statistics-form").css("color", 'red');
				jQuery("#reset-statistics-form").submit();
			}

			return false;
		});
	}

	function registerFormSubmit() {
		jQuery(".test-form #test-form").submit(function () {
			var inputs = jQuery(this).find(".variation .percentage input");
			var percentageCount = 0;
			jQuery.each(inputs, function (index, value) {
				percentageCount += getInt(jQuery(value).val());
			});

			if (percentageCount != 100) {
				var message = "<?php esc_html_e( 'All variations are %PERCENTAGE% counted together but it has to be 100', 'split-test-for-elementor' ); ?>";
				alert(message.replace("%PERCENTAGE%", percentageCount));
				return false;
			}

			var currentTestCount = jQuery(".split-test-for-elementor.test-form .test-variations .row.variation").length;
			if (currentTestCount < 2) {
				alert("<?php esc_html_e( 'You have to setup at least two Test Variations', 'split-test-for-elementor' ); ?>");
				return false;
			}
		});
	}

	function changeTestTypeLayout(selector) {
		var val = jQuery(selector).val();
		var variationsWrapper = jQuery(".test-variations");
		if (val === "null") {
			jQuery(variationsWrapper).hide();
			jQuery(".test-uri-wrapper").hide();
		} else if (val === "elements") {
			jQuery(variationsWrapper).show();
			jQuery(variationsWrapper).addClass("test-variations-elements");
			jQuery(variationsWrapper).removeClass("test-variations-posts");
            jQuery(variationsWrapper).removeClass("test-variations-urls");
			jQuery(".test-uri-wrapper").hide();
		} else if (val === "pages") {
            jQuery(variationsWrapper).show();
            jQuery(variationsWrapper).addClass("test-variations-posts");
            jQuery(variationsWrapper).removeClass("test-variations-elements");
            jQuery(variationsWrapper).removeClass("test-variations-urls");
            jQuery(".test-uri-wrapper").show();
        }  else if (val === "urls") {
            jQuery(variationsWrapper).show();
            jQuery(variationsWrapper).addClass("test-variations-urls");
			jQuery(variationsWrapper).removeClass("test-variations-posts");
            jQuery(variationsWrapper).removeClass("test-variations-elements");
            jQuery(".test-uri-wrapper").show();
        }
	}

	function registerTestTypeChange() {
		jQuery('select[name="test-type"]').change(function () {
			changeTestTypeLayout(this);
		});
	}

	jQuery(document).ready(function() {

		registerTestVariationDelete();

		registerTestVariationAdd();

		registerFormSubmit();

		registerResetStats();

		registerTestTypeChange();

		changeTestTypeLayout(jQuery('select[name="test-type"]'));

		jQuery('input[name="test-uri"]').keypress(function () {
			onTestUriChanged(jQuery(this).val());
		});

		jQuery('input[name="test-uri"]').change(function () {
			onTestUriChanged(jQuery(this).val());
		});

		var conversionTypeSelector = jQuery('select[name="test-conversion-type"]');
		if (jQuery(conversionTypeSelector).val() === "widget") {
			jQuery(".test-conversion-page-wrapper").hide();
			jQuery(".test-conversion-code").hide();
			jQuery(".test-conversion-url-wrapper").hide();
			jQuery(".test-conversion-link-wrapper").hide();
		} else if (jQuery(conversionTypeSelector).val() === "external_page") {
			jQuery(".test-conversion-page-wrapper").hide();
			jQuery(".test-conversion-code").show();
			jQuery(".test-conversion-url-wrapper").hide();
			jQuery(".test-conversion-link-wrapper").hide();
		} else if (jQuery(conversionTypeSelector).val() === "url") {
			jQuery(".test-conversion-page-wrapper").hide();
			jQuery(".test-conversion-code").hide();
			jQuery(".test-conversion-url-wrapper").show();
			jQuery(".test-conversion-link-wrapper").hide();
		} else if (jQuery(conversionTypeSelector).val() === "external_link") {
			jQuery(".test-conversion-page-wrapper").hide();
			jQuery(".test-conversion-code").hide();
			jQuery(".test-conversion-url-wrapper").hide();
			jQuery(".test-conversion-link-wrapper").show();
		} else {
			jQuery(".test-conversion-page-wrapper").show();
			jQuery(".test-conversion-code").hide();
			jQuery(".test-conversion-url-wrapper").hide();
			jQuery(".test-conversion-link-wrapper").hide();
		}

		jQuery(conversionTypeSelector).change(function () {
			if (jQuery(this).val() === "widget") {
				alert("<?php esc_html_e( 'Conversion widgets are deprecated and should not be used anymore', 'split-test-for-elementor' ); ?>");
				jQuery(".test-conversion-page-wrapper").slideUp();
				jQuery('select[name="test-conversion-page"]').val("null");
				jQuery(".test-conversion-code").slideUp();
				jQuery(".test-conversion-url-wrapper").slideUp();
				jQuery(".test-conversion-link-wrapper").slideUp();
			} else if (jQuery(this).val() === "external_page") {
				jQuery(".test-conversion-page-wrapper").slideUp();
				jQuery('select[name="test-conversion-page"]').val("null");
				jQuery(".test-conversion-code").slideDown();
				jQuery(".test-conversion-url-wrapper").slideUp();
				jQuery(".test-conversion-link-wrapper").slideUp();
			} else if (jQuery(this).val() === "url") {
				jQuery(".test-conversion-page-wrapper").slideUp();
				jQuery('select[name="test-conversion-page"]').val("null");
				jQuery(".test-conversion-code").slideUp();
				jQuery(".test-conversion-link-wrapper").slideUp();
				jQuery(".test-conversion-url-wrapper").slideDown();
			} else if (jQuery(this).val() === "external_link") {
				jQuery(".test-conversion-page-wrapper").slideUp();
				jQuery('select[name="test-conversion-page"]').val("null");
				jQuery(".test-conversion-code").slideUp();
				jQuery(".test-conversion-url-wrapper").slideUp();
				jQuery(".test-conversion-link-wrapper").slideDown();
			} else {
				jQuery(".test-conversion-page-wrapper").slideDown();
				jQuery(".test-conversion-link-wrapper").slideUp();
				jQuery(".test-conversion-code").slideUp();
				jQuery(".test-conversion-url-wrapper").slideUp();
			}
		});

		jQuery("#external-page-tracking-code").focus(function() {
			jQuery("#external-page-tracking-code").select();
		});

	});

    function copyToClipboard(element) {
        var $temp = jQuery("<input>");
        jQuery("body").append($temp);
        $temp.val( jQuery(element).attr('href') ).select();
        document.execCommand("copy");
        $temp.remove();
    }
</script>

<div class="wrap split-test-for-elementor test-form split-test-for-elementor-test-form split-test-for-elementor-test-<?php echo($scope); ?>">
	<?php if ($scope == "edit") { ?>
		<h1><?php esc_html_e( 'Edit Split Test', 'split-test-for-elementor' ); ?>: <?php echo($test->name); ?></h1>
	<?php } else { ?>
		<h1><?php esc_html_e( 'Create Split Test', 'split-test-for-elementor' ); ?></h1>
	<?php } ?>

	<div class="content-wrapper">

		<form method="post" action="<?php echo $formUrl; ?>" id="test-form">
			<input name="nonce" type="hidden" value="<?php echo(wp_create_nonce('test-nonce')); ?>" />
			<label for="test-name"><?php esc_html_e( 'Test name', 'split-test-for-elementor' ); ?>:</label>
			<input id="test-name" name="test-name" type="text" value="<?php echo(isset($test->name) ? $test->name : ""); ?>" placeholder="<?php esc_html_e( 'Test Name', 'split-test-for-elementor' ); ?>" required /><br /><br />

			<h2><?php esc_html_e( 'Test type', 'split-test-for-elementor' ); ?>:</h2>
			<select name="test-type" style="width: 100%;">
				<?php if (!isset($test->test_type)) { ?>
					<option value="null" selected="selected"><?php esc_html_e( 'Please select test type ...', 'split-test-for-elementor' ); ?></option>
				<?php }?>
				<option value="elements" <?php if(isset($test->test_type) && $test->test_type == "elements") { echo('selected="selected"'); } ?>><?php esc_html_e( 'Elements (to test elements on one page)', 'split-test-for-elementor' ); ?></option>
				<option value="pages" <?php if(isset($test->test_type) && $test->test_type == "pages") { echo('selected="selected"'); } ?>><?php esc_html_e( 'Page (to test whole pages against each other)', 'split-test-for-elementor' ); ?></option>
				<option value="urls" <?php if(isset($test->test_type) && $test->test_type == "urls") { echo('selected="selected"'); } ?>><?php esc_html_e( 'Url (to test urls against each other)', 'split-test-for-elementor' ); ?></option>
			</select>
			<div class="test-uri-wrapper">
				<div class="headline" style="margin-top: 14px;"><?php esc_html_e( 'Split test url', 'split-test-for-elementor' ); ?>:</div>
				<div>
                    <?php echo( home_url('/')); ?>
                    <input name="test-uri" type="text" placeholder="identifier" pattern="^([A-Za-z0-9\-_\/]*)$" title="Only input letters, numbers, dashes and underscores" value="<?php echo(isset($test->test_uri) ? $test->test_uri : ""); ?>" style="width: 14em; display: inline-block;" />
                    <?php if(isset($test->test_type) && ($test->test_type == 'pages' || $test->test_type == 'urls')) { ?>
                        <div style="float: right;">
                            <a class="button button-primary" id="test-page-url" target="_blank" href="<?php echo home_url() . '/' . $test->test_uri . '/'; ?>"><?php esc_html_e( 'View Page', 'split-test-for-elementor' ); ?></a>
                            <a href="javascript:void(0)" title="Copy Split Test URL" class="button button-primary" onclick="copyToClipboard('#test-page-url')"><span style="line-height: 26px;" class="dashicons dashicons-admin-page"></span></a>
                        </div>
                    <?php } ?>
				</div>
			</div>
			<br /><br />

			<div class="test-variations">
				<h2><?php esc_html_e( 'Test Variations', 'split-test-for-elementor' ); ?>:</h2>
				<div class="button-primary button-add-variation">+ <?php esc_html_e( 'Add Variation', 'split-test-for-elementor' ); ?></div>
				<div class="row headline clearfix">
					<div class="name"><?php esc_html_e( 'Name', 'split-test-for-elementor' ); ?>:</div>
					<div class="post"><?php esc_html_e( 'Post', 'split-test-for-elementor' ); ?>:</div>
					<div class="url"><?php esc_html_e( 'Url', 'split-test-for-elementor' ); ?>:</div>
					<div class="percentage"><?php esc_html_e( 'Percentage', 'split-test-for-elementor' ); ?>:</div>
					<div class="actions">&nbsp;</div>
				</div>

				<?php $i = 0; foreach ($test->variations as $variation) { ?>
					<div class="row data variation" data-variation-id="<?php echo($variation->id); ?>">
						<div class="name">
							<input id="test-name" name="test-variation[<?php echo($i); ?>][name]" type="text" value="<?php echo($variation->name); ?>" placeholder="<?php esc_html_e( 'Name', 'split-test-for-elementor' ); ?>" required />
						</div>
                        <div class="post">
                            <select name="test-variation[<?php echo($i); ?>][post-id]">
                                <option value="null"></option>
								<?php foreach ($posts as $post) { ?>
                                    <option value="<?php echo($post->ID); ?>" <?php if(isset($variation->post_id) && $variation->post_id == $post->ID) { echo('selected="selected"'); } ?>><?php echo($post->post_title); ?></option>
								<?php } ?>
                            </select>
                        </div>

                        <div class="url">
                            <input type="text" placeholder="Add whole url with slash at last." name="test-variation[<?php echo($i); ?>][url]" value="<?php echo isset( $variation->url ) ? $variation->url : ''; ?>" />
                        </div>

						<div class="percentage">
							<input id="test-name" name="test-variation[<?php echo($i); ?>][percentage]" type="number" value="<?php echo($variation->percentage); ?>" placeholder="<?php esc_html_e( 'Percentage', 'split-test-for-elementor' ); ?>" required />
						</div>
						<div class="actions">
							<div class="button-primary button-delete">&times;</div>
							<input id="test-name" name="test-variation[<?php echo($i); ?>][id]" type="hidden" value="<?php echo($variation->id); ?>" required />
						</div>
					</div>
				<?php $i++; } ?>
			</div>
			<div>
				<h2><?php esc_html_e( 'Conversion', 'split-test-for-elementor' ); ?>:</h2>
				<div for="test-conversion-type"><?php esc_html_e( 'Conversion Type', 'split-test-for-elementor' ); ?>:</div>
				<select name="test-conversion-type" style="width: 100%;">
					<option value="page" <?php if(isset($test->conversion_type) && $test->conversion_type == "page") { echo('selected="selected"'); } ?>><?php esc_html_e( 'Page', 'split-test-for-elementor' ); ?></option>
					<option value="url" <?php if(isset($test->conversion_type) && $test->conversion_type == "url") { echo('selected="selected"'); } ?>><?php esc_html_e( 'Url', 'split-test-for-elementor' ); ?></option>
					<option value="external_link" <?php if(isset($test->conversion_type) && $test->conversion_type == "external_link") { echo('selected="selected"'); } ?>><?php esc_html_e( 'External Link', 'split-test-for-elementor' ); ?></option>
					<option value="external_page" <?php if(isset($test->conversion_type) && $test->conversion_type == "external_page") { echo('selected="selected"'); } ?>><?php esc_html_e( 'External Page', 'split-test-for-elementor' ); ?></option>
					<option value="widget" <?php if(isset($test->conversion_type) && $test->conversion_type == "widget") { echo('selected="selected"'); } ?>><?php esc_html_e( 'Conversion Widget (deprecated)', 'split-test-for-elementor' ); ?></option>
				</select>
				<div class="test-conversion-page-wrapper">
					<div for="test-conversion-type"><?php esc_html_e( 'Conversion Page', 'split-test-for-elementor' ); ?>:</div>
					<select name="test-conversion-page" style="width: 100%;">
						<option value="null"></option>
						<?php foreach ($posts as $post) { ?>
							<option value="<?php echo($post->ID); ?>" <?php if(isset($test->conversion_page_id) && $test->conversion_page_id == $post->ID) { echo('selected="selected"'); } ?>><?php echo($post->post_title); ?></option>
						<?php } ?>
					</select>
				</div>

				<div class="test-conversion-url-wrapper">
					<div for="test-conversion-type"><?php esc_html_e( 'Conversion Url', 'split-test-for-elementor' ); ?>:</div>
					<input type="text" name="test-conversion-url" value="<?php echo isset($test->conversion_url) && $test->conversion_url != null ? $test->conversion_url : '' ?>"
						   placeholder="<?php esc_html_e( 'A Url on your page like e.x. ', 'split-test-for-elementor' ); ?><?php echo get_home_url(); ?>" />
				</div>

				<div class="test-conversion-link-wrapper">
					<div for="test-conversion-type"><?php esc_html_e( 'External Link', 'split-test-for-elementor' ); ?>:</div>
					<input type="text" name="test-external-link" value="<?php echo isset($test->external_link) && $test->external_link != null ? $test->external_link : '' ?>"
						   placeholder="<?php esc_html_e( 'An external Url after opening the conversion the user will be redirect to', 'split-test-for-elementor' ); ?>" />

					<div for="test-conversion-type"><?php esc_html_e( 'Redirect Link', 'split-test-for-elementor' ); ?>:</div>
					<?php
					if (isset($test->id) && $test->id != null) {
						$trackingUrl = rtrim(get_home_url(), "/")."/split-test-for-elementor/v1/tests/".$test->id."/external-link-redirect/";
						?>
						<input type="text" name="tracking-url" readonly value="<?php echo $trackingUrl; ?>" />
						<?php
					} else {
						?>
						<div style="margin: 1em 0; font-size: 1.25em; color: red;">
							<?php esc_html_e( 'Please first save the test to get the test tracking link.', 'split-test-for-elementor'); ?>
						</div>
						<?php
					}
					?>

				</div>

				<div class="test-conversion-code">
					<?php esc_html_e( 'Tracking Code', 'split-test-for-elementor' ); ?>:
					<?php
						if (isset($test->id) && $test->id != null) {
							$trackingUrl = rtrim(get_home_url(), "/")."/split-test-for-elementor/v1/tests/".$test->id."/track-conversion/";
							?>
							<textarea readonly id="external-page-tracking-code"><img src="<?php echo($trackingUrl); ?>" alt="" /></textarea>
							<?php
						} else {
							?>
							<div style="margin: 1em 0; font-size: 1.25em; color: red;">
								<?php esc_html_e( 'Please first save the test to get the test tracking code.', 'split-test-for-elementor'); ?>
							</div>
							<?php
						}
					?>

				</div>
			</div>

			<input type="hidden" name="variation-count" value="<?php echo($i); ?>" />
			<input type="hidden" name="test-id" value="<?php echo($test->id); ?>" />
			<input class="button-primary button-save" type="submit" value="<?php esc_html_e( 'Save', 'split-test-for-elementor' ); ?>" />
			<?php if (isset($test->id) && $test->id != null) { ?>
				<input class="button-primary button-reset-stats button-delete" type="button" style="float: right; margin-top: 10px;" value="<?php esc_html_e( 'Reset Statistics', 'split-test-for-elementor' ); ?>" />
			<?php } ?>

		</form>

		<form method="post" action="<?php echo $resetStatsFormUrl; ?>" id="reset-statistics-form">
			<input name="nonce" type="hidden" value="<?php echo(wp_create_nonce('test-nonce')); ?>" />
			<input type="hidden" name="test-id" value="<?php echo($test->id); ?>" />
		</form>

		<?php if ($postsForTest != null && sizeof($postsForTest) > 0) { ?>
			<div class="test-pages">
				<h2><?php esc_html_e( 'Posts this test is used on', 'split-test-for-elementor' ); ?>:</h2>
				<?php foreach($postsForTest as $post) { ?>
					<div class="test-page">
						<a href="<?php echo(get_permalink($post->ID)); ?>" target="_blank"><?php echo($post->post_title); ?></a>
						[<a href="<?php echo(get_admin_url()."post.php?post=".$post->ID."&action=elementor"); ?>" target="_blank"><?php esc_html_e( 'edit', 'split-test-for-elementor' ); ?></a>]
					</div>
				<?php }?>
			</div>
		<?php } ?>

		<div style="display: none;" id="templates">
			<div id="test-variation-row-template">
				<div class="row data variation" data-variation-id="VARIATION_ID">
					<div class="name">
						<input id="test-name" name="test-variation[TEST_COUNT][name]" type="text" value="" placeholder="<?php esc_html_e( 'name', 'split-test-for-elementor' ); ?>" required />
					</div>
					<div class="post">
						<select name="test-variation[TEST_COUNT][post-id]">
							<option value="null"></option>
							<?php foreach ($posts as $post) { ?>
								<option value="<?php echo($post->ID); ?>" <?php if(isset($variation->post_id) && $variation->post_id == $post->ID) { echo('selected="selected"'); } ?>><?php echo($post->post_title); ?></option>
							<?php } ?>
						</select>
					</div>

					<div class="url">
						<input type="text" placeholder="Add whole url with slash at last." name="test-variation[VARIATION_ID][url]" value="" />
					</div>

					<div class="percentage">
						<input id="test-name" name="test-variation[TEST_COUNT][percentage]" type="number" value="" placeholder="<?php esc_html_e( 'percentage', 'split-test-for-elementor' ); ?>" required />
					</div>
					<div class="actions">
						<div class="button-primary button-delete">&times;</div>
						<input id="test-name" name="test-variation[TEST_COUNT][id]" type="hidden" value="null" />
					</div>
				</div>
			</div>
		</div>

	</div>
</div>