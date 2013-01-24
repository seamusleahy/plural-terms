Plural Terms
============

A WordPress plugin that adds a plural name field to terms

You need to register the taxonomies to have the field:

```php
plural_terms_add_taxonomies( array( 'my-tax' ) );
```

To get the value:

```php
echo plural_terms_get_plural_name( $term );
```
