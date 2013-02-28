<?php
/*
Plugin Name: Plural Terms
Plugin URI:
Description: Provides API to add plural field to your taxonomy terms.
Version: 0.0.2
Author: Seamus Leahy
Author URI: http://seamusleahy.com
License: MIT
*/

// Depends upon wp-large-options when running on VIP
if ( function_exists( 'wpcom_vip_load_plugin' ) ) {
	wpcom_vip_load_plugin( 'wp-large-options' );
}

class Plural_Terms {

	var $taxonomies = array();
	var $registered = array();

	const KEY = 'plural_terms';

	function __construct() {
		global $pagenow;

		if ( is_admin() && in_array( $pagenow, array( 'edit-tags.php' ) ) ) {
			add_action( 'created_term', array( $this, 'update_term' ), 10, 3 );
			add_action( 'edited_term', array( $this, 'update_term' ), 10, 3 );
		}
	}


	/**
	 * Add a taxonomy to add the plural field to
	 */
	function add_taxonomies( $taxonomies ) {
		$taxonomies = (array) $taxonomies;

		$this->taxonomies = array_unique( array_merge( $this->taxonomies, $taxonomies ) );

		$initialize = array_diff( $this->taxonomies, $this->registered );
		foreach ( $initialize as $tax ) {
			add_action( $tax.'_pre_edit_form', array( $this, 'pre_edit_form' ), 10, 2 );
		}
		$this->registered = $this->taxonomies;
	}


	/**
	 * Called before the term field, use it to register the form render callback
	 */
	function pre_edit_form( $tag, $taxonomy ) {
		add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_form' ), 10, 2 );
	}


	/**
	 * Render the plural field
	 */
	function edit_form( $tag, $taxonomy ) {
		$plural_name = $this->get_plural_name( $tag->term_id );
?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="plural_term_name"><?php echo 'Plural Name' ; ?></label></th>
			<td>
				<input type="text" name="plural_term_name" id="plural_term_name" value="<?php echo $plural_name ? $plural_name : ''; ?>"><br />
				<span class="description"><?php echo 'The name of the term in plural form.'; ?></span>
			</td>
		</tr>
		<?php
	}



	/**
	 * Hook callback for saving the term
	 */
	function update_term( $term_id, $tt_id, $taxonomy ) {
		$data = $this->get_data();
		$data[$term_id] = filter_input( INPUT_POST, 'plural_term_name', FILTER_SANITIZE_STRING );


		if ( function_exists( 'wlo_update_option' ) ) {
			// update does not properly add the post if it doesn't already exists
			if ( !wlo_add_option( self::KEY, $data ) ) {
				wlo_update_option( self::KEY, $data );
			}
		} else {
			update_option( self::KEY, $data );
		}
	}


	/**
	 * Get the plural name for a term if any
	 */
	function get_plural_name( $term_id ) {
		$data = $this->get_data();
		$term_id = $this->get_term_id( $term_id );
		return array_key_exists( $term_id, $data ) ? $data[$term_id] : '';
	}


	/**
	 * Get the stored data
	 */
	protected function get_data() {
		if ( empty( $this->data ) ) {
			if ( function_exists( 'wlo_get_option' ) ) {
				$this->data = wlo_get_option( self::KEY, array() );
			} else {
				$this->data = get_option( self::KEY, array() );
			}
		}

		return $this->data;
	}


	/**
	 * Get the term ID where $term is either a term object or an ID
	 */
	protected function get_term_id( $term ) {
		if ( is_object( $term ) ) {
			return (int) $term->term_id;
		} else {
			return (int) $term;
		}
	}
}

global $plural_terms;
$plural_terms = new Plural_Terms();




//
// Global API
//

/**
 * Add a taxonomy to add a plural field to the taxonomy terms
 *
 * @param unknown $taxonomies (string|array) - the names of the taxonomies
 */
function plural_terms_add_taxonomies( $taxonomies ) {
	global $plural_terms;
	$plural_terms->add_taxonomies( $taxonomies );
}


/**
 * Get the plural name for a term
 *
 * @param unknown $term (int|object) - either the term ID or term object
 * @return string
 */
function plural_terms_get_plural_name( $term ) {
	global $plural_terms;
	return $plural_terms->get_plural_name( $term );
}
