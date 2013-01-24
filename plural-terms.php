<?php
/*
Plugin Name: Plural Terms
Plugin URI: 
Description: Provides API to add plural field to your taxonomy terms.
Version: 0.0.1
Author: Seamus Leahy
Author URI: http://seamusleahy.com
License: MIT
*/


class Plural_Terms {

	var $taxonomies = array();
	var $registered = array();

	function __construct() {
		global $pagenow;

		if( is_admin() && in_array( $pagenow, array( 'edit-tags.php' ) ) ) {
			add_action( 'created_term', array( $this, 'update_term'), 10, 3 );
			add_action( 'edited_term', array( $this, 'update_term'), 10, 3 );
		}
	}


	/**
	 * Add a taxonomy to add the plural field to
	 */
	function add_taxonomies( $taxonomies ) {
		$taxonomies = (array) $taxonomies;

		$this->taxonomies = array_unique( array_merge( $this->taxonomies, $taxonomies ) );

		$initialize = array_diff( $this->taxonomies, $this->registered );
		foreach( $initialize as $tax ) {
			add_action( $tax.'_pre_edit_form', array( $this, 'pre_edit_form'), 10, 2 );
		}
		$this->registered = $this->taxonomies;
	}


	/**
	 * Called before the term field, use it to register the form render callback
	 */
	function pre_edit_form( $tag, $taxonomy ) {
		add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_form'), 10, 2 );
	}


	/**
	 * Render the plural field
	 */
	function edit_form( $tag, $taxonomy ) {
    $plural_name = $this->get_plural_name( $tag->term_id );
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="plural_term_name"><?php _e('Plural Name'); ?></label></th>
			<td>
				<input type="text" name="plural_term_name" id="plural_term_name" value="<?php echo $plural_name ? $plural_name : ''; ?>"><br />
		    <span class="description"><?php _e('The name of the term in plural form.'); ?></span>
			</td>
		</tr>
		<?php
	}


	/**
	 * Generate the option key from the term ID
	 */
	function option_key( $term_id ) {
		return "plural_term_name_{$term_id}";
	}


	/**
	 * Hook callback for saving the term
	 */
	function update_term( $term_id, $tt_id, $taxonomy ) {
		$val = empty( $_POST['plural_term_name'] ) ? '' : $_POST['plural_term_name'];
		update_option( $this->option_key( $term_id ), $val );
	}


	/**
	 * Get the plural name for a term if any
	 */
	function get_plural_name( $term ) {
		if( is_numeric($term) ) {
			$term_id = $term;
		} else {
			$term_id = $term->term_id;
		}

		return get_option( $this->option_key( $term_id ) );
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
 * @param $taxonomies (string|array) - the names of the taxonomies
 */
function plural_terms_add_taxonomies( $taxonomies ) {
	global $plural_terms;
	$plural_terms->add_taxonomies( $taxonomies );
}


/**
 * Get the plural name for a term
 *
 * @param $term (int|object) - either the term ID or term object
 * @return string
 */
function plural_terms_get_plural_name( $term ) {
	global $plural_terms;
	return $plural_terms->get_plural_name( $term );
}
