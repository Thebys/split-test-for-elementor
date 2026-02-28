<?php

namespace SplitTestForElementor\Admin\Classes\Events;

use Elementor\Controls_Manager;
use Elementor\Element_Base;
use SplitTestForElementor\Admin\Classes\Elementor\SplitTestControl;

class AfterWidgetEndEvent {

	public function fire($element, $section_id, $args) {



		/** @var \Elementor\Element_Base $element */
		if ('text-editor' === $element->get_name() && 'section_style' === $section_id) {
			 $this->registerControls( $element );
		}
		else if ('button' === $element->get_name() && 'section_style' === $section_id) {
			$this->registerControls( $element );
		}
		else if ('image' === $element->get_name() && 'section_style_image' === $section_id) {
			$this->registerControls( $element );
		}
		else if ('html' === $element->get_name() && 'section_title' === $section_id) {
			$this->registerControls( $element );
		}
		else if ('heading' === $element->get_name() && 'section_title_style' === $section_id) {
			$this->registerControls( $element );
		}
		else if ('section' === $element->get_name() && 'section_advanced' === $section_id) {
			$this->registerControls( $element );
		}
	}

	/**
	 * @param $element Element_Base
	 */
	private function registerControls( $element ) {

		$element->start_controls_section(
			'split_test_section',
			[
				'tab'   => Controls_Manager::TAB_ADVANCED,
				'label' => __( 'Split Test', 'plugin-name' )
			]
		);

		$element->add_control(
			'split_test_control_test_id',
			[
				'type'    => Controls_Manager::HIDDEN,
				'default' => null
			]
		);

		$element->add_control(
			'split_test_control_variation_id',
			[
				'type'    => Controls_Manager::HIDDEN,
				'default' => null
			]
		);

		$control = new SplitTestControl();

		$element->add_control(
			'split_test_control_html',
			[
				'type'    => Controls_Manager::RAW_HTML,
				'raw'     => $control->render($element),
				'label'   => __( 'Split Test', 'plugin-name' ),
				'default' => null
			]
		);

		$element->end_controls_section();
	}

}