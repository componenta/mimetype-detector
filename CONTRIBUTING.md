# Development

Run `composer install`, `composer check`, and `composer bench` on PHP 8.4 and 8.5. Pest covers behavior; PHPStan analyzes the production source at its maximum level. The editable MIME catalog is `resources/mime-catalog.json`; run `composer catalog-generate` and `composer catalog-check` to regenerate and verify the generated PHP files.

To test the Config 4 / DI 6 integration with this package installed in a consumer, run `php tests/integration/config-consumer.php /path/to/consumer/vendor/autoload.php`.

The revised 2.0.0 edition retains the `^2.0` dependency constraint. Consumers must review the changed exception imports and contracts in [UPGRADE.md](UPGRADE.md). The integration workflow checks Config 4, DI 6 and the installed Validation package against the current source. To inspect the Validation rule's behavior independently in that environment, run `php tests/integration/validation-consumer.php vendor/autoload.php`.
