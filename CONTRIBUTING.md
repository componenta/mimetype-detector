# Development

Run `composer install`, `composer check`, and `composer bench` on PHP 8.4 and 8.5. Pest covers behavior; PHPStan analyzes the production source at its maximum level. The editable MIME catalog is `resources/mime-catalog.json`; run `composer catalog-generate` and `composer catalog-check` to regenerate and verify the generated PHP files.

To test the Config 4 / DI 6 integration with this package installed in a consumer, run `php tests/integration/config-consumer.php /path/to/consumer/vendor/autoload.php`.

The 3.x branch requires consumers to update their dependency constraints and exception imports. The published 2.x validation integration pin is not compatible with 3.x; reinstate its integration check when a corresponding validation branch supports 3.x.
