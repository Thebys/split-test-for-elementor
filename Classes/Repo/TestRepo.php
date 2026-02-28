<?php

namespace SplitTestForElementor\Classes\Repo;

class TestRepo {

	public function getAllTests($withInActive = false, $withDeleted = false) {
		global $wpdb;

		$query = [];
		$query[] = "SELECT * FROM ".$this->getTestTable();
		if (!$withInActive) {
			$query[] = "WHERE ".$this->getTestTable().".active IS TRUE";
		}

		$tests = $wpdb->get_results(implode(" ", $query), OBJECT);

		for ($i = 0; $i < sizeof($tests); $i++) {
			$tests[$i]->variations = $this->getVariations($tests[$i]->id, $withInActive, $withDeleted);
		}

		return $tests;
	}

	public function getTest($testId, $withInActive = false, $withDeleted = false) {
		$tests = $this->getTests([$testId], $withInActive, $withDeleted);
		return sizeof($tests) > 0 ? $tests[0] : null;
	}

	public function getTests($ids, $withInActive = false, $withDeleted = false) {
		if (sizeof($ids) == 0) {
			return [];
		}

		global $wpdb;
		$tests = $wpdb->get_results("SELECT * FROM ".$this->getTestTable()." WHERE id IN(".implode(",", $ids).")", OBJECT);

		for ($i = 0; $i < sizeof($tests); $i++) {
			$tests[$i]->variations = $this->getVariations($tests[$i]->id, $withInActive, $withDeleted);
		}

		return $tests;
	}

	public function getTestsByConversionPagePostId($postId) {
		global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT * FROM ".$this->getTestTable()." WHERE conversion_page_id = '%d' AND conversion_type = 'page'",
            [ $postId ]
        );

		$tests = $wpdb->get_results($sql, OBJECT);

		for ($i = 0; $i < sizeof($tests); $i++) {
			$tests[$i]->variations = $this->getVariations($tests[$i]->id, false, false);
		}

		return $tests;
	}

	public function getRedirectTestsByUri($uri) {
		global $wpdb;

        $uri = str_replace("*", "%", $uri);
        $sql = $wpdb->prepare(
            "SELECT * FROM ".$this->getTestTable()." WHERE test_uri = '%s'",
            [ $uri ]
        );
		$tests = $wpdb->get_results($sql, OBJECT);

		for ($i = 0; $i < sizeof($tests); $i++) {
			$tests[$i]->variations = $this->getVariations($tests[$i]->id, false, false);
		}

		return $tests;
	}

	public function getVariations($postId, $withInActive = false, $withDeleted = false) {
		global $wpdb;

		$query = [];
		$query[] = "SELECT * FROM ".$this->getVariationTable();
		if (!$withInActive && !$withDeleted) {
			$query[] = "WHERE ".$this->getVariationTable().".active IS TRUE";
			$query[] = "AND ".$this->getVariationTable().".deleted_at IS NULL";
			$query[] = "AND splittest_id = ".$postId;
		} else if (!$withInActive && $withDeleted) {
			$query[] = "WHERE ".$this->getVariationTable().".active IS TRUE";
			$query[] = "AND splittest_id = ".$postId;
		} else if ($withInActive && !$withDeleted) {
			$query[] = "WHERE ".$this->getVariationTable().".deleted_at IS NULL";
			$query[] = "AND splittest_id = ".$postId;
		} else {
			$query[] = "WHERE splittest_id = ".$postId;
		}

		$results = $wpdb->get_results(implode(" ", $query), OBJECT);
		foreach ($results as $result) {
			$result->post_id = (int) $result->post_id;
		}

		return $results;
	}

	// TODO@kberlau: Trim conversion url on save
	public function updateTest($id, $data) {
		global $wpdb;

		$wpdb->update($this->getTestTable(), [
			'name' => $data['name'],
			'test_type' => $data['testType'],
			'test_uri' => $data['testType'] == "pages" || $data['testType'] == "urls" ? $data['testUri'] : null,
			'conversion_type' => $data['conversionType'],
			'conversion_page_id' => $data['conversionPageId'],
			'external_link' => isset($data['externalLink']) ? $data['externalLink'] : "",
			'conversion_url' => $this->normalizeConversionUrl($data['conversionUrl']),
		], ['id' => $id], ['%s', '%s', '%s'], ['%d']);
	}

	public function createTest($data) {
		global $wpdb;
		$result = $wpdb->insert($this->getTestTable(), array(
			'name' => $data['name'],
			'active' => true,
			'test_type' => $data['testType'],
			'test_uri' => $data['testType'] == "pages" || $data['testType'] == "urls" ? $data['testUri'] : null,
			'conversion_type' => $data['conversionType'],
			'conversion_page_id' => $data['conversionPageId'],
			'conversion_url' => $this->normalizeConversionUrl($data['conversionUrl']),
			'external_link' => isset($data['externalLink']) ? $data['externalLink'] : "",
			'created_at' => current_time('mysql')
		), array('%s'));
		return $wpdb->insert_id;
	}

	public function resetTestStatistics($id) {
		global $wpdb;
		$test = $this->getTest($id, true, true);
		if ($test == null) {
			return;
		}

		$wpdb->delete($this->getTestIntetractionsTable(), ['splittest_id' => $id], ['%d']);
	}

	public function deleteTest($id) {
		global $wpdb;
		$test = $this->getTest($id, true, true);
		if ($test == null) {
			return;
		}
		if (sizeof($test->variations) > 0) {
			foreach ($test->variations as $variation) {
				$this->deleteTestVariation($variation->id);
			}
		}
		$postTestRepo = new PostTestRepo();
		$postTestRepo->deletePostTestByTestId($test->id);
		$this->deleteTestInteractions($test->id);
		$wpdb->delete($this->getTestTable(), ['id' => $id], ['%d']);
	}

	public function deleteTestInteractions($splitTestID) {
		global $wpdb;
		$wpdb->delete($this->getTestIntetractionsTable(), ['splittest_id' => $splitTestID], ['%d']);
	}

	public function createTestVariation($testId, $data) {
		global $wpdb;
		$result = $wpdb->insert($this->getVariationTable(), array(
			'name' => $data['name'],
			'percentage' => (int) $data['percentage'],
			'post_id' => isset($data['postId']) ? (int) $data['postId'] : null,
			'url' => isset($data['url']) ? $data['url'] : null,
			'splittest_id' => $testId,
			'active' => true,
			'created_at' => current_time('mysql')
		), array('%s', '%d', '%d', '%s', '%d'));
		return $wpdb->insert_id;
	}

	public function updateTestVariation($id, $data) {
		global $wpdb;
		$wpdb->update($this->getVariationTable(),
			['name' => $data['name'], 'percentage' => (int) $data['percentage'], 'post_id' => $data['postId'], 'url' => $data['url']],
			['id' => $id],
			['%s', '%d', '%s'], ['%d']
		);
	}

	public function softDeleteTestVariation($id) {
		global $wpdb;
		$wpdb->update($this->getVariationTable(),
			['deleted_at' => current_time( 'mysql' )],
			['id' => $id],
			['%s'],
			['%d']
		);
	}


	public function getTestsByConversionUrl($conversionUrl)
	{
		$conversionUrl = $this->normalizeConversionUrl($conversionUrl);

		global $wpdb;
		$tests = $wpdb->get_results("SELECT * FROM ".$this->getTestTable()." WHERE conversion_url = '".$conversionUrl."' AND conversion_type = 'url'", OBJECT);

		for ($i = 0; $i < sizeof($tests); $i++) {
			$tests[$i]->variations = $this->getVariations($tests[$i]->id, false, false);
		}

		return $tests;
	}

	public function deleteTestVariation($id) {
		global $wpdb;
		$wpdb->delete($this->getVariationTable(), ['id' => $id], ['%d']);
	}

	private function getTestTable() {
		global $wpdb;
		return $wpdb->prefix.'elementor_splittest';
	}

	private function getVariationTable() {
		global $wpdb;
		return $wpdb->prefix.'elementor_splittest_variations';
	}

	private function getTestIntetractionsTable() {
		global $wpdb;
		return $wpdb->prefix.'elementor_splittest_interactions';
	}

	/**
	 * @param $conversionUrl
	 * @return string
	 */
	private function normalizeConversionUrl($conversionUrl)
	{
		if ($conversionUrl == null) {
			return null;
		}
		return rtrim($conversionUrl, "/") . "/";
	}

}