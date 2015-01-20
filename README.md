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
Then use `composer update` to install Crispus CMS.

Crispus CMS is now installed in the `vendor` directory. To use it, copy the `index.php` file from Crispus CMS to your root directory. Additionally, copy the `config`, `content`, `controllers` and `themes` folders to your root directory to get started.

Usage
--------------------
Crispus CMS uses Markdown files in the `content` directory to store pages. To create a new page, simply create a new Markdown file in this directory. Page properties can be set via the header of the Markdown file, see the provided examples for details.

You can extend the functionality of pages via the controllers in the `controllers` directory. By default, `IndexController.php` is used, except when `PagenameController.php` exists, where `Pagename` is the name of the Markdown file of the page, with a capital first letter.

Configuration settings are stored in JSON format in `config/config.json`. In this config file, you can also set the theme that is used. Themes are stored in the `themes` directory and use the Twig template engine.
