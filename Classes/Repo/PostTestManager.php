<?php

namespace SplitTestForElementor\Classes\Repo;

class PostTestManager {

	/**
	 * @var PostTestRepo
	 */
	private static $postTestRepo;

	/**
	 * PostTestManager constructor.
	 */
	public function __construct() {
		if (self::$postTestRepo == null) {
			self::$postTestRepo = new PostTestRepo();
		}
	}

	public function onEditorSave($postId, $editorData) {
		$splitTestIds = [];
		$this->scanElements($postId, $editorData, $splitTestIds);
	}

	/**
	 * @param $postId
	 * @param $elements
	 * @param $splitTestIds
	 */
	private function scanElements( $postId, $elements, &$splitTestIds ) {
		foreach ($elements as $element) {
			if (isset($element['settings'] ) && isset($element['settings']['split_test_control_test_id'])) {
				$id = $element['settings']['split_test_control_test_id'];
				if (filter_var($id, FILTER_VALIDATE_INT)) {
					$splitTestIds[ (int) $id ] = true;
				}
			}
			if (isset($element['elements'])) {
				$this->scanElements($postId, $element['elements'], $splitTestIds);
			}
		}

		self::$postTestRepo->updateTestRegistry($postId, $splitTestIds);
	}

}