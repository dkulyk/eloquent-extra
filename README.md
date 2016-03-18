eloquent-extra - Additional features for laravel Eloquent ORM
======================================
[![Build Status](https://travis-ci.org/dkulyk/eloquent-extra.svg?branch=master)](https://travis-ci.org/dkulyk/eloquent-extra)
[![StyleCI](https://styleci.io/repos/50935963/shield)](https://styleci.io/repos/50935963)

This package will help you to provide an additional features to your Eloquent models:

## Install

#### 1. Require with composer
Require this package using composer:

```
composer require dkulyk/eloquent-extra
```

#### 2. Load the Service Provider
Add the `DKulyk\Eloquent\ServiceProvider` class to your `providers` array in `config/app.php`:

```
DKulyk\Eloquent\ServiceProvider::class,
```

#### 3. Publish the package configuration (optional)
Publish the package configuration

```
php artisan vendor:publish --provider="DKulyk\Eloquent\ServiceProvider" --tag="config"
```

#### 4. Create migration tables

##### Create logging table
Eloquent logging require table for log data
```
php artisan eloquent-extra:logging
```

##### Create migration for EAV tables
This needs a couple of tables for creating the EAV schema structure.

```
php artisan eloquent-extra:properties
```

Then just migrate:

```
php artisan migrate
```

### Features
#### 1. Eloquent logging

TODO:
#### 2. EAV support

TODO:
#### 3. Relations with localization

TODO:
#### 4. Support for transactions while saving the model

TODO:

