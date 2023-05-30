# DbpRelayCoreConnectorTextfileBundle

[GitHub](https://github.com/digital-blueprint/relay-core-connector-textfile-bundle) |
[Packagist](https://packagist.org/packages/dbp/relay-core-connector-textfile-bundle)

The core_connector_textfile bundle provides an implementation of the `AuthorizationDataProviderInterface` 
which retrieves user attributes used for access control from the bundle's config file.

## Bundle installation

You can install the bundle directly from [packagist.org](https://packagist.org/packages/dbp/relay-core-connector-textfile-bundle).

```bash
composer require dbp/relay-core-connector-textfile-bundle
```

## Integration into the Relay API Server

* Add the bundle to your `config/bundles.php` in front of `DbpRelayCoreBundle`:

```php
...
Dbp\Relay\CoreConnectorTextfileBundle\DbpRelayCoreConnectorTextfileBundle::class => ['all' => true],
Dbp\Relay\CoreBundle\DbpRelayCoreBundle::class => ['all' => true],
];
```

If you were using the [DBP API Server Template](https://github.com/digital-blueprint/relay-server-template)
as template for your Symfony application, then this should have already been generated for you.

* Run `composer install` to clear caches

## Configuration

User authorization attributes can be defined using the bundle config. For this create `config/packages/dbp_relay_core_connector_textfile.yaml`. 

Here is an example config file:

```yaml
dbp_relay_core_connector_textfile:
  # used to define groups used for the attribute mapping
  groups: 
    - name: DEVELOPERS
      users:
        - junior
        - senior

  # used to declare available attributes
  attributes: 
    - name: ROLE_DEVELOPER
      default_value: false # default value: 'null' for scalar and '[]' for array attributes
    - name: ORGANIZATION_UNITS 
      array: true # default value: 'false'

  # used to define values for the attributes
  # each mapping entry specifies a value for an attribute for one or many users and/or groups
  attribute_mapping: 
    - name: ROLE_DEVELOPER
      groups:
        - DEVELOPERS
      value: true
    - name ORGANIZATION_UNITS
      groups:
        - DEVELOPERS
      values:
        - 1
        - 2
    - name ORGANIZATION_UNITS
      users:
        - foo
      values:
        - 3

```

If you were using the [DBP API Server Template](https://gitlab.tugraz.at/dbp/relay/dbp-relay-server-template)
as template for your Symfony application, then the configuration file should have already been generated for you.

For more info on bundle configuration see <https://symfony.com/doc/current/bundles/configuration.html>.

## Development & Testing

* Install dependencies: `composer install`
* Run tests: `composer test`
* Run linters: `composer run lint`
* Run cs-fixer: `composer run cs-fix`

## Bundle dependencies

Don't forget you need to pull down your dependencies in your main application if you are installing packages in a bundle.

```bash
# updates and installs dependencies of dbp/relay-core-connector-textfile-bundle
composer update dbp/relay-core-connector-textfile-bundle
```
