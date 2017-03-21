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
            
            $this->fields[$taxonomy_name] = array();
        }

        if( !isset($this->fields[$taxonomy_name][$field_name]))
        {
            $this->fields[$taxonomy_name][$field_name] = $field_props;
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
}