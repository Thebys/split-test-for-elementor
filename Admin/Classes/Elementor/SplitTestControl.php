<?php

namespace SplitTestForElementor\Admin\Classes\Elementor;

use SplitTestForElementor\Classes\Repo\PostRepo;
use SplitTestForElementor\Classes\Repo\TestRepo;

class SplitTestControl {

	public function render($element) {

		$elementType = "generic";
		if (get_class($element) == "ElementorPro\Modules\Forms\Widgets\Form") {
			$elementType = "form";
		}

		$testRepo = new TestRepo();
		$tests = $testRepo->getAllTests();

		$postRepo = new PostRepo();
		$postData = [];
		foreach ($postRepo->getAllPosts() as $post) {
			$postData[] = [
				'id' => $post->ID,
				'postTitle' => $post->post_title
			];
		}

		$testData = [];
		foreach ($tests as $test) {
			$variations = [];
			foreach ($test->variations as $variation) {
				$variations[] = [
					'id' => $variation->id,
					'percentage' => $variation->percentage,
					'name' => $variation->name
				];
			}
			$testData[] = [
				'id' => $test->id,
				'name' => $test->name,
				'variations' => $variations
			];
		}

		ob_start();
		?>

		<style>
			.split-test-for-elementor-hint {
				background: #ff6600;
				height: auto;
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				padding: 0.5em;
				color: white;
				text-align: center;
				font-size: 20px;
			}

			.split-test-for-elementor-hint a {
				color: white;
			}

			.split-test-for-elementor-hint a:hover {
				color: #eeeeee;
			}
			.split-test-for-elementor-hint .close {
				cursor: pointer;
				position: absolute;
				font-size: 1.5em;
				top: 0.1em;
				right: 0.4em;
			}

			.elementor-control-split_test_control_html:before {
				height: 0 !important;
				margin-bottom: 0 !important;
			}
			.elementor-control-split_test_control_html > .elementor-control-content > .elementor-control-title {
				display: none;
			}
			.split-test-for-elementor-control {
				padding: 0 0 15px 0;
			}
			.split-test-for-elementor-control:before {
				background-color: white !important;
			}
			.split-test-for-elementor-no-top-border:before {
				height: 0 !important;
				margin-bottom: 0 !important;
			}
			.elementor-button.elementor-button-success {
				color: #fff;
				font-size: 11px;
				padding: 6.5px 15px;
			}
		</style>
		<div class="split-test-for-elementor-control-wrapper" data-initialized="false">
			<div class="elementor-control elementor-control-type-select elementor-label-inline elementor-control-separator-default split-test-for-elementor-control split-test-for-elementor-test-control">
				<div class="elementor-control-content">
					<div class="elementor-control-field">
						<label class="elementor-control-title"><?php esc_html_e( 'Split Test', 'split-test-for-elementor' ); ?></label>
						<div class="elementor-control-input-wrapper">
							<select id="split-test-for-elementor-test-selector">
							</select>
						</div>
					</div>
				</div>
			</div>

			<div class="split-test-for-elementor-add-new-test-wrapper">
				<div class="elementor-control elementor-control-type-text elementor-label-inline elementor-control-separator-default split-test-for-elementor-control split-test-for-elementor-no-top-border">
					<div class="elementor-control-content">
						<div class="elementor-control-field">
							<label for="elementor-control-default-c662" class="elementor-control-title"><?php esc_html_e( 'New test name', 'split-test-for-elementor' ); ?>:</label>
							<div class="elementor-control-input-wrapper">
								<input id="split-test-for-elementor-test-name-input" type="text" class="tooltip-target elementor-control-tag-area" data-tooltip="" original-title="" />
							</div>
						</div>
					</div>
				</div>
				<?php if ($elementType == "form") { ?>
				<?php } else { ?>
				<div class="elementor-control elementor-control-type-text elementor-label-inline elementor-control-separator-default split-test-for-elementor-control split-test-for-elementor-no-top-border">
					<div class="elementor-control-content">
						<div class="elementor-control-field">
							<label for="elementor-control-default-c662" class="elementor-control-title"><?php esc_html_e( 'Conversion Type', 'split-test-for-elementor' ); ?>:</label>
							<div class="elementor-control-input-wrapper">
								<select id="split-test-for-elementor-conversion-type-selector">
									<option value="page" selected="selected"><?php esc_html_e( 'Page', 'split-test-for-elementor' ); ?></option>
									<option value="url" selected="selected"><?php esc_html_e( 'Url', 'split-test-for-elementor' ); ?></option>
									<option value="external_page"><?php esc_html_e( 'External Page', 'split-test-for-elementor' ); ?></option>
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="elementor-control elementor-control-type-text elementor-label-inline elementor-control-separator-default split-test-for-elementor-control split-test-for-elementor-no-top-border split-test-for-elementor-conversion-page-wrapper">
					<div class="elementor-control-content">
						<div class="elementor-control-field">
							<label for="elementor-control-default-c662" class="elementor-control-title"><?php esc_html_e( 'Conversion Page', 'split-test-for-elementor' ); ?>:</label>
							<div class="elementor-control-input-wrapper">
								<select id="split-test-for-elementor-conversion-page-selector">
									<option value="null"></option>
									<?php foreach ($postData as $post) { ?>
										<option value="<?php echo($post['id']); ?>"><?php echo($post['postTitle']); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="elementor-control elementor-control-type-text elementor-label-inline elementor-control-separator-default split-test-for-elementor-control split-test-for-elementor-no-top-border split-test-for-elementor-conversion-url-wrapper">
					<div class="elementor-control-content">
						<div class="elementor-control-field">
							<label for="elementor-control-default-c662" class="elementor-control-title"><?php esc_html_e( 'Conversion Url', 'split-test-for-elementor' ); ?>:</label>
							<div class="elementor-control-input-wrapper">
								<input id="split-test-for-elementor-conversion-url" type="text" />
							</div>
						</div>
					</div>
				</div>
				<?php } ?>
				<?php $trackingUrl = !isset($test) || $test == null ? "" : rtrim(get_home_url(), "/")."/split-test-for-elementor/v1/tests/".$test->id."/track-conversion/"; ?>
				<div class="elementor-control elementor-control-type-text elementor-label-inline elementor-control-separator-default split-test-for-elementor-control split-test-for-elementor-no-top-border split-test-for-elementor-tracking-code-wrapper">
					<div class="elementor-control-content">
						<div class="elementor-control-field">
							<label for="elementor-control-default-c662" class="elementor-control-title"><?php esc_html_e( 'Tracking Code', 'split-test-for-elementor' ); ?>:</label>
							<div class="elementor-control-input-wrapper">
								<textarea readonly id="external-page-tracking-code"><?php echo($trackingUrl); ?></textarea>
							</div>
						</div>
					</div>
				</div>
				<button type="button" class="elementor-button split-test-for-elementor-save-test-control elementor-button-success" data-event="" data-text="<?php esc_html_e( 'Save new test', 'split-test-for-elementor' ); ?>" style="width: 100%;"></button>
			</div>

			<div class="elementor-control elementor-control-type-select elementor-label-inline elementor-control-separator-default split-test-for-elementor-control split-test-for-elementor-variation-control">
				<div class="elementor-control-content">
					<div class="elementor-control-field">
						<label class="elementor-control-title"><?php esc_html_e( 'Split Test Variation', 'split-test-for-elementor' ); ?></label>
						<div class="elementor-control-input-wrapper">
							<select id="split-test-for-elementor-variation-selector">
							</select>
						</div>
					</div>
				</div>
			</div>

			<div class="split-test-for-elementor-add-new-variation-wrapper">
				<div class="elementor-control elementor-control-type-text elementor-label-inline elementor-control-separator-default split-test-for-elementor-control split-test-for-elementor-no-top-border">
					<div class="elementor-control-content">
						<div class="elementor-control-field">
							<label for="elementor-control-default-c662" class="elementor-control-title"><?php esc_html_e( 'New variation name', 'split-test-for-elementor' ); ?>:</label>
							<div class="elementor-control-input-wrapper">
								<input type="text" class="tooltip-target elementor-control-tag-area" id="split-test-for-elementor-new-variation-name" data-tooltip="" original-title="" />
							</div>
						</div>
					</div>
				</div>
				<div class="elementor-control elementor-control-type-text elementor-label-inline elementor-control-separator-default split-test-for-elementor-control split-test-for-elementor-no-top-border">
					<div class="elementor-control-content">
						<div class="elementor-control-field">
							<label for="elementor-control-default-c662" class="elementor-control-title"><?php esc_html_e( 'Percentage', 'split-test-for-elementor' ); ?>:</label>
							<div class="elementor-control-input-wrapper">
								<input type="number" class="tooltip-target elementor-control-tag-area" id="split-test-for-elementor-new-variation-percentage" data-tooltip="" original-title="" />
							</div>
						</div>
					</div>
				</div>
				<button type="button" class="elementor-button split-test-for-elementor-save-variation-control elementor-button-success" data-event="" data-text="<?php esc_html_e( 'Save new variation', 'split-test-for-elementor' ); ?>" style="width: 100%;"></button>
			</div>

		</div>

		<?php 

			$testDataCleaned = str_replace("'", "", json_encode( $testData ));
			$testDataCleaned = str_replace("´", "", $testDataCleaned );
			$testDataCleaned = str_replace("`", "", $testDataCleaned );
			$postDataCleaned = str_replace("'", "", json_encode( $postData ));
			$postDataCleaned = str_replace("´", "", $postDataCleaned );
			$postDataCleaned = str_replace("`", "", $postDataCleaned );

		?>

			<script type="text/javascript" id="split-test-for-elementor-jscode">
			window.splitTestForElementor.data = {
				testData: <?php echo($testDataCleaned); ?>,
				postData: <?php echo( $postDataCleaned ); ?>,
				wpNonce: "<?php echo(wp_create_nonce('wp_rest')); ?>"
			};

			window.splitTestForElementor.start('<?php echo($testDataCleaned); ?>', '<?php echo( $postDataCleaned ); ?>', '<?php echo(wp_create_nonce('wp_rest')); ?>');
			window.splitTestForElementor.init();
		</script>

		<?php
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

}