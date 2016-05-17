# drupal-sapi2-prototype-demo
Demo example code for drupal-sapi2-prototype

## How to use this module package

The repo is designed to contain as many modules as necessary to showcase `sapi` modules's functionality,
therefore each module should be placed in a separate subdirectory to allow multiple modules to co-exist.

To use the module package, place the repo in your Drupal 8 module directory. Example:

````
$/> cd modules/custom
$/> git clone git@github.com:james-nesbitt/drupal-sapi2-prototype-demo.git sapi_demo_modules
````

# Content generation


To generate content [devel_generate](https://www.drupal.org/project/devel) must be enabled.

To generate "demo_colors" terms :
```sh
drush gent demo_colors 5 --kill
```
To generate "demo_article" nodes :
```sh
drush genc 50 --types=demo_article --kill
```
