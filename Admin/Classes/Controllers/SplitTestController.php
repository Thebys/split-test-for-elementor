<?php

namespace SplitTestForElementor\Admin\Classes\Controllers;

use SplitTestForElementor\Classes\Misc\Util;
use SplitTestForElementor\Classes\Repo\PostRepo;
use SplitTestForElementor\Classes\Repo\PostTestRepo;
use SplitTestForElementor\Classes\Repo\TestRepo;

class SplitTestController {

	public function run() {
		switch (isset($_GET['action']) ? $_GET['action'] : "index") {
			case "index"    		:  $this->index(); break;
			case "create"   		:  $this->create(); break;
			case "show"     		:  $this->show(); break;
			case "edit"     		:  $this->edit(); break;
			default         		:  $this->index(); break;
		}
	}

	public function index() {
		$testRepo = new TestRepo();
		$tests = $testRepo->getAllTests();
		include(__DIR__."/../../views/test/index.view.php");
	}

	public function create() {
		$postRepo = new PostRepo();
		$posts = $postRepo->getAllPosts();
		include(__DIR__."/../../views/test/create.view.php");
	}

	public function store() {


		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'],'test-nonce') || !is_user_logged_in()) {
			wp_redirect(admin_url('admin.php?page=splittest-for-elementor&message=security_error'));
			return;
		}

		if (!isset($_POST['test-id'])) {
			wp_redirect(admin_url('admin.php?page=splittest-for-elementor&message=error_store_data_missing'));
			return;
		}

		if ($_POST['test-conversion-type'] == "page" && ($_POST['test-conversion-page'] == "" || $_POST['test-conversion-page'] == null || $_POST['test-conversion-page'] == "null")) {
			wp_redirect(admin_url('admin.php?page=splittest-for-elementor&message=error_conversion_page_missing'));
			return;
		}

		if ($_POST['test-conversion-type'] == "url" && ($_POST['test-conversion-url'] == "" || $_POST['test-conversion-url'] == null || $_POST['test-conversion-url'] == "null")) {
			wp_redirect(admin_url('admin.php?page=splittest-for-elementor&message=error_conversion_url_missing'));
			return;
		}

		if ($_POST['test-type'] == "pages") {
			if (strpos($_POST['test-uri'], '/') !== false) {
				wp_redirect(admin_url('admin.php?page=splittest-for-elementor&scope=test&action=create&message=error_test_page_invalid_chars'));
				return;
			}
		}

		$testRepo = new TestRepo();

		if ($_POST['test-type'] == "pages") {
			$id = $testRepo->createTest([
				'name' => $_POST['test-name'],
				'testType' => $_POST['test-type'],
				'testUri' => $_POST['test-uri'],
				'conversionType' => $_POST['test-conversion-type'],
				'conversionPageId' => $_POST['test-conversion-page'],
				'externalLink' => $_POST['test-external-link'] == "null" || $_POST['test-external-link'] == null ? null : $_POST['test-external-link'],
				'conversionUrl' => $_POST['test-conversion-url'] == "null" ? null : $_POST['test-conversion-url']
			]);
		} else {
			$id = $testRepo->createTest([
				'name' => $_POST['test-name'],
				'testType' => $_POST['test-type'],
				'conversionType' => $_POST['test-conversion-type'],
				'conversionPageId' => $_POST['test-conversion-page'],
				'externalLink' => $_POST['test-external-link'] == "null" || $_POST['test-external-link'] == null ? null : $_POST['test-external-link'],
				'conversionUrl' => $_POST['test-conversion-url'] == "null" ? null : $_POST['test-conversion-url']
			]);
		}

		foreach ($_POST['test-variation'] as $variation) {
			$variation['postId'] = $variation['post-id'];
			$testRepo->createTestVariation($id, $variation);
		}

		wp_redirect(admin_url('admin.php?page=splittest-for-elementor&scope=test&action=edit&id='.$id.'&message=store_success'));
	}

	public function show() {

	}

	public function edit() {

		$id = $_GET['id'];
		if (!filter_var($id, FILTER_VALIDATE_INT)) {
			return "Wrong Test Id";
		}

		$testRepo = new TestRepo();
		$test = $testRepo->getTest((int) $id);

		$postRepo = new PostRepo();
		$posts = $postRepo->getAllPosts();

		$testPostRepo = new PostTestRepo();
		$postsForTest = $testPostRepo->getPostsForTest($test->id);

		include(__DIR__."/../../views/test/edit.view.php");
	}

	public function update() {


		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'],'test-nonce') || !is_user_logged_in()) {
			wp_redirect(admin_url('admin.php?page=splittest-for-elementor&message=security_error'));
			return;
		}

		if (!isset($_POST['test-id'])) {
			wp_redirect(admin_url('admin.php?page=splittest-for-elementor&message=error_update_data_missing'));
			return;
		}

		if ($_POST['test-conversion-type'] == "page" && ($_POST['test-conversion-page'] == "" || $_POST['test-conversion-page'] == null || $_POST['test-conversion-page'] == "null")) {
			wp_redirect(admin_url('admin.php?page=splittest-for-elementor&message=error_conversion_page_missing'));
			return;
		}

		if ($_POST['test-conversion-type'] == "url" && ($_POST['test-conversion-url'] == "" || $_POST['test-conversion-url'] == null || $_POST['test-conversion-url'] == "null")) {
			wp_redirect(admin_url('admin.php?page=splittest-for-elementor&message=error_conversion_url_missing'));
			return;
		}

		if ($_POST['test-type'] == "pages") {
			if (strpos($_POST['test-uri'], '/') !== false) {
				wp_redirect(admin_url('admin.php?page=splittest-for-elementor&scope=test&action=edit&id='.$_POST['test-id'].'&message=error_test_page_invalid_chars'));
				return;
			}
		}

		$testRepo = new TestRepo();

		if ($_POST['test-type'] == "urls") {
			$testRepo->updateTest((int) $_POST['test-id'], array(
				'name' => $_POST['test-name'],
				'testType' => $_POST['test-type'],
				'testUri' => $_POST['test-uri'],
				'conversionType' => $_POST['test-conversion-type'],
				'externalLink' => $_POST['test-external-link'] == "null" || $_POST['test-external-link'] == null ? null : $_POST['test-external-link'],
				'conversionPageId' => $_POST['test-conversion-page'] == "null" ? null : (int) $_POST['test-conversion-page'],
				'conversionUrl' => $_POST['test-conversion-url'] == "null" ? null : $_POST['test-conversion-url']
			));
		} else if ($_POST['test-type'] == "pages") {
			$testRepo->updateTest((int) $_POST['test-id'], array(
				'name' => $_POST['test-name'],
				'testType' => $_POST['test-type'],
				'testUri' => $_POST['test-uri'],
				'conversionType' => $_POST['test-conversion-type'],
				'externalLink' => $_POST['test-external-link'] == "null" || $_POST['test-external-link'] == null ? null : $_POST['test-external-link'],
				'conversionPageId' => $_POST['test-conversion-page'] == "null" ? null : (int) $_POST['test-conversion-page'],
				'conversionUrl' => $_POST['test-conversion-url'] == "null" ? null : $_POST['test-conversion-url']
			));
		} else {
			$testRepo->updateTest((int) $_POST['test-id'], array(
				'name' => $_POST['test-name'],
				'testType' => $_POST['test-type'],
				'conversionType' => $_POST['test-conversion-type'],
				'externalLink' => $_POST['test-external-link'] == "null" || $_POST['test-external-link'] == null ? null : $_POST['test-external-link'],
				'conversionPageId' => $_POST['test-conversion-page'] == "null" ? null : (int) $_POST['test-conversion-page'],
				'conversionUrl' => $_POST['test-conversion-url'] == "null" ? null : $_POST['test-conversion-url']
			));
		}

		foreach ($_POST['test-variation'] as $variation) {
			$variation['postId'] = (int) $variation['post-id'];
			if (Util::nullOrEmpty($variation['id'])) {
				$testRepo->createTestVariation((int) $_POST['test-id'], $variation);
			} else {
				$testRepo->updateTestVariation((int) $variation['id'], $variation);
			}
		}

		if (isset($_POST['test-delete-variation'])) {
			foreach ($_POST['test-delete-variation'] as $id) {
				$testRepo->softDeleteTestVariation((int) $id);
			}
		}

		wp_redirect(admin_url('admin.php?page=splittest-for-elementor&scope=test&action=edit&id='.$_POST['test-id'].'&message=save_success'));
	}

	public function delete() {

		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'],'test-nonce') || !is_user_logged_in()) {
			wp_redirect(admin_url('admin.php?page=splittest-for-elementor&message=security_error'));
			return;
		}

		if (!isset($_GET['id'])) {
			wp_redirect(admin_url('admin.php?page=splittest-for-elementor&message=error_delete'));
		}

		$testRepo = new TestRepo();
		$testRepo->deleteTest((int) $_GET['id']);

		wp_redirect(admin_url('admin.php?page=splittest-for-elementor&scope=test&action=index&message=delete_success'));
	}

	public function resetStatistics()
	{
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'],'test-nonce') || !is_user_logged_in()) {
			wp_redirect(admin_url('admin.php?page=splittest-for-elementor&message=security_error'));
			return;
		}

		if (!isset($_GET['id'])) {
			wp_redirect(admin_url('admin.php?page=splittest-for-elementor&message=error_update_data_missing'));
			return;
		}

		if (!filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
			wp_redirect(admin_url('admin.php?page=splittest-for-elementor&message=error_update_data_missing'));
			return;
		}

		$testRepo = new TestRepo();
		$testRepo->resetTestStatistics($_GET['id']);

		wp_redirect(admin_url('admin.php?page=splittest-for-elementor&message=reset_success'));
	}

}