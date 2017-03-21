# amarkal-taxonomy [![Build Status](https://scrutinizer-ci.com/g/askupasoftware/amarkal-taxonomy/badges/build.png?b=master)](https://scrutinizer-ci.com/g/askupasoftware/amarkal-taxonomy/build-status/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/askupasoftware/amarkal-taxonomy/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/askupasoftware/amarkal-taxonomy/?branch=master) [![License](https://img.shields.io/badge/license-GPL--3.0%2B-red.svg)](https://raw.githubusercontent.com/askupasoftware/amarkal-taxonomy/master/LICENSE)
A set of utility functions for taxonomies in WordPress.

**Tested up to:** WordPress 4.7  
**Dependencies**: *[amarkal-ui](https://github.com/askupasoftware/amarkal-ui)*

![amarkal-taxonomy](https://askupasoftware.com/wp-content/uploads/2015/04/amarkal-taxonomy.png)

## Overview
Using **amarkal-taxonomy** you can easily modify the taxonomy forms in the WordPress admin area and add custom fields to them. More utility functions will be added in the future.

## Installation

### Via Composer

If you are using the command line:  
```
$ composer require askupa-software/amarkal-ui:dev-master
$ composer require askupa-software/amarkal-taxonomy:dev-master
```

Or simply add the following to your `composer.json` file:
```javascript
"require": {
    "askupa-software/amarkal-ui": "dev-master"
    "askupa-software/amarkal-taxonomy": "dev-master"
}
```
And run the command 
```
$ composer install
```

This will install the package in the directory `vendors/askupa-software/amarkal-taxonomy`.

### Manually

Download [amarkal-ui](https://github.com/askupasoftware/amarkal-ui/archive/master.zip) and [amarkal-taxonomy](https://github.com/askupasoftware/amarkal-taxonomy/archive/master.zip) from github and include them in your project.

## Usage

Before you can use **amarkal-taxonomy** in your project, you will need to bootstrap it:

```php
require_once 'path/to/amarkal-ui/bootstrap.php';
require_once 'path/to/amarkal-taxonomy/bootstrap.php';
```

Now that **amarkal-taxonomy** is bootstrapped, the following functions become available for you to use:

### amarkal_taxonomy_add_field
*Add a field to the add & edit forms of a given taxonomy.*
```php
amarkal_taxonomy_add_field( $taxonomy_name, $field_type, $field_props )
```
This function can be used to add fields to the 'add term' and 'edit term' taxonomy forms. See [amarkal-ui](https://github.com/askupasoftware/amarkal-ui/) for supported field types, or register your own field type using `amarkal_ui_register_component`. `$field_props` can accept the following properties in addition to the field's original properties: `label` and `description`.

**Parameters**  
* `$taxonomy_name` (*String*)   The taxonomy name, e.g. 'category'.
* `$field_type` (*String*)  The type of the field to add. One of the core `amarkal-ui` components or a registered custom component.
* `$field_props` (*Array*)  The component's properties.

**Example Usage**
```php
// Add a text field to the category 'add' & 'edit' forms:
amarkal_taxonomy_add_field('text', 'category', array(
    'type'        => 'text',
    'label'       => 'Icon',
    'description' => 'The category\'s icon'
));
```
