<?php

namespace SplitTestForElementor\Admin\Classes\Events;

use Elementor\Controls_Manager;
use Elementor\Element_Base;
use Elementor\Element_Section;
use Elementor\Widget_Base;
use SplitTestForElementor\Admin\Classes\Elementor\SplitTestControl;

class AfterSectionEndEvent {

	private static $registeredElements = [];

	public function fire($element, $section_id, $args) {

		$name = $element->get_name() . "-" . $element->get_id();

		if (isset(self::$registeredElements[$name])) {
			return;
		}

		/** @var \Elementor\Element_Base $element */
		if ('text-editor' === $element->get_name()) {
			if ('section_style' === $section_id) {
				$this->registerControls( $element );
			}
		}
		else if ('button' === $element->get_name()) {
			if ('section_style' === $section_id) {
				$this->registerControls( $element );
			}
		}
		else if ('image' === $element->get_name()) {
			if ('section_style_image' === $section_id) {
				$this->registerControls( $element );
			}
		}
		else if ('html' === $element->get_name()) {
			if ('section_title' === $section_id) {
				$this->registerControls( $element );
			}
		}
		else if ('heading' === $element->get_name()) {
			if ('section_title_style' === $section_id) {
				$this->registerControls( $element );
			}
		}
		else if ('section' === $element->get_name()) {
			if ('section_advanced' === $section_id) {
				$this->registerControls( $element );
			}
		} else if ('container' === $element->get_name()) {
			if ('section_shape_divider' === $section_id) {
				$this->registerControls( $element );
			}
		} else {
			if (!$element instanceof Widget_Base && !$element instanceof Element_Section) {
				return;
			}

			if ('section_custom_css' === $section_id) {
				$this->registerControls( $element );
			}
		}

		if ('section_custom_css' === $section_id || 'section_custom_css_pro' === $section_id) {
			$this->registerControls( $element );
		}
	}

	/**
	 * @param $element Element_Base
	 */
	private function registerControls( $element ) {

		$name = $element->get_name() . "-" . $element->get_id();
		self::$registeredElements[$name] = true;

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