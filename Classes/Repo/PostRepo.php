<?php

namespace SplitTestForElementor\Classes\Repo;

use WP_Query;

class PostRepo {

	public function getAllPosts() {
		$posts = [];
		$query = array(
			'posts_per_page' => - 1,
			'post_type'      => apply_filters('split_test_for_elementor_test_post_types', ['page', 'landingpage', 'e-landing-page']),
			'post_status'    => array( 'publish' ),
			'orderby'        => 'title',
			'order'          => 'ASC'
		);

		$loop  = new WP_Query( $query );

		while ( $loop->have_posts() ) {
			$posts[] = $loop->next_post();
		}

		return $posts;
	}

}