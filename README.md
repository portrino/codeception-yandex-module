# Codeception Yandex Module

[![Build Status](https://travis-ci.org/portrino/codeception-yandex-module.svg?branch=master)](https://travis-ci.org/portrino/codeception-yandex-module)
[![Code Climate](https://codeclimate.com/github/portrino/codeception-yandex-module/badges/gpa.svg)](https://codeclimate.com/github/portrino/codeception-yandex-module)
[![Test Coverage](https://codeclimate.com/github/portrino/codeception-yandex-module/badges/coverage.svg)](https://codeclimate.com/github/portrino/codeception-yandex-module/coverage)
[![Issue Count](https://codeclimate.com/github/portrino/codeception-yandex-module/badges/issue_count.svg)](https://codeclimate.com/github/portrino/codeception-yandex-module)
[![Latest Stable Version](https://poser.pugx.org/portrino/codeception-yandex-module/v/stable)](https://packagist.org/packages/portrino/codeception-yandex-module)
[![Total Downloads](https://poser.pugx.org/portrino/codeception-yandex-module/downloads)](https://packagist.org/packages/portrino/codeception-yandex-module)


This package provides validation of responses via [**Structured data validator API**](https://tech.yandex.com/validator/) 
from Yandex. You can automatically check if your embedded [structured data](https://developers.google.com/search/docs/guides/intro-structured-data) 
markup (aka semantic markup) is correct based on the current vocabularies like [schema.org](http://schema.org/). 

> To transmit data to the API, you specify the HTML code or URL of the page. After completing validation, the API outputs 
> the structured data extracted from the page in JSON format, > along with the codes of any errors detected. The following 
> syntaxes are currently supported: JSON-LD, RDFa, microdata, and microformats.

*Source: https://tech.yandex.com/validator/*

With this module you are able to automate tests for semantic data validation in your cests via codeception. 
You save time during development of new features, because you do not have to copy your markup manually into the
[structured data testing tool](https://search.google.com/structured-data/testing-tool) or 
[structured data validator](https://webmaster.yandex.com/tools/microtest/) when checking if some new feature or bugfix
 break your semantic markup.
 
You also can use this module for automating structured data validation for large quantities of pages.

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
  $I->seeResponseContainsValidStructuredDataMarkup();
  // validate only JSON+LD
  $I->seeResponseContainsValidJsonLdMarkup();
  // validate only Microdata
  $I->seeResponseContainsValidMicrodataMarkup();
  // validate only Microformat
  $I->seeResponseContainsValidMicroformatMarkup();
  // validate only Rdfa
  $I->seeResponseContainsValidRdfaMarkup();
  
  // grab the data array from Yandex API response
  // @see: https://tech.yandex.com/validator/doc/dg/concepts/response_standart-docpage/
  $data = $I->grabStructuredDataFromApiResponse()['json-ld'];
  
  // grab the data from Yandex API response via jsonPath 
  // !Important: All chars like . // / and - are replaced by _ to make jsonPath working! 
  $I->assertEquals(
      'foo.com',
      $I->grabStructuredDataFromApiResponseByJsonPath('json_ld.0.http___schema_org_name.0._value')[0]
  );
  
```

### Methods

#### seeResponseContainsValidStructuredDataMarkup()

Validates the current response from `$I->amOnPage('/foo/bar/');` against the structured data validator API and checks 
all supported formats like: **JSON-LD, RDFa, microdata, and microformats**.

```php
  $I->seeResponseContainsValidStructuredDataMarkup();
  
```

#### seeResponseContainsValidJsonLdMarkup()

Validates the current response from `$I->amOnPage('/foo/bar/');` against the structured data validator API and checks 
only the data which is in **JSON-LD** format.

```php
  $I->seeResponseContainsValidJsonLdMarkup();
  
```

#### seeResponseContainsValidMicrodataMarkup()

Validates the current response from `$I->amOnPage('/foo/bar/');` against the structured data validator API and checks 
only the data which is in **microdata** format.

```php
  $I->seeResponseContainsValidMicrodataMarkup();
  
```

#### seeResponseContainsValidMicroformatMarkup()

Validates the current response from `$I->amOnPage('/foo/bar/');` against the structured data validator API and checks 
only the data which is in **microformat** format.

```php
  $I->seeResponseContainsValidMicroformatMarkup();
  
```

#### seeResponseContainsValidRdfaMarkup()

Validates the current response from `$I->amOnPage('/foo/bar/');` against the structured data validator API and checks 
only the data which is in **RDFa** format.

```php
  $I->seeResponseContainsValidRdfaMarkup();
  
```

#### grabStructuredDataFromApiResponse()

Grab the structured data from the current response and it returns it as array. Please have a look at https://tech.yandex.com/validator/doc/dg/concepts/response_standart-docpage/ 
for information about the standard response format of the yandex API.

```php
  $data = $I->grabStructuredDataFromApiResponse();
  
```

#### grabStructuredDataFromApiResponseByJsonPath()

**Experimental!!!** 

Grab the structured data from the current response by jsonPath query syntax with the help of [JSONPath PHP Package](https://github.com/FlowCommunications/JSONPath). 
All "special" chars like: 

* . 
* // 
* / 
* - 
* :
* @
* '
* \

are replaced in json response from yandex structured data validator API by _ to make jsonPath working! 

```php
  $data = $I->grabStructuredDataFromApiResponseByJsonPath('json_ld.0.http___schema_org_name.0._value');
  
```