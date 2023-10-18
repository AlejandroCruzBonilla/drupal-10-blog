# Contributing to Single File Components

Beyond all the normal Drupal.org practices for contributing, here are some
tips for working on Single File Components locally.

## Testing standards

This project currently has 100% test coverage, in that 100% of public class
methods are executed during test runs. That doesn't make the coverage perfect,
but maintaining 100% test coverage is important when adding new features.

Please try to write tests for every patch you contribute!

## Running tests

All tests are written with PHPUnit. To run tests, run this command from the
root directory of your Drupal installation:

```
./vendor/bin/phpunit -c core modules/sfc
```

## Checking coding standards

Ensure that you have PHPCS installed globally with the Drupal standard (docs
at https://www.drupal.org/docs/8/modules/code-review-module/installing-coder-sniffer),
then run this command from the `sfc` folder:

```
phpcs --standard=Drupal,phpcs.xml .
```

## Checking code coverage

Before checking code coverage, copy `./core/phpunit.xml.dist` to
`./core/phpunit.xml`, then under `Filter for coverage reports` change the rule
to only whitelist the `sfc` module. For example:

```
  <filter>
    <whitelist>
        <directory>../modules/sfc</directory>
     </whitelist>
  </filter>
```

Code coverage for Unit and Kernel tests can then be checked by running this
command from the root directory of your Drupal installation:

```
./vendor/bin/phpunit -c core modules/sfc --exclude-group functional --coverage-html sfc
```
