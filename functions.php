<?php
/**
 * WordPress Taxonomy
 *
 * A set of utility functions for taxonomies in WordPress.
 * This is a component within the Amarkal framework.
 *
 * @package   amarkal-taxonomy
 * @depends   amarkal-ui
 * @author    Askupa Software <hello@askupasoftware.com>
 * @link      https://github.com/askupasoftware/amarkal-taxonomy
 * @copyright 2017 Askupa Software
 */

// Prevent direct file access
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Prevent loading the library more than once
 */
if( defined( 'AMARKAL_TAXONOMY' ) ) return false;
define( 'AMARKAL_TAXONOMY', true );

if(!function_exists('amarkal_taxonomy_add_field'))
{
    /**
     * Add a field to the add & edit forms of a given taxonomy.
     * 
     * @param string $taxonomy_name The taxonomy name, e.g. 'category'
     * @param string $field_name The name of the field to add. One of the core 
     * amarkal-ui components or a registered custom component.
     * @param array $field_props The field's properties
     */
    function amarkal_taxonomy_add_field( $taxonomy_name, $field_name, $field_props )
    {
        $form = Amarkal\Taxonomy\Form::get_instance();
        $form->add_field( $taxonomy_name, $field_name, $field_props );
    }
}