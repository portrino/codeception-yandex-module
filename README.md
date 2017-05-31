# Codeception Yandex Module

This package provides validation of structured data via yandex API

## Installation

You need to add the repository into your composer.json file

```bash
    composer require --dev portrino/codeception-yandex-module
```

## Usage

You can use this module as any other Codeception module, by adding 'Yandex' to the enabled modules in your Codeception suite configurations.

### Enable module and setup the configuration variables

- After registering you can get the `apiKey` here: *https://developer.tech.yandex.ru/*
- The `url` could be set in config file directly or via an environment variable: `%BASE_URL%`


```yml
modules:
    enabled:
        - Yandex:
            depends: PhpBrowser
            url: ADD_YOUR_BASE_URL_HERE
            apiKey: ADD_YOUR_API_KEY_HERE
 ```  

Update Codeception build
  
```bash
  codecept build
```

### Implement the cept / cest 

```php
  $I->wantToTest('If structured data for page is valid.');
  $I->amOnPage('foo/bar/');
  
  $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
  
  // validate all
  $I->seeResponseContainsValidMarkup();
  // validate only JSON+LD
  $I->seeResponseContainsValidJsonLdMarkup();
  // validate only Microdata
  $I->seeResponseContainsValidMicrodataMarkup();
  // validate only Microformat
  $I->seeResponseContainsValidMicroformatMarkup();
  // validate only Rdfa
  $I->seeResponseContainsValidRdfaMarkup();
  
  // grab the Yandex API response 
  $I->grabStructuredDataFromApiResponse()['json-ld'];
  
```