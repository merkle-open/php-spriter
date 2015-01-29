# Changelog - PHP Spriter

## v1.0.0 - 2014-10-20  - first version

## v1.1.0 - 2014-11-03

### Maintenance / Fixes

* configuration: add spriteFilepath config param for CSS rule
* rename config param iconDirectory to srcDirectory
* 0 instead of 0px in CSS rule

## v1.1.1 - 2014-12-08

### Maintenance

* {{name}} placeholder won't create class point anymore.
* default each templates changed and now include class point.

## v1.2.0 - 2015-01-19

### Maintenance

* configuration: add `targets` config param for generation of different CSS/Less/Sass files referencing the same png sprite file
* new config param `cssFilename` inside `targets`
* `cssFileExtension` is now deprecated

## v1.2.1 - 2015-01-29

### Fixes

* checksum file per target
