<?php

namespace SplitTestForElementor\Admin\Classes\Elementor;

use \Elementor\Widget_Base;
use SplitTestForElementor\Classes\Services\ConversionTracker;
use SplitTestForElementor\Classes\Repo\TestRepo;

class ConversionWidget extends Widget_Base {

	public function get_name() {
		return 'splittest_conversion_widget';
	}

	public function get_title() {
		return __('Splittest Conversion', 'plugin-name');
	}

	public function get_icon() {
		return 'fa fa-code';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	protected function register_controls() {

		$testRepo = new TestRepo();

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Split Test', 'plugin-name' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$tests = $testRepo->getAllTests();

		$testOptions = [0 => __('No Test', 'plugin-name')];
		foreach ($tests as $test) {
			$testOptions[$test->id] = $test->name;
		}

		$this->add_control(
			'split_test',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => __('Split Test', 'plugin-name'),
				'default' => 'No Test',
				'options' => $testOptions
			]
		);

		$this->end_controls_section();

	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		$testRepo = new TestRepo();
		$test = $testRepo->getTest((int) $settings['split_test']);

		if ($test != null) {

			global $clientId;

			$cookieName = "elementor_split_test_".$test->id."_variation";
			if(!isset($_COOKIE[$cookieName])) {
				return;
			}

			$conversionTracker = new ConversionTracker();
			$variationId = (int) $_COOKIE[$cookieName];
			foreach ($test->variations as $variation) {
				if ($variationId == $variation->id) {
					$conversionTracker->trackConversion($test->id, $variationId, $clientId);
				}
			}

		}

	}

}