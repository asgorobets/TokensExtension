# Behat TokensExtension

Behat TokensExtension provides a way for step text and arguments to contain replacement tokens that will be replaced with some context values before step definitions are processed

[![Latest Stable Version](https://poser.pugx.org/behat/tokens-extension/v/stable)](https://packagist.org/packages/behat/tokens-extension)
[![License](https://poser.pugx.org/behat/tokens-extension/license)](https://packagist.org/packages/behat/tokens-extension)
[![Build Status](https://img.shields.io/travis/asgorobets/TokensExtension/master.svg?style=flat)](https://travis-ci.org/asgorobets/TokensExtension)
[![Quality Score](https://img.shields.io/scrutinizer/g/asgorobets/TokensExtension.svg?style=flat)](https://scrutinizer-ci.com/g/asgorobets/TokensExtension)
[![Total Downloads](https://poser.pugx.org/behat/tokens-extension/downloads)](https://packagist.org/packages/behat/tokens-extension)

## Installation

- `curl -sS https://getcomposer.org/installer | php`
- `vim composer.json`
```json
{
  "require": {
    "behat/tokens-extension": "dev-master"
  },
  "config": {
    "bin-dir": "bin"
  }
}
```
- `composer install`
- Enable `TokensExtension` in behat.yml:
```
default:
  extensions:
    Behat\TokensExtension: ~
```

## Testing

```shell
composer update --dev
./vendor/bin/behat
```

## Contributions

Feel free to provide feedback in issue queue and contributions are much welcome.

## Authors

- [Alexei Gorobets (asgorobets)](https://github.com/asgorobets)

## Supporting organizations

Thanks to [FFW Agency](http://www.ffwagency.com/) for supporting this contribution.
