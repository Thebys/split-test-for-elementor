<?php

namespace SplitTestForElementor\Classes\Repo;


class PostTestRepo {

	public function updateTestRegistry($postId, $splitTestIds) {

		global $wpdb;

		$splitTestPostTable = $this->getTestPostTable($wpdb);

		$wpdb->delete($splitTestPostTable, array('post_id' => $postId));

		foreach ($splitTestIds as $splitTestId => $active) {
			$result = $wpdb->insert($splitTestPostTable, array(
				'splittest_id' => $splitTestId,
				'post_id' => $postId,
				'created_at' => current_time('mysql')
			), array('%d', '%d', '%s'));
		}

	}

	public function getPostsForTest($testId){
		global $wpdb;
		$query[] = "SELECT * FROM ".$this->getTestPostTable($wpdb);
		$query[] = "INNER JOIN ".$wpdb->prefix."posts ON ".$this->getTestPostTable($wpdb).".post_id = ".$wpdb->prefix."posts.ID";
		$query[] = "WHERE splittest_id = ".$testId;
		return $wpdb->get_results(implode(" ", $query), OBJECT);
	}

	public function getTestIdsForPost($postId) {
		global $wpdb;

		$postsTests = $wpdb->get_results("SELECT * FROM ".$this->getTestPostTable($wpdb)." WHERE post_id = ".$postId, OBJECT);

		$testIds = [];
		foreach ($postsTests as $postsTest) {
			$testIds[] = $postsTest->splittest_id;
		}

		return $testIds;
	}

	/**
	 * Find test IDs from Elementor templates whose display conditions
	 * match a given post type.  Covers tests placed inside Elementor Pro
	 * Theme Builder templates (not directly associated with a page post ID).
	 */
	public function getTestIdsFromMatchingTemplates($postType) {
		global $wpdb;

		$table = $this->getTestPostTable($wpdb);
		$templatePostIds = $wpdb->get_col(
			"SELECT DISTINCT stp.post_id
			 FROM {$table} stp
			 JOIN {$wpdb->prefix}posts p ON stp.post_id = p.ID
			 WHERE p.post_type = 'elementor_library'"
		);

		if (empty($templatePostIds)) {
			return [];
		}

		$matchingTestIds = [];
		foreach ($templatePostIds as $templateId) {
			$conditions = get_post_meta($templateId, '_elementor_conditions', true);
			if ($this->templateConditionsMatchPostType($conditions, $postType)) {
				$matchingTestIds = array_merge($matchingTestIds, $this->getTestIdsForPost($templateId));
			}
		}

		return array_unique($matchingTestIds);
	}

	private function templateConditionsMatchPostType($conditions, $postType) {
		if (empty($conditions) || !is_array($conditions)) {
			return false;
		}

		foreach ($conditions as $condition) {
			$parts = explode('/', $condition);
			// Elementor Pro format: {include|exclude}/{general|singular|archive}/{post_type}
			if ($parts[0] !== 'include') {
				continue;
			}
			if (isset($parts[1]) && $parts[1] === 'general') {
				return true;
			}
			if (isset($parts[1]) && $parts[1] === 'singular') {
				if (!isset($parts[2]) || $parts[2] === $postType) {
					return true;
				}
			}
		}

		return false;
	}

	public function deletePostTestByTestId($splitTestID) {
		global $wpdb;
		$wpdb->delete($this->getTestPostTable($wpdb), ['splittest_id' => $splitTestID], ['%d']);
	}

	private function getTestPostTable($wpdb) {
		return $wpdb->prefix . "elementor_splittest_post";
	}

}