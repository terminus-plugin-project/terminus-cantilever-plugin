# Terminus Cantilever Plugin
[![Terminus v2.x Compatible](https://img.shields.io/badge/terminus-v2.x-green.svg)](https://github.com/terminus-plugin-project/terminus-cantilever-plugin/tree/2.x)

* isolate environments by framework (drupal, drupal8, wordpress)
* isolate environments by plan (sandbox, basic, performance small, performance medium, elite)
* run drush and/or wp-cli commands, or really any commands at all
* provide organized report of operations on per site basis

## Watch the video

Watch [the video](https://youtu.be/YMUEQuFX4Po/ "Cantilever Video") to learn more about the why and how of Cantilever.

## Use Case
This plugin extends `site:list` to allow users to execute commands on each site that is listed.

It will take arguments for environment `--env=`, framework `--frame=`, tags `--tags=` and organizations `--org=`.

You can also specify a command `--command=` to apply to all of the sites targeted.

Ex. `terminus can --frame='wordpress' --env='dev' --command='terminus [site] wp plugin update --all && terminus [site] wp theme update themename'`.

In the command, you have tokens available to use as placeholders for each sites data.
* `[site]` will produce site.env; Ex. `yoursite.live`
* `[name]` will produce the sites name; Ex. `yoursite`
* `[env]` will produce the environment selected; Ex. `live`


## Installation
To install this plugin place it in `~/.terminus/plugins/`.

On Mac OS/Linux:
```
mkdir -p ~/.terminus/plugins
composer create-project -d ~/.terminus/plugins terminus-plugin-project/terminus-cantilever-plugin:~2
```

## Examples

```terminus can --env=live --plan='basic,performance small,performance medium' --frame='drupal,drupal8' --command='terminus drush [site] pml|grep redis'```

```terminus can --env=live --frame='wordpress' --command='terminus wp [site] option get home'```

## Help
Run `terminus can --help` for help.

## TODO

* Add support for organization/membership tags
* Allow interactive input for filters

This plugin is provided by [Inclind](https://www.inclind.com "Inclind's Homepage")
