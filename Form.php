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
     * @var Array Stores a form for each taxonomy
     */
    private $forms = array();
    
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
     * @param array $component
     * @throws \RuntimeException if duplicate names are registered under the same taxonomy
     */
    public function add_field( $taxonomy_name, $component )
    {
        if( !isset($this->forms[$taxonomy_name]) )
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
            
            $this->forms[$taxonomy_name] = new \Amarkal\UI\Form();
        }
        
        $this->forms[$taxonomy_name]->add_component(
            array_merge( $this->default_props(), $component )
        );
    }
    
    /**
     * Render the 'edit term' form for a given taxonomy
     * 
     * @param object $term Taxonomy term
     */
    public function render_edit_form( $term )
    {
        $form = $this->forms[$term->taxonomy];
        $new_instance = array();
        
        foreach( $form->get_components() as $component )
        {
            $new_instance[$component->name] = \get_term_meta($term->term_id, $component->name, true);
        }
        
        $form->update($new_instance);
        
        include __DIR__.'/EditForm.phtml';
    }
    
    /**
     * Render the 'add new term' form for a given taxonomy
     * 
     * @param string $taxonomy Taxonomy name
     */
    public function render_add_form( $taxonomy )
    {
        $form = $this->forms[$taxonomy];
        $form->update();
        
        include __DIR__.'/AddForm.phtml';
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
        
        foreach( $this->forms[$term->taxonomy]->get_components() as $component )
        {
            $term_meta = filter_input(INPUT_POST, $component->name);
            if( null !== $term_meta )
            {
                \update_term_meta($term_id, $component->name, $term_meta);
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
        $this->traverse_components(function( $taxonomy, $component ) use ( &$columns ) 
        {
            if( $component->table['show'] )
            {
                $columns[$component->name] = $component->title;
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
        $this->traverse_components(function( $taxonomy, $component ) use ( &$content, $column_name, $term ) 
        {
            if( $component->table['show'] && 
                $term->taxonomy === $taxonomy &&
                $component->name === $column_name
            ) {
                $content = \get_term_meta($term->term_id, $component->name, true);
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
        $this->traverse_components(function( $taxonomy, $component ) use ( &$columns ) 
        {
            if( $component->table['show'] && 
                $component->table['sortable']
            ) {
                $columns[$component->name] = $component->name;
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
    public function sort_custom_column( $clauses, $taxonomies, $args )
    {
        $this->traverse_components(function( $taxonomy, $component ) use ( &$clauses, $args ) 
        {
            if( in_array($taxonomy, $args['taxonomy']) && 
                $component->table['sortable'] &&
                $component->name === $args['orderby']
            )
            {
                global $wpdb;
                // tt refers to the $wpdb->term_taxonomy table
                $clauses['join'] .= " LEFT JOIN {$wpdb->termmeta} AS tm ON t.term_id = tm.term_id";
                $clauses['where'] = "tt.taxonomy = '{$taxonomy}' AND (tm.meta_key = '{$component->name}' OR tm.meta_key IS NULL)";
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
            'title'         => null,
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
    private function traverse_components( $callback )
    {
        foreach( $this->forms as $taxonomy => $form )
        {
            foreach( $form->get_components() as $component )
            {
                $callback( $taxonomy, $component );
            }
        }
    }
}