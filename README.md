# Part framework
This framework has the recommended file structure of a website using it, but should be used with composer.

## Build status
[![Build Status](https://travis-ci.org/budde377/Part.svg?branch=master)](https://travis-ci.org/budde377/Part)

## Requirements
For now the system has only been tested on machines running Ubuntu, but other linux distros should work as well. 

You'll need the following software:

 * A LAMP server with PHP 5.5
 * `dart-pub`
 * `php5-json`
 * `php5-imagick`

## Install
There will be some sinister way of installing your own mock website... 

## Local initialization
When the system is installed there is still plenty to be done. While the file structure is all op to you, we
recommend that you let apache point to the ./www folder after installation and that you use the designated ./dart folder
 for dart files, structured as a package.

The installation also provides a Makefile, this assumes that you follows the structure provided. This will make your
dart package (and fetching dependencies) and look after a composer file at `./composer.json`, and fetch it.

When on a development machine, run `update-dev`. This will fetch various development dependencies.

### The `site-config.xml` file

All your sensitive information should be declared in a site-config.xml file. Please ensure that you set-up your .htaccess 
right, so none has access to it other than you.

### Providing your own `SiteFactory`
Since the system is structured in a OOP manner, build on some default instance of `SiteFactory`. You can however provide
your own, by creating the file `./local.php` and write to the variable `$factory`. This will install the system with your
factory.


### Writing templates
The system relies on [Twig](http://twig.sensiolabs.org/) for templates. All templates in `./common/templates` are
automatically loaded. Your template folder is specified in `site-config.xml` (usually `./templates/`).

#### Environment
In order to ease your work, some variables has been added to the environment. These are

 * `site`: The current site instance
 * `user_lib`: The user library instance
 * `current_user`: The current user, null if none logged in
 * `has_root_privileges`: A boolean indicating whether the current user has root privileges
 * `has_site_privileges`: A boolean indicating whether the current user has site privileges
 * `has_page_privileges`: A boolean indicating whether the current user has page privileges to the current page
 * `page_order`: The page order
 * `current_page`: The current page, from the current page strategy.
 * `current_page_path`: The current page path, from the current page strategy.
 * `page_element_factory`: The factory creating and reusing page_elements.
 * `css_register`: A css register. The use of this should generally be avoided.
 * `js_register`: A JavaScript register. The use of this should generally be avoided.
 * `debug_mode`: A boolean indicating whether the site is running in debug mode.
 * `initialize`: A boolean indicating whether the template is initializing (short render).
 * `last_modified`: Unix timestamp (in seconds) indicating when this page was last edited, taking site modification into account.
 * `updater`: The updater. This might be null, if the updater is not enabled in config.
 * `config`: The config instance.
 * `backend_container`: The backend container. Pretty much the spine of the site.

#### Custom nodes

The CMS provides some Twig nodes that should ease the development of templates.

 * `{% init_page_element {page-element} %}` This sets up the page element, without generating content.
 * `{% page_element {page-element} %}` This sets up the page element, generates content and inserts it. The `page_element` is either an class or an name as defined in setup.
 * `{% page_variable {page}[{id}] %}` or `{% page_variable {id} %}` This inserts the content of a page variable of the page (current or provided).
 * `{% page_content {page}[{optional id}] %}` or `{% page_content {optional id} %}` This inserts the latest page content of the page (current or provided).
 * `{% site_variable {id} %}`. This inserts the site variable matching id provided.
 * `{% site_content {optional id} %}`. This inserts the latest site content.
