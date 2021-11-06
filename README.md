> ## Repository abandoned 2021-11-06
>
> This package is abandoned. Use [hivecommerce/magerun2-list-api-endpoints](https://github.com/hivecommerce/magerun2-list-api-endpoints) instead!

# magerun2 plugin: List all API endpoints

This is a plugin for [netz98 Magerun2](https://github.com/netz98/n98-magerun2) to list all API endpoints.

[![Build Status](https://github.com/bitExpert/magerun2-list-api-endpoints/workflows/ci/badge.svg?branch=master)](https://github.com/bitExpert/magerun2-list-api-endpoints)
[![Coverage Status](https://coveralls.io/repos/github/bitExpert/magerun2-list-api-endpoints/badge.svg?branch=master)](https://coveralls.io/github/bitExpert/magerun2-list-api-endpoints?branch=master)
[![Infection MSI](https://badge.stryker-mutator.io/github.com/bitExpert/magerun2-list-api-endpoints/master)](https://infection.github.io)

## Installation

The preferred way of installing `bitexpert/magerun2-list-api-endpoints` is through Composer.
Simply add `bitexpert/magerun2-list-api-endpoints` as a dev dependency:

```
composer.phar require --dev bitexpert/magerun2-list-api-endpoints
```

### Local installation

If you do not want to add the command to one specific project only, you can install the plugin globally by placing the
code in the `~/.n98-magerun2/modules` directory. If the folder does not already exist in your setup, create the folder
by running the following command:

```
mkdir -p  ~/.n98-magerun2/modules
```

The next thing to do is to clone the repository in a subdirectory of `~/.n98-magerun2/modules`:

```
git clone git@github.com:bitExpert/magerun2-list-api-endpoints.git ~/.n98-magerun2/modules/magerun2-list-api-endpoints
```

## Usage

This plugin adds the `api:list:endpoints` command to magerun2.

You are able to filter routes by their respective HTTP methods. To only
see `GET` routes, run magerun2 like this:

```
magerun2 api:list:endpoints --method=get
```

To list all `GET` and `POST` routes, pass a comma-separated list as method argument:

```
magerun2 api:list:endpoints --method=get,post
```

You are able to filter routes by their url. To only see `customers` routes,
run magerun2 like this:

```
magerun2 api:list:endpoints --route=customers
```

Both filters can be combined, to show only `customers` routes with the `GET`
method, run magerun2 like this:

```
magerun2 api:list:endpoints --route=customers --method=get
```

## Contribute

Please feel free to fork and extend existing or add new features and send
a pull request with your changes! To establish a consistent code quality,
please provide unit tests for all your changes and adapt the documentation.

## Want To Contribute?

If you feel that you have something to share, then we’d love to have you.
Check out [the contributing guide](CONTRIBUTING.md) to find out how, as
well as what we expect from you.

## License

This plugin is released under the Apache 2.0 license.
