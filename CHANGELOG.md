# Changelog - PHP Spriter

## v1.2.2 - 2015-03

* [feature] configuration: add possibility to configure tile margins
* [feature] new placeholders {{checksum}} {{timestamp}} & add timestamp to placeholder {{sprite}}
* [maintenance] new placeholder {{sprite}} in ratio template

## v1.2.1 - 2015-01-29

* [fix] checksum file per target

## v1.2.0 - 2015-01-19

* [feature] configuration: add `targets` config param for generation of different CSS/Less/Sass files referencing the same png sprite file
* [feature] new config param `cssFilename` inside `targets`
* [maintenance] `cssFileExtension` is now deprecated

## v1.1.1 - 2014-12-08

* [maintenance] {{name}} placeholder won't create class point anymore.
* [maintenance] default each templates changed and now include class point.

## v1.1.0 - 2014-11-03

* [feature] configuration: add spriteFilepath config param for CSS rule
* [maintenance] rename config param iconDirectory to srcDirectory
* [fix] 0 instead of 0px in CSS rule

## v1.0.0 - 2014-10-20  - first version
