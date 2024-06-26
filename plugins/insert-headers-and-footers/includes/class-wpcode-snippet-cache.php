<?php
/**
 * Cache active snippets in a single query.
 *
 * @package WPCode
 */

/**
 * Class WPCode_Snippet_Cache.
 */
class WPCode_Snippet_Cache {

	/**
	 * The option name used for storing data in the db.
	 *
	 * @var string
	 */
	protected $option_name = 'wpcode_snippets';

	/**
	 * The snippets stored in the db.
	 *
	 * @var array
	 */
	protected $snippets;

	/**
	 * Get the snippets data from the cache.
	 *
	 * @return array
	 */
	public function get_cached_snippets() {
		if ( ! isset( $this->snippets ) ) {
			$all_snippets = $this->get_option();
			foreach ( $all_snippets as $location => $snippets ) {
				if ( empty( $snippets ) ) {
					continue;
				}
				if ( ! is_array( $all_snippets[ $location ] ) ) {
					$all_snippets[ $location ] = array();
				}
				// Load minimal snippet data from array.
				foreach ( $snippets as $key => $snippet ) {
					$all_snippets[ $location ][ $key ] = $this->load_snippet( $snippet );
				}

				usort( $all_snippets[ $location ], array( $this, 'priority_order' ) );
			}

			$this->snippets = $all_snippets;
		}

		return $this->snippets;
	}

	/**
	 * Load a snippet by id, WP_Post or array.
	 *
	 * @param array|int|WP_Post $snippet_data Load a snippet by id, WP_Post or array.
	 *
	 * @return WPCode_Snippet
	 */
	public function load_snippet( $snippet_data ) {
		return new WPCode_Snippet( $snippet_data );
	}

	/**
	 * Get cached snippets in an array by their id.
	 *
	 * @return WPCode_Snippet[]
	 */
	public function get_cached_snippets_by_id() {
		$snippets_by_id  = array();
		$cached_snippets = $this->get_cached_snippets();
		foreach ( $cached_snippets as $snippets ) {
			foreach ( $snippets as $snippet ) {
				$snippets_by_id[ $snippet->get_id() ] = $snippet;
			}
		}

		return $snippets_by_id;
	}

	/**
	 * Used for sorting by priority.
	 *
	 * @param WPCode_Snippet $snippet_a The first snippet.
	 * @param WPCode_Snippet $snippet_b The second snippet.
	 *
	 * @return int
	 */
	public function priority_order( $snippet_a, $snippet_b ) {
		return $snippet_a->get_priority() - $snippet_b->get_priority();
	}

	/**
	 * Delete the cache option completely.
	 *
	 * @return void
	 */
	public function delete_cache() {
		update_option( $this->option_name, array() );
	}

	/**
	 * Save all the loaded snippets in a single option.
	 *
	 * @return void
	 */
	public function cache_all_loaded_snippets() {
		if ( ! apply_filters( 'wpcode_cache_active_snippets', true ) ) {
			return;
		}
		$auto_inserts         = wpcode()->auto_insert->get_types();
		$snippets_by_location = array();
		foreach ( $auto_inserts as $auto_insert ) {
			// We don't want to use cached data when gathering stuff for cache.
			add_filter( 'wpcode_use_auto_insert_cache', '__return_false' );
			// Make sure snippets were not already loaded by earlier hooks.
			unset( $auto_insert->snippets );
			$snippets_by_location = array_merge( $auto_insert->get_snippets(), $snippets_by_location );
		}

		$data_for_cache = array();
		foreach ( $snippets_by_location as $location => $snippets ) {
			if ( empty( $snippets ) ) {
				continue;
			}
			$data_for_cache[ $location ] = $this->prepare_snippets_for_caching( $snippets );
		}

		$this->update_option( $data_for_cache );
	}

	/**
	 * Update the option with the new data.
	 *
	 * @param array $data_for_cache The data to store in the option.
	 *
	 * @return bool
	 */
	public function update_option( $data_for_cache ) {
		return update_option( $this->option_name, $data_for_cache );
	}

	/**
	 * Get the option from the db.
	 *
	 * @return array
	 */
	public function get_option() {
		return (array) get_option( $this->option_name, array() );
	}

	/**
	 * Go through an array of snippets and extract just the minimal data
	 * needed for running the snippets.
	 *
	 * @param WPCode_Snippet[] $snippets The snippets array.
	 *
	 * @return array
	 */
	private function prepare_snippets_for_caching( $snippets ) {
		$prepared_snippets = array();
		foreach ( $snippets as $snippet ) {
			$prepared_snippets[] = $snippet->get_data_for_caching();
		}

		return $prepared_snippets;
	}
}
