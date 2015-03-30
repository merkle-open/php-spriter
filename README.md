# PHP Spriter - The icon sprite generator

PHP Spriter is an easy to use and flexible icon sprite generator.
It can be included in any PHP project and works on the fly.

## Table of contents

* [Installation](#installation)
* [Configuration](#configuration)
* [Generation state](#generation-state)
* [Templates](#templates)
* [Contributing](#contributing)
* [Credits & License](#credits)

## Installation

You need a PHP environment 5.3+ compiled with support for GD (but this should already be in place).
Put these files in a directory inside your project.
Add...

    require_once 'path/to/spriter/spriter.inc.php';
    new Spriter($your_spriter_configuration);

...to the desired place inside your project where you want Spriter to get in action.

## Configuration

    $your_spriter_configuration = array(
        "forceGenerate" => false,                 // set to true if you want to force the CSS and sprite generation.

        "srcDirectory" => "/path/to/src/images", // folder that contains the source pictures for the sprite.
        "spriteDirectory" => "/path/to/sprite",   // folder where you want the sprite image file to be saved (folder has to be writable by your webserver)

        "spriteFilepath" => "path/to/sprite",     // path to the sprite image for CSS rule.
        "spriteFilename" => "icon-sprite",        // name of the generated CSS and PNG file.

        "tileMargin" => 0,                        // margin in px between tiles (default 0)
        "retina" => array(2, 1),                  // defines the desired retina dimensions, you want.
        "retinaDelimiter" => "@",                 // delimiter inside the sprite image filename.
        "namespace" => "icon-",                   // namespace for your icon CSS classes

        "ignoreHover" => false,                   // set to true if you don't need hover icons
        "hoverSuffix" => "-hover",                // set to any suffix you want.

        "targets" => array(
            // you can define multiple targets that will all reference the same png sprite
            array(
                "cssDirectory" => "/path/to/css",         // folder where you want the sprite CSS to be saved (folder has to be writable, too)
                "cssFilename" => "icon-sprite.less",      // your CSS/Less/Sass target file
                "globalTemplate" => "...",                // global template, which contains general CSS styles for all icons (remove line for default)
                "eachTemplate" => "...",                  // template for each CSS icon class (remove line for default)
                "eachHoverTemplate" => "...",             // template for each CSS icon hover class (remove line for default)
                "ratioTemplate" => "..."                  // template for each retina media query (remove line for default)
            )
        )

    );

### Naming rules

You can specify the icons, that shall be generated for hover states with your configured `hoverSuffix` property. If `hoverSuffix` contains e.g. `"-hover"` then you should have two files with the same basename and one of them containing the suffix. `arrow-right.png` and `arrow-right-hover.png` would result in a `icon-arrow-right` CSS class, which will have the eachHoverTemplate applied.

## Generation state

If you want to check, if Spriter has made a new generation you can do the following

    $spriter = new Spriter($your_spriter_configuration);

    if($spriter->hasGenerated) {
        // do the stuff, you need, when files have been generated
    }

## Templates

You can simply edit the existing template files (placed at spriter/templates) to your needs. You can also add your own template files (if you want to).

### Global template

The global template represents the general CSS declarations for each icon.
Spriter comes with the following default template:

    .icon, .icon-after:after, .icon-before:before {
        background-image: url({{spriteFilepath}}/{{spriteFilename}}.png);
        background-repeat: no-repeat;
        background-size: {{width}} {{height}};
        display: inline-block;
    }

    .icon-after:after, .icon-before:before {
        top: 0;
        margin: 0;
        padding: 0;
        content: "";
        display: inline-block;
        position: relative;
    }

    .icon-after:after {
        right: 0;
    }

    .icon-before:before {
        left: 0;
    }

The following placeholders can be used inside the global template:

* {{namespace}} = configured namespace
* {{width}} = generated sprite width
* {{height}} = generated sprite height
* {{spriteFilepath}} = configured sprite directory
* {{spriteFilename}} = configured sprite filename
* {{sprite}} = {{spriteFilepath}}/{{spriteFilename}}.png

### Each Template

The each template represents the CSS declarations for a single named icon.
The following default template comes with Spriter:

    .{{name}}, .{{name}}-after:after, .{{name}}-before:before { background-position: {{x}} {{y}}; width: {{width}}; height: {{height}}; }

You can use the following placeholders:

* {{name}} = name of the icon (this is the icons filename without file extension)
* {{x}} = the top position on the background sprite
* {{y}} = the left position on the background sprite
* {{width}} = the width of the icon
* {{height}} = the height of the icon

### Each Hover Template

The each template represents the CSS declarations for a single named icons hover state.
The following default template comes with Spriter:

    .{{name}}:hover, .{{name}}-after:hover:after, .{{name}}-before:hover:before { background-position: {{x}} {{y}}; width: {{width}}; height: {{height}}; }

You can use the following placeholders:

* {{name}} = name of the icon (this is the icons filename without file extension and without hoverSuffix)
* {{x}} = the top position on the background sprite
* {{y}} = the left position on the background sprite
* {{width}} = the width of the icon
* {{height}} = the height of the icon

### Ratio Template

With the ratio template you can configure the CSS definitions for the retina media queries.
This is the default template:

    @media only screen and (-webkit-min-device-pixel-ratio: {{ratio}}),
    only screen and (-o-min-device-pixel-ratio: {{ratioFrag}}),
    only screen and (min-device-pixel-ratio: {{ratio}}) {
        .icon, .icon-after:after, .icon-before:before {
            background-image: url({{spriteFilepath}}/{{spriteFilename}}{{delimiter}}{{ratio}}x.png);
            -webkit-background-size: {{width}} {{height}};
            -moz-background-size: {{width}} {{height}};
            background-size: {{width}} {{height}};
        }
    }

You can use the following placeholders:

* {{ratio}} = the retina ratio value
* {{ratioFrag}} = the retina ratio value as a fragment (for opera)
* {{delimiter}} = the configured delimiter
* {{namespace}} = configured namespace
* {{width}} = generated sprite width
* {{height}} = generated sprite height
* {{spriteFilepath}} = configured sprite directory
* {{spriteFilename}} = configured sprite filename

## Contributing

* For Bugs & Features please use [github](https://github.com/namics/php-spriter/issues)
* Feel free to fork and send PRs. That's the best way to discuss your ideas.

## Credits

PHP Spriter was created by [Christian Stuff](https://github.com/Regaddi)

## License

Released under the [MIT license](LICENSE)
