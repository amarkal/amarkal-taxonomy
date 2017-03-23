<?php

namespace Amarkal\Taxonomy;

class AddField
extends \Amarkal\UI\AbstractComponent
{
    public function default_model() 
    {
        return array(
            'type'          => '',
            'label'         => '',
            'description'   => ''
        );
    }
    
    public function get_template_path() 
    {
        return __DIR__.'/AddField.phtml';
    }
}