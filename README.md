<p align="center">
    <img src="https://raw.githubusercontent.com/akki-io/laravel-nova-search/master/hero.png" alt="Hero" width="600">
</p>

# Laravel Nova Search

[![Latest Version](https://img.shields.io/github/release/akki-io/laravel-nova-search.svg?style=flat-square)](https://github.com/akki-io/laravel-nova-search/releases)
[![Build Status](https://img.shields.io/travis/akki-io/laravel-nova-search/master.svg?style=flat-square)](https://travis-ci.org/akki-io/laravel-nova-search)
[![Quality Score](https://img.shields.io/scrutinizer/g/akki-io/laravel-nova-search.svg?style=flat-square)](https://scrutinizer-ci.com/g/akki-io/laravel-nova-search)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![StyleCI](https://styleci.io/repos/291209513/shield?branch=master)](https://styleci.io/repos/291209513)
[![Total Downloads](https://img.shields.io/packagist/dt/akki-io/laravel-nova-search.svg?style=flat-square)](https://packagist.org/packages/akki-io/laravel-nova-search)

This package provides a trait that extends the search behaviour of laravel nova resource.

## Installation

You can install the package via composer:

```bash
composer require akki-io/laravel-nova-search
```

Next, add `AkkiIo\LaravelNovaSearch\LaravelNovaSearchable` trait to your base resource `App\Nova\Resource` class.

```php
use AkkiIo\LaravelNovaSearch\LaravelNovaSearchable;

abstract class Resource extends NovaResource
{
    use LaravelNovaSearchable;
    
    // ...
}
``` 

## Usage

This package DOES NOT have fuzzy matching capabilities. If you are looking for robust fuzzy matching capabilities provided by "real" search engines, you should look into [Laravel Scout](https://laravel.com/docs/scout).

This package adds the following types of search to your laravel nova resource.
- Search multiple columns using concatenation.
- Search every word in columns.
- Search relationship columns.

### Search multiple columns using concatenation.

To define which resource fields are searchable, you may assign a two-dimensional array of database columns in the `public static $searchConcatenation` property of your resource class. 
Each array in the array are names of columns that are concatenated using whitespace.   

``` php
/**
 * The columns that should be concatenated and searched.
 *
 * @var array
 */
 public static $searchConcatenation = [
    ['first_name', 'last_name'],
    ['first_name', 'company'],
 ];
```

### Search every word in columns.

To define which resource fields are searchable, you may assign an array of database columns in the `public static $searchMatchingAny` property of your resource class. 
Every word in your input is searched for across all these columns. 

```php
/**
 * The columns that should be searched for any matching entry.
 *
 * @var array
 */
 public static $searchMatchingAny = [
    'first_name',
    'last_name',
    'email',
 ];
```

### Search relationship columns.

To define which resource fields are searchable, you may assign an array of database columns in the `public static $searchRelations` property of your resource class. 
These database columns are from the related table that is used for searching. 
This array has a relationship name as a key, and an array of columns to search for as a value.

```php
/**
 * The relationship columns that should be searched.
 *
 * @var array
 */
 public static $searchRelations = [
     'posts' => ['title', 'sub_title'],
 ];
```

#### Nested relationships

You may search nested relationships using dot notation.

```php
/**
 * The relationship columns that should be searched.
 *
 * @var array
 */
public static $searchRelations = [
    'user.location' => ['state_abbr', 'country_abbr],
];
```

#### Search multiple columns in relationship using concatenation.

To define which resource fields are searchable, you may assign a two-dimensional array of database columns in the `public static $searchRelationsConcatenation` property of your resource class.
Each array in the array are names of columns that are concatenated using whitespace.

``` php

/**
 * The relationship columns that should to be concatenated and searched.
 *
 * @var array
 */
 public static $searchRelationsConcatenation = [
    'user' => [
        ['first_name', 'last_name'],
        ['email']
    ],
 ];
```

#### Search every word in columns of a relationship.

To define which resource fields are searchable, you may assign an array of database columns in the `public static $searchRelationsMatchingAny` property of your resource class.
Every word in your input is searched for across all these columns.

```php
/**
 * The relationship columns that should be searched for any matching entry.
 *
 * @var array
 */
 public static $searchRelationsMatchingAny = [
    'user' => ['first_name', 'last_name'],
 ];
```

### Testing

``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email hello@akki.io instead of using the issue tracker.

## Credits

- [Akki Khare](https://github.com/akki-io)
- [Nova Search Relationship Package](https://github.com/TitasGailius/nova-search-relations)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
