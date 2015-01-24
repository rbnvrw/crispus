Crispus CMS
========================================
[![Build Status](https://travis-ci.org/rbnvrw/crispus.svg?branch=master)](https://travis-ci.org/rbnvrw/crispus)
[![Code Climate](https://codeclimate.com/github/rbnvrw/crispus/badges/gpa.svg)](https://codeclimate.com/github/rbnvrw/crispus)
[![Test Coverage](https://codeclimate.com/github/rbnvrw/crispus/badges/coverage.svg)](https://codeclimate.com/github/rbnvrw/crispus)

Crispus CMS is a light-weight file based content management system.

Installation
--------------------
Installing Crispus CMS is easy: just add it as a dependency of your project via Composer:
```
"require": {
  "rbnvrw/crispus": "dev-master"
}
```
Then use `composer update` to install Crispus CMS in the `vendor` directory.

Usage
--------------------
See my [Crispus example repository](https://github.com/rbnvrw/crispus-example) for an example on how to use Crispus CMS for your website.

Pages are stored in the `pages` directory as directories. The page directory should contain a `config.json` file with the page properties and one or more Markdown files that correspond to the blocks that are used in the current theme that is rendered via Twig.

Global configuration settings are stored in JSON format in `config/config.json`. In this config file, you can also set the theme that is used. Themes are stored in the `themes` directory and use the Twig template engine.
