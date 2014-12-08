<?php

/**
 * @class Icon
 * @author Christian Stuff <christian.stuff@namics.com>
 */
class Icon {
	public $file;
	public $name;
	public $width;
	public $height;
	public $x;
	public $y;
	public $hasHover = false;
	public $hoverIcon = null;

	public function __construct( $file, $name, $width, $height, $x = null, $y = null ) {
		$this->file   = $file;
		$this->name   = $name;
		$this->width  = $width;
		$this->height = $height;
		$this->x      = $x;
		$this->y      = $y;
	}

	public function calcHeight() {
		return $this->height % 2 == 0 ? $this->height : $this->height + 1;
	}

	public function calcWidth() {
		return $this->width % 2 == 0 ? $this->width : $this->width + 1;
	}

	public static function sort( &$icons ) {
		usort( $icons, function ( $a, $b ) {
			return $a->width * $a->height < $b->width * $b->height;
		} );
	}

	public function generateCSSRule( $namespace, $template, $retina ) {
		$width  = $this->width;
		$height = $this->height;
		$x      = $this->x;
		$y      = $this->y;

		return $this->generateTemplate( $namespace, $template, $retina, $width, $height, $x, $y );
	}

	public function generateHoverCSSRule( $namespace, $template, $retina ) {
		if ( $this->hasHover && $this->hoverIcon ) {
			$width  = $this->hoverIcon->width;
			$height = $this->hoverIcon->height;
			$x      = $this->hoverIcon->x;
			$y      = $this->hoverIcon->y;

			return $this->generateTemplate( $namespace, $template, $retina, $width, $height, $x, $y );
		}

		return "";
	}

	private function generateTemplate( $namespace, $template, $retina, $width, $height, $x, $y ) {
		if ( is_array( $retina ) && count( $retina ) > 1 ) {
			$maxRatio = max( $retina );
			$width /= $maxRatio;
			$height /= $maxRatio;
			$x /= $maxRatio;
			$y /= $maxRatio;
		}

		$replacements = array(
			"{{name}}"   => $namespace . $this->name,
			"{{width}}"  => $width . "px",
			"{{height}}" => $height . "px",
			"{{x}}"      => ( $x === 0 ) ? "0" : "-" . $x . "px",
			"{{y}}"      => ( $y === 0 ) ? "0" : "-" . $y . "px"
		);

		return str_replace(
			array_keys( $replacements ),
			array_values( $replacements ),
			$template
		);
	}
}
