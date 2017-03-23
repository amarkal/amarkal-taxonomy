<?php

namespace Amarkal\Taxonomy;

/**
 * WordPress taxonomy form utilities
 */
class Form
{
    /**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;
    
    /**
     * @var Array Stores all the registered fields for each taxonomy
     */
    private $fields = array();
    
    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function get_instance()
    {
        if( null === static::$instance ) 
        {
            static::$instance = new static();
        }
        return static::$instance;
    }
    
    /**
     * Add a form field to both the add & edit forms for a given taxonomy.
     * 
     * @param string $taxonomy_name
     * @param string $field_name
     * @param array $field_props
     * @throws \RuntimeException if duplicate names are registered under the same taxonomy
     */
    public function add_field( $taxonomy_name, $field_name, $field_props )
    {
        if( !isset($this->fields[$taxonomy_name]) )
        {
            // Add fields to taxonomy add and edit forms 
            add_action( "{$taxonomy_name}_add_form_fields", array($this, 'render_add_form') );
            add_action( "{$taxonomy_name}_edit_form_fields", array($this, 'render_edit_form') );
            
            // Save the data from taxonomy add and edit forms
            add_action( "create_{$taxonomy_name}", array($this, 'update_term') );
            add_action( "edited_{$taxonomy_name}", array($this, 'update_term') );
            
            // Modify the taxonomy term table
            add_filter( "manage_edit-{$taxonomy_name}_columns", array($this, 'modify_table_columns') );
            add_filter( "manage_{$taxonomy_name}_custom_column", array($this, 'modify_table_content'), 10, 3 );
            add_filter( "manage_edit-{$taxonomy_name}_sortable_columns", array($this, 'modify_table_sortable_columns') );
            add_filter( 'terms_clauses', array($this, 'sort_custom_column'), 10, 3 );
            
            $this->fields[$taxonomy_name] = array();
        }

        if( !isset($this->fields[$taxonomy_name][$field_name]))
        {
            $this->fields[$taxonomy_name][$field_name] = array_merge( $this->default_props(), $field_props );
        }
        else throw new \RuntimeException("A field named '$field_name' has already been registered in '$taxonomy_name'");
    }
    
    /**
     * Render the 'edit term' form for a given taxonomy
     * 
     * @param object $term Taxonomy term
     */
    public function render_edit_form( $term )
    {
        $fields = $this->fields[$term->taxonomy];
        
        foreach( $fields as $name => $props )
        {
            $props['name'] = $name;
            $props['term_id'] = $term->term_id;
            $field = new EditField($props);
            echo $field->render();
        }
    }
    
    /**
     * Render the 'add new term' form for a given taxonomy
     * 
     * @param string $taxonomy Taxonomy name
     */
    public function render_add_form( $taxonomy )
    {
        $fields = $this->fields[$taxonomy];
        
        foreach( $fields as $name => $props )
        {
            $props['name'] = $name;
            $field = new AddField($props);
            echo $field->render();
        }
    }
    
    /**
     * Update the meta values for a given term. Called once one of the add/edit
     * forms is saved.
     * 
     * @param type $term_id
     */
    function update_term( $term_id ) 
    {
        $term = \get_term( $term_id );
        
        foreach( $this->fields[$term->taxonomy] as $name => $props )
        {
            if( isset($_POST[$name]) )
            {
                update_term_meta($term_id, $name, filter_input(INPUT_POST, $name));
            }
        }
    }
    
    /**
     * Add additional columns to the term table.
     * 
     * @param array $columns
     * @return array
     */
    function modify_table_columns( $columns )
    {   
        $this->traverse_fields(function( $taxonomy, $name, $props ) use ( &$columns ) 
        {
            if( $props['table']['show'] )
            {
                $columns[$name] = $props['label'];
            }
        });
        return $columns;
    }
    
    /**
     * Retrieve the data for a given column in the term table.
     * 
     * @see https://developer.wordpress.org/reference/hooks/manage_this-screen-taxonomy_custom_column/
     * 
     * @param type $content
     * @param type $column_name
     * @param type $term_id
     * @return type
     */
    function modify_table_content( $content, $column_name, $term_id )
    {   
        $term = \get_term($term_id);
        $this->traverse_fields(function( $taxonomy, $name, $props ) use ( &$content, $column_name, $term ) 
        {
            if( $props['table']['show'] && 
                $term->taxonomy === $taxonomy &&
                $name === $column_name
            ) {
                $content = \get_term_meta($term->term_id, $name, true);
            }
        });
        return $content;
    }
    
    /**
     * Make custom table columns sortable.
     * 
     * @param array $columns
     * @return string
     */
    function modify_table_sortable_columns( $columns )
    {
        $this->traverse_fields(function( $taxonomy, $name, $props ) use ( &$columns ) 
        {
            if( $props['table']['show'] && 
                $props['table']['sortable']
            ) {
                $columns[$name] = $name;
            }
        });
        return $columns;
    }
    
    /**
     * Modify terms_clauses to allow sorting custom WordPress Admin Table Columns by a custom Taxonomy Term meta
     * 
     * @see https://developer.wordpress.org/reference/hooks/terms_clauses/
     * 
     * @global type $wpdb
     * @param type $clauses
     * @param type $taxonomies
     * @param type $args
     * @return string
     */
    function sort_custom_column( $clauses, $taxonomies, $args )
    {
        $this->traverse_fields(function( $taxonomy, $name, $props ) use ( &$clauses, $args ) 
        {
            if( in_array($taxonomy, $args['taxonomy']) && 
                $props['table']['show'] && 
                $props['table']['sortable'] &&
                $name === $args['orderby']
            )
            {
                global $wpdb;
                // tt refers to the $wpdb->term_taxonomy table
                $clauses['join'] .= " LEFT JOIN {$wpdb->termmeta} AS tm ON t.term_id = tm.term_id";
                $clauses['where'] = "tt.taxonomy = '{$taxonomy}' AND (tm.meta_key = '{$name}' OR tm.meta_key IS NULL)";
                $clauses['orderby'] = "ORDER BY tm.meta_value";
            }
        });
        return $clauses;
    }
    
    /**
     * The default form field properties. This is merged with the user given 
     * properties. When the component is rendered, this will be merged with the
     * component's properties as well.
     * 
     * @return array
     */
    private function default_props()
    {
        return array(
            'type'          => null,
            'label'         => null,
            'description'   => null,
            'table'         => array(
                'show'      => false,
                'sortable'  => false
            )
        );
    }
    
    /**
     * Treverse the $fields array.
     * 
     * @param collable $callback Called on each iteration
     */
    private function traverse_fields( $callback )
    {
        foreach( $this->fields as $taxonomy => $fields )
        {
            foreach( $fields as $name => $props )
            {
                $callback( $taxonomy, $name, $props );
            }
        }
    }
}