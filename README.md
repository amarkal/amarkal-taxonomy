# amarkal-taxonomy [![Build Status](https://scrutinizer-ci.com/g/askupasoftware/amarkal-taxonomy/badges/build.png?b=master)](https://scrutinizer-ci.com/g/askupasoftware/amarkal-taxonomy/build-status/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/askupasoftware/amarkal-taxonomy/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/askupasoftware/amarkal-taxonomy/?branch=master) [![License](https://img.shields.io/badge/license-GPL--3.0%2B-red.svg)](https://raw.githubusercontent.com/askupasoftware/amarkal-taxonomy/master/LICENSE)
A set of utility functions for taxonomies in WordPress.

**Tested up to:** WordPress 4.7  
**Dependencies**: *[amarkal-ui](https://github.com/askupasoftware/amarkal-ui)*

![amarkal-taxonomy](https://askupasoftware.com/wp-content/uploads/2015/04/amarkal-taxonomy.png)

## Overview
**amarkal-taxonomy** lets you add custom fields to taxonomies in WordPress, and optionally add columns to the taxonomy terms table. More utility functions will be added in the future.

## Installation

### Via Composer

If you are using the command line:  
```
$ composer require askupa-software/amarkal-taxonomy:dev-master
```

Or simply add the following to your `composer.json` file:
```javascript
"require": {
    "askupa-software/amarkal-taxonomy": "dev-master"
}
```
And run the command 
```
$ composer install
```

This will install the package in the directory `vendors/askupa-software/amarkal-taxonomy`.
Now all you need to do is include the composer autoloader.

```php
require_once 'path/to/vendor/autoload.php';
```

### Manually

Download [amarkal-ui](https://github.com/askupasoftware/amarkal-ui/archive/master.zip) and [amarkal-taxonomy](https://github.com/askupasoftware/amarkal-taxonomy/archive/master.zip) from github and include them in your project.

```php
require_once 'path/to/amarkal-ui/bootstrap.php';
require_once 'path/to/amarkal-taxonomy/bootstrap.php';
```

## Reference

### amarkal_taxonomy_add_field
*Add a field to the add & edit forms of a given taxonomy.*
```php
amarkal_taxonomy_add_field( $taxonomy_name, $field_type, $field_props )
```
This function can be used to add fields to the 'add term' and 'edit term' taxonomy forms. See [amarkal-ui](https://github.com/askupasoftware/amarkal-ui/) for supported field types, or register your own field type using `amarkal_ui_register_component`.

**Parameters**  
* `$taxonomy_name` (*String*) Specifies the taxonomy name, e.g. 'category'.
* `$field_name` (*String*)  Specifies the name/id of the field.
* `$field_props` (*Array*)  Specifies the UI component's properties. This array should have the original UI component properties as specified in [amarkal-ui](https://github.com/askupasoftware/amarkal-ui), as well as the following:
  * `type` (*String*) Specifies the type of the field to add. One of the core `amarkal-ui` components or a registered custom component.
  * `label` (*String*) Specifies the form label for this field.
  * `description` (*String*) Specifies a short description that will be printed below the field.
  * `table` (*Array*) An associative array with the following arguments:
    * `show` (*Boolean*) Specifies whether to add a column for this field in the taxonomy terms table.
    * `sortable` (*Boolean*) Specifies whether to make the column for this field sortable.

**Example Usage**
```php
// Add a text field to the 'category' taxonomy 'add' & 'edit' forms:
amarkal_taxonomy_add_field('category', 'cat_icon', array(
    'type'        => 'text',
    'label'       => 'Icon',
    'description' => 'The category\'s icon',
    'table'       => array(
        'show'      => true,  // Add a column to the terms table
        'sortable'  => true   // Make that column sortable
    )
));

// Then you can retrieve the data using:
$icon = get_term_meta( $term_id, 'cat_icon', true );
```
