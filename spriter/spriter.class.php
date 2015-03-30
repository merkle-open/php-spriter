<?php

/**
 * @class Spriter
 * @author Christian Stuff <christian.stuff@namics.com>
 */
class Spriter {
	public static $version = "1.2.2";

	public $hasGenerated = false;

	protected $srcDirectory;
	protected $spriteDirectory;
	protected $cssDirectory;
	protected $spriteFilepath;
	protected $spriteFilename;
	protected $spriteChecksum = false;
	protected $tileMargin = 0;
	protected $retina = array( 1 );
	protected $retinaDelimiter = "@";
	protected $cssFileExtension = "css"; // deprecated
	protected $namespace = "icon-";
	protected $globalTemplate;
	protected $eachTemplate;
	protected $eachHoverTemplate;
	protected $ratioTemplate;
	protected $timestamp;
	protected $forceGenerate = false;
	protected $ignoreHover = false;
	protected $hoverSuffix = "-hover";

	protected $targets = array();

	private $icons = array();

	public function __construct( $config = array() ) {
		if ( is_array( $config ) ) {
			// config
			foreach ( $config as $key => $val ) {
				if ( property_exists( $this, $key ) ) {
					$this->$key = $val;
				}
			}
			// validation & defaults
			$this->validate();
			if ( !in_array( 1, $this->retina ) ) {
				$this->retina[] = 1;
			}
			$this->checkDefaultTemplates();
			// setup & generation
			$this->setupIcons();
			if ( $this->hasChanged() || $this->forceGenerate ) {
				$this->timestamp = date( 'YmdHis', time() );
				$this->generateChecksum();
				$this->generateSprite();
				$this->generateCSS();
				$this->hasGenerated = true;
			}
		}
		else {
			$this->error( self::INVALID_CONFIG );
		}
	}

	private function checkDefaultTemplates() {
		$defaultGlobal    = file_get_contents( __DIR__ . "/templates/default-global.tpl" );
		$defaultEach      = file_get_contents( __DIR__ . "/templates/default-each.tpl" );
		$defaultEachHover = file_get_contents( __DIR__ . "/templates/default-each-hover.tpl" );
		$defaultRatio     = file_get_contents( __DIR__ . "/templates/default-ratio.tpl" );

		if ( empty( $this->targets ) ) {
			if ( empty( $this->globalTemplate ) ) {
				$this->globalTemplate = $defaultGlobal;
			}
			if ( empty( $this->eachTemplate ) ) {
				$this->eachTemplate = $defaultEach;
			}
			if ( empty( $this->eachHoverTemplate ) ) {
				$this->eachHoverTemplate = $defaultEachHover;
			}
			if ( empty( $this->ratioTemplate ) && count( $this->retina ) > 1 ) {
				$this->ratioTemplate = $defaultRatio;
			}
		}
		else {
			for ( $i = 0; $i < count( $this->targets ); $i ++ ) {
				if ( empty( $this->targets[ $i ]['globalTemplate'] ) ) {
					$this->targets[ $i ]['globalTemplate'] = $defaultGlobal;
				}
				if ( empty( $this->targets[ $i ]['eachTemplate'] ) ) {
					$this->targets[ $i ]['eachTemplate'] = $defaultEach;
				}
				if ( empty( $this->targets[ $i ]['eachHoverTemplate'] ) ) {
					$this->targets[ $i ]['eachHoverTemplate'] = $defaultEachHover;
				}
				if ( empty( $this->targets[ $i ]['ratioTemplate'] ) && count( $this->retina ) > 1 ) {
					$this->targets[ $i ]['ratioTemplate'] = $defaultRatio;
				}
			}
		}
	}

	private function hasChanged() {
		// Check if the generated sprite files exist
		foreach ( $this->retina as $ratio ) {
			if ( !file_exists( $this->getSpriteFilename( $ratio ) ) ) {
				return true;
			}
		}

		// Check if the generated css file exists
		foreach ( $this->targets as $target ) {
			if ( !file_exists( $target['cssDirectory'] . "/" . $target['cssFilename'] ) ) {
				return true;
			}
		}

		// Checksum comparison
		if ( file_exists( __DIR__ . "/" . $this->getChecksumName() ) ) {
			$lastChecksum = file_get_contents( __DIR__ . "/" . $this->getChecksumName() );

			if ( $lastChecksum !== $this->getChecksum() ) {
				return true;
			}
		}
		else {
			return true;
		}

		return false;
	}

	private function setupIcons() {
		if ( $handle = opendir( $this->srcDirectory ) ) {
			while ( false !== ( $file = readdir( $handle ) ) ) {
				if ( $file != "." && $file != ".." && in_array( pathinfo( $file, PATHINFO_EXTENSION ), array(
						"gif",
						"jpg",
						"jpeg",
						"png"
					) )
				) {
					$fullPath   = $this->srcDirectory . "/" . $file;
					$size       = getimagesize( $fullPath );
					$path_parts = pathinfo( $fullPath );
					array_push( $this->icons, new Icon(
						$file,
						$path_parts['filename'],
						$size[0],
						$size[1]
					) );
				}
			}
			// Get the hover indicated icons
			if ( !$this->ignoreHover ) {
				foreach ( $this->icons as $icon ) {
					$hoverIcon = $this->searchHoverIcon( $icon->name );
					if ( $hoverIcon != null ) {
						$icon->hoverIcon = $hoverIcon;
						$icon->hasHover  = true;
					}
				}
			}
		}
		Icon::sort( $this->icons );
	}

	private function searchHoverIcon( $name ) {
		$hoverName = $name . $this->hoverSuffix;
		foreach ( $this->icons as $icon ) {
			if ( $icon->name == $hoverName ) {
				return $icon;
			}
		}

		return null;
	}

	private function createTransparentImage( $width, $height ) {
		$img   = imagecreatetruecolor( $width, $height );
		$white = imagecolorexactalpha( $img, 255, 255, 255, 127 );
		imagefill( $img, 0, 0, $white );
		imagealphablending( $img, false );
		$bg = imagecolorallocate( $img, 0, 0, 0 );
		imagecolortransparent( $img, $bg );
		imagesavealpha( $img, true );

		return $img;
	}

	private function generateSprite() {
		$canvas   = array( 1, 1 );
		$free     = array();
		$maxRatio = 1.0;

		$tileMargin = $this->tileMargin;

		if ( is_array( $this->retina ) && count( $this->retina ) > 1 ) {
			$maxRatio = max( $this->retina );
		}

		foreach ( $this->icons as $icon ) {
			if ( $canvas[0] == 1 && $canvas[1] == 1 ) {
				$icon->x = 0;
				$icon->y = 0;
				$canvas  = array( $icon->width, $icon->height );
			}
			else {
				for ( $i = 0; $i < count( $free ); $i ++ ) {
					if ( $free[ $i ]['width'] >= $icon->width && $free[ $i ]['height'] >= $icon->height ) {
						// icon fits in free area
						$icon->x = $free[ $i ]['x'];
						$icon->y = $free[ $i ]['y'];

						if ( $icon->height + $tileMargin < $free[ $i ]['height'] ) {
							array_push( $free, array(
								'x'      => $free[ $i ]['x'],
								'y'      => $free[ $i ]['y'] + $icon->height + $tileMargin,
								'width'  => $free[ $i ]['width'],
								'height' => $free[ $i ]['height'] - $icon->height - $tileMargin
							) );
						}

						$free[ $i ]['x']      = $free[ $i ]['x'] + $icon->width + $tileMargin;
						$free[ $i ]['width']  = $free[ $i ]['width'] - $icon->width - $tileMargin;
						$free[ $i ]['height'] = $icon->height;
						if ( $free[ $i ]['width'] <= 0 ) {
							array_splice( $free, $i, 1 );
						}
						break;
					}
				}

				if ( is_null( $icon->x ) && is_null( $icon->y ) ) {
					// icon needs new space
					if ( $canvas[1] >= $canvas[0] ) {
						// increase canvas width
						$canvas  = array( $canvas[0] + $icon->width + $tileMargin, $canvas[1] );
						$icon->x = $canvas[0] - $icon->width;
						$icon->y = 0;

						if ( $canvas[1] > $icon->height ) {
							array_push( $free, array(
								'x'      => $canvas[0] - $icon->width,
								'y'      => $icon->height + $tileMargin,
								'width'  => $icon->width,
								'height' => $canvas[1] - $icon->height - $tileMargin
							) );
						}
					}
					else {
						// increase canvas height
						$canvas  = array( $canvas[0], $canvas[1] + $icon->height + $tileMargin );
						$icon->x = 0;
						$icon->y = $canvas[1] - $icon->height;

						if ( $canvas[0] > $icon->width ) {
							array_push( $free, array(
								'x'      => $icon->width + $tileMargin,
								'y'      => $canvas[1] - $icon->height,
								'width'  => $canvas[0] - $icon->width - $tileMargin,
								'height' => $icon->height
							) );
						}
					}
				}
			}
		}

		if ( is_array( $this->retina ) ) {
			foreach ( $this->retina as $ratio ) {
				if ( is_numeric( $ratio ) ) {
					$retina_width  = $canvas[0] * $ratio / $maxRatio;
					$retina_height = $canvas[1] * $ratio / $maxRatio;
					$retina        = self::createTransparentImage( $retina_width, $retina_height );
					foreach ( $this->icons as $i ) {
						$path_parts = pathinfo( $this->srcDirectory . "/" . $i->file );
						$icon_img   = null;
						switch ( $path_parts['extension'] ) {
							case "png":
								$icon_img = imagecreatefrompng( $this->srcDirectory . "/" . $i->file );
								break;
							case "jpg":
							case "jpeg":
								$icon_img = imagecreatefromjpeg( $this->srcDirectory . "/" . $i->file );
								break;
							case "gif":
								$icon_img = imagecreatefromgif( $this->srcDirectory . "/" . $i->file );
								break;
						}
						if ( is_null( $icon_img ) ) {
							continue;
						}
						imagecopyresampled( $retina, $icon_img, (int) ( $i->x * $ratio / $maxRatio ), (int) ( $i->y * $ratio / $maxRatio ), 0, 0, $i->width * $ratio / $maxRatio, $i->height * $ratio / $maxRatio, $i->width, $i->height );
					}
					imagepng( $retina, $this->getSpriteFilename( $ratio ), 9 );
					if ( $ratio == 1 ) {
						$this->width  = imagesx( $retina );
						$this->height = imagesy( $retina );
					}
				}
			}
		}
	}

	private function generateCSS() {

		if ( empty( $this->targets ) ) {
			array_push( $this->targets, array(
				"cssDirectory"      => $this->cssDirectory,
				"cssFilename"       => $this->spriteFilename . "." . $this->cssFileExtension,
				"globalTemplate"    => $this->globalTemplate,
				"eachTemplate"      => $this->eachTemplate,
				"eachHoverTemplate" => $this->eachHoverTemplate,
				"ratioTemplate"     => $this->ratioTemplate
			) );
		}

		for ( $i = 0; $i < count( $this->targets ); $i ++ ) {
			$target = $this->targets[ $i ];

			$result = "";

			$replacements = array(
				"{{spriteFilepath}}"  => $this->spriteFilepath,
				"{{spriteFilename}}"  => $this->spriteFilename,
				"{{sprite}}"          => $this->spriteFilepath . "/" . $this->spriteFilename . ".png?" . $this->timestamp,
				"{{width}}"           => $this->width . "px",
				"{{height}}"          => $this->height . "px",
				"{{namespace}}"       => $this->namespace,
				"{{checksum}}"        => $this->spriteChecksum,
				"{{timestamp}}"       => $this->timestamp,

				"{{spriteDirectory}}" => $this->spriteFilepath // deprecated
			);

			$result .= str_replace(
				array_keys( $replacements ),
				array_values( $replacements ),
				$target['globalTemplate']
			);

			foreach ( $this->icons as $icon ) {
				$result .= $icon->generateCSSRule( $this->namespace, $target['eachTemplate'], $this->retina );
				if ( !$this->ignoreHover && $icon->hasHover ) {
					$result .= $icon->generateHoverCSSRule( $this->namespace, $target['eachHoverTemplate'], $this->retina );
				}
			}

			if ( isset( $target['ratioTemplate'] ) && !empty( $target['ratioTemplate'] ) && is_array( $this->retina ) && count( $this->retina ) > 1 ) {
				$ratios = "";
				foreach ( $this->retina as $ratio ) {
					if ( $ratio > 1 ) {
						$replacements = array(
							"{{spriteFilepath}}"  => $this->spriteFilepath,
							"{{spriteFilename}}"  => $this->spriteFilename,
							"{{sprite}}"          => $this->spriteFilepath . "/" . $this->spriteFilename . $this->retinaDelimiter . $ratio . "x.png?" . $this->timestamp,
							"{{ratio}}"           => $ratio,
							"{{ratioFrag}}"       => $ratio . "/1",
							"{{width}}"           => $this->width . "px",
							"{{height}}"          => $this->height . "px",
							"{{delimiter}}"       => $this->retinaDelimiter,
							"{{namespace}}"       => $this->namespace,
							"{{checksum}}"        => $this->spriteChecksum,
							"{{timestamp}}"       => $this->timestamp,

							"{{spriteDirectory}}" => $this->spriteFilepath, // deprecated
						);
						$ratios       = str_replace(
							                array_keys( $replacements ),
							                array_values( $replacements ),
							                $target['ratioTemplate']
						                ) . "\n" . $ratios;
					}
				}
				$result .= $ratios;
			}

			file_put_contents( $target['cssDirectory'] . "/" . $target['cssFilename'], $result );
		}
	}

	private function getChecksum() {
		$content = "";

		if ( $this->spriteChecksum === false ) {
			foreach ( $this->icons as $icon ) {
				$content .= $icon->file . file_get_contents( $this->srcDirectory . "/" . $icon->file );
			}
			$this->spriteChecksum = sha1( $content );
		}

		return $this->spriteChecksum;
	}

	private function generateChecksum() {
		file_put_contents( __DIR__ . "/" . $this->getChecksumName(), $this->getChecksum() );
	}

	private function getChecksumName() {
		return ".checksum-" . $this->spriteFilename;
	}

	private function getSpriteFilename( $ratio = 1 ) {
		return $this->spriteDirectory . "/" . $this->spriteFilename . ( $ratio > 1 ? $this->retinaDelimiter . $ratio . "x" : "" ) . ".png";
	}

	private function validate() {
		if ( !isset( $this->srcDirectory ) ) {
			$this->error( sprintf( self::MISSING_PROP, 'srcDirectory' ) );
		}
		if ( !isset( $this->spriteDirectory ) ) {
			$this->error( sprintf( self::MISSING_PROP, 'spriteDirectory' ) );
		}
		if ( !isset( $this->spriteFilepath ) ) {
			$this->error( sprintf( self::MISSING_PROP, 'spriteFilepath' ) );
		}
		if ( !isset( $this->spriteFilename ) ) {
			$this->error( sprintf( self::MISSING_PROP, 'spriteFilename' ) );
		}
	}

	private function error( $msg ) {
		throw new Exception( $msg );
	}

	/** error messages **/
	const INVALID_CONFIG = "Spriter: invalid initialization. Please check the documentation.";
	const MISSING_PROP = "Spriter: missing mandatory property \"%s\"";
}
