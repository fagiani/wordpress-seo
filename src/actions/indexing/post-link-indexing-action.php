<?php

namespace Yoast\WP\SEO\Actions\Indexing;

use Yoast\WP\Lib\Model;
use Yoast\WP\SEO\Helpers\Post_Type_Helper;

/**
 * Reindexing action for post link indexables.
 */
class Post_Link_Indexing_Action extends Abstract_Link_Indexing_Action {

	/**
	 * The transient name.
	 *
	 * @var string
	 */
	const UNINDEXED_COUNT_TRANSIENT = 'wpseo_unindexed_post_link_count';

	/**
	 * The post type helper.
	 *
	 * @var Post_Type_Helper
	 */
	protected $post_type_helper;

	/**
	 * Sets the required helper.
	 *
	 * @required
	 *
	 * @param Post_Type_Helper $post_type_helper The post type helper.
	 *
	 * @return void
	 */
	public function set_helper( Post_Type_Helper $post_type_helper ) {
		$this->post_type_helper = $post_type_helper;
	}

	/**
	 * Returns objects to be indexed.
	 *
	 * @return array Objects to be indexed.
	 */
	protected function get_objects() {
		$query = $this->get_select_query( $this->get_limit() );

		$posts = $this->wpdb->get_results( $query );

		return \array_map(
			static function ( $post ) {
				return (object) [
					'id'      => (int) $post->ID,
					'type'    => 'post',
					'content' => $post->post_content,
				];
			},
			$posts
		);
	}

	/**
	 * Builds a query for counting the number of unindexed post links.
	 *
	 * @param bool $limit The maximum amount of unindexed post links that should be counted.
	 *
	 * @return string The prepared query string.
	 */
	protected function get_count_query( $limit = false ) {
		// Limited queries are use to determine whether background indexing should occur, the exact number is irrelevant.
		if ( $limit !== false ) {
			return $this->get_limited_unindexed_count( $limit );
		}

		$public_post_types = $this->post_type_helper->get_accessible_post_types();
		$post_types        = \implode( ', ', \array_fill( 0, \count( $public_post_types ), '%s' ) );
		$indexable_table   = Model::get_table_name( 'Indexable' );
		$links_table       = Model::get_table_name( 'SEO_Links' );
		$replacements      = $public_post_types;

		$query_columns = 'COUNT(P.ID)';
		$limit_query = '';
		if ( $limit ) {
			$limit_query    = 'LIMIT %d';
			$replacements[] = $limit;
			$query_columns    = 'P.ID';
		}

		// Warning: If this query is changed, makes sure to update the query in get_select_query as well.
		return $this->wpdb->prepare(
			"SELECT $query_columns
			FROM {$this->wpdb->posts} AS P
			LEFT JOIN $indexable_table AS I
				ON P.ID = I.object_id
				AND I.link_count IS NOT NULL
				AND I.object_type = 'post'
			LEFT JOIN $links_table AS L
				ON L.post_id = P.ID
				AND L.target_indexable_id IS NULL
				AND L.type = 'internal'
				AND L.target_post_id IS NOT NULL
				AND L.target_post_id != 0
			WHERE ( I.object_id IS NULL OR L.post_id IS NOT NULL )
				AND P.post_status = 'publish'
				AND P.post_type IN ($post_types)
			ORDER BY P.ID
			$limit_query
			",
			$replacements
		);
	}

	/**
	 * Builds a query for selecting the ID's of unindexed post links.
	 *
	 * @param bool $limit The maximum number of post link IDs to return.
	 *
	 * @return string The prepared query string.
	 */
	protected function get_select_query( $limit = false ) {
		$public_post_types = $this->post_type_helper->get_accessible_post_types();
		$post_types        = \implode( ', ', \array_fill( 0, \count( $public_post_types ), '%s' ) );
		$indexable_table   = Model::get_table_name( 'Indexable' );
		$links_table       = Model::get_table_name( 'SEO_Links' );
		$replacements      = $public_post_types;

		$limit_query = '';
		if ( $limit ) {
			$limit_query    = 'LIMIT %d';
			$replacements[] = $limit;
		}

		// Warning: If this query is changed, makes sure to update the query in get_count_query as well.
		return $this->wpdb->prepare(
			"SELECT P.ID, P.post_content
			FROM {$this->wpdb->posts} AS P
			LEFT JOIN $indexable_table AS I
				ON P.ID = I.object_id
				AND I.link_count IS NOT NULL
				AND I.object_type = 'post'
			LEFT JOIN $links_table AS L
				ON L.post_id = P.ID
				AND L.target_indexable_id IS NULL
				AND L.type = 'internal'
				AND L.target_post_id IS NOT NULL
				AND L.target_post_id != 0
			WHERE ( I.object_id IS NULL OR L.post_id IS NOT NULL )
				AND P.post_status = 'publish'
				AND P.post_type IN ($post_types)
			$limit_query
			",
			$replacements
		);
	}
}
