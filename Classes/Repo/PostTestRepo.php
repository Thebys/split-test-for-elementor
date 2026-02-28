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

	public function deletePostTestByTestId($splitTestID) {
		global $wpdb;
		$wpdb->delete($this->getTestPostTable($wpdb), ['splittest_id' => $splitTestID], ['%d']);
	}

	private function getTestPostTable($wpdb) {
		return $wpdb->prefix . "elementor_splittest_post";
	}

}