<?php

use SplitTestForElementor\Admin\Classes\Misc\ColorUtil;

$chartData = ['datasets' => []];
$i = 0;
foreach ($statsForChart as $dataEntry) {
	$set = [
		'label' => $dataEntry['name'],
		'borderColor' => ColorUtil::getColor($i + 1),
		'backgroundColor' => ColorUtil::getColor($i + 1, 0.2),
		'data' => []
	];
	foreach ($dataEntry['stats'] as $key => $statEntry) {
		$set['data'][] = ['x' => $key, 'y' => $statEntry];
	}
	$chartData['datasets'][] = $set;
	$i++;
}

$sortedVariations = $variations;

$i = 0;
$doughnutData = ['datasets' => [['data' => []]], 'labels' => []];
foreach ($sortedVariations as $variation) {
	$doughnutData['datasets'][0]['data'][] = $variation->conversionRate;
	$doughnutData['datasets'][0]['backgroundColor'][] = ColorUtil::getColor($i + 1);
	$doughnutData['labels'][] = $variation->name;
	$i++;
}

?>

<div class="split-test-for-elementor-statistics">
	<h1><?php esc_html_e( 'Split Testing', 'split-test-for-elementor' ); ?></h1>

	<div class="messages">
		<?php if (isset($message) && $message == "end_date_to_small") { ?>
			<div class="notice notice-warning is-dismissible">
				<p><?php esc_html_e( 'The end Date is smaller then the start Date. Resetting to last month.', 'split-test-for-elementor' ); ?></p>
			</div>
		<?php } ?>
		<?php if (isset($message) && $message == "invalid_date_input") { ?>
			<div class="notice notice-warning is-dismissible">
				<p><?php esc_html_e( 'The given dates are not valid', 'split-test-for-elementor' ); ?></p>
			</div>
		<?php } ?>
	</div>

	<?php //LOW@kberlau Multi Test Overflow ?>
	<div class="split-test-variation-wrapper">
		<?php for($i = 0; $i < sizeof($variations); $i++) { $variation = $variations[$i]; ?>
			<div class="variation-box variation-box-<?php echo($i + 1); ?>" style="background: <?php echo(ColorUtil::getColor($i + 1));  ?>">
				<div class="variation-content">
					<div class="headline"><?php echo($variation->name); ?></div>
					<!-- <div class="options">&times;</div> -->
					<div class="conversion-rate"><?php echo($variation->conversionRate); ?>%</div>
					<div class="ranking">#<?php echo($variation->ranking); ?></div>
				</div>
			</div>
		<?php } ?>

		<?php if (!self::$licenceManager->hasActiveProLicence()) { ?>
			<div class="variation-box variation-box-promo">
				<div class="variation-content">
					<div class="headline"><?php esc_html_e( 'More variations', 'split-test-for-elementor' ); ?></div>
					<div class="options"><?php esc_html_e( 'Pro Version', 'split-test-for-elementor' ); ?></div>
					<div class="center-wrapper"><a class="cta-button" href="<?php echo(SPLIT_TEST_FOR_ELEMENTOR_PRO_VERSION_LINK); ?>"><?php esc_html_e( 'unlock now', 'split-test-for-elementor' ); ?></a></div>
				</div>
			</div>
		<?php } ?>

	</div>

	<div class="split-test-line-chart">
		<canvas id="lineChart" width="400" height="150"></canvas>
	</div>

	<?php // LOW@kberlau Input Validation ?>
	<div class="split-test-date-range">
		<h2><?php esc_html_e( 'Timerange', 'split-test-for-elementor' ); ?></h2>
		<p class="hint"><?php esc_html_e( 'Select your analytics period', 'split-test-for-elementor' ); ?></p>
		<form action="<?php echo htmlentities($_SERVER["REQUEST_URI"]); ?>" method="post">
			<input name="nonce" type="hidden" value="<?php echo(wp_create_nonce('test-nonce')); ?>" />
			<div class="form-row">
				<label>Start:</label>
				<input class="date-year" type="text" name="start_date_year" placeholder="YYYY" value="<?php echo($startDate != null ? date("Y", strtotime($startDate)) : ""); ?>" />
				<input class="date-month" type="text" name="start_date_month" placeholder="MM" value="<?php echo($startDate != null ? date("m", strtotime($startDate)) : ""); ?>" />
				<input class="date-day" type="text" name="start_date_day" placeholder="DD" value="<?php echo($startDate != null ? date("d", strtotime($startDate)) : ""); ?>" />
			</div>
			<div class="form-row">
				<label>End:</label>
				<input class="date-year" type="text" name="end_date_year" placeholder="YYYY" value="<?php echo($endDate != null ? date("Y", strtotime($endDate)) : ""); ?>" />
				<input class="date-month" type="text" name="end_date_month" placeholder="MM" value="<?php echo($endDate != null ? date("m", strtotime($endDate)) : ""); ?>" />
				<input class="date-day" type="text" name="end_date_day" placeholder="DD" value="<?php echo($endDate != null ? date("d", strtotime($endDate)) : ""); ?>" />
			</div>
			<div class="form-row">
				<!-- <div class="set-hole-range-button date-range-button">Set hole range</div>-->
				<button class="date-range-button"><?php esc_html_e( 'Load data', 'split-test-for-elementor' ); ?></button>
			</div>

		</form>
	</div>

	<div class="split-test-doughnut-chart">
		<canvas id="doughnutChart" width="400" height="200"></canvas>
	</div>

	<script>
		var chartCtx = document.getElementById("lineChart").getContext('2d');
		var scatterChart = new Chart(chartCtx, {
			type: 'line',
			data: <?php echo(json_encode($chartData)); ?>,
			options: {
				scales: {
					xAxes: [{
						type: 'time',
						time: {
							unit: "day",
							displayFormats: {
								day: 'YYYY-MM-DD'
							}
						}
					}]
				}
			}
		});

		var doughnutCtx = document.getElementById("doughnutChart").getContext('2d');
		var doughnutChart = new Chart(doughnutCtx, {
			type: 'doughnut',
			data: <?php echo(json_encode($doughnutData)); ?>,
			options: {
				cutoutPercentage: 50
			}
		});

	</script>

	<div class="wp-clearfix"></div>

	<div class="split-test-data-table">
		<table>
			<tr>
				<th width="2%" class="indicator">&nbsp;</th>
				<th><?php esc_html_e( 'Variation', 'split-test-for-elementor' ); ?></th>
				<th><?php esc_html_e( 'Percentage', 'split-test-for-elementor' ); ?></th>
				<th><?php esc_html_e( 'Views', 'split-test-for-elementor' ); ?></th>
				<th><?php esc_html_e( 'Conversions', 'split-test-for-elementor' ); ?></th>
				<th><?php esc_html_e( 'Conversion Rate', 'split-test-for-elementor' ); ?></th>
			</tr>
			<?php foreach ($variations as $key => $variation) { ?>
				<tr>
					<td class="indicator" style="background: <?php echo(ColorUtil::getColor($key + 1)); ?>">&nbsp;</td>
					<td><?php echo($variation->name); ?></td>
					<td><?php echo($variation->percentage); ?></td>
					<td><?php echo($variation->allViews); ?></td>
					<td><?php echo($variation->allConversions); ?></td>
					<td><?php echo($variation->conversionRate); ?>%</td>
				</tr>
			<?php } ?>
			<?php if (!self::$licenceManager->hasActiveProLicence()) { ?>
				<tr class="promo-row">
					<td class="indicator">&nbsp;</td>
					<td class="promo-headline"><?php esc_html_e( 'More variations', 'split-test-for-elementor' ); ?></td>
					<td class="promo-text"><?php esc_html_e( 'Pro Version', 'split-test-for-elementor' ); ?></td>
					<td class="promo-text"><?php esc_html_e( 'Pro Version', 'split-test-for-elementor' ); ?></td>
					<td class="promo-text"><?php esc_html_e( 'Pro Version', 'split-test-for-elementor' ); ?></td>
					<td class="promo-button"><a href="<?php echo(SPLIT_TEST_FOR_ELEMENTOR_PRO_VERSION_LINK); ?>" class="cta-link"><?php esc_html_e( 'unlock now', 'split-test-for-elementor' ); ?></a></td>
				</tr>
			<?php } ?>
		</table>
	</div>

</div>