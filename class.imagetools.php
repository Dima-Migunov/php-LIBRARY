<?php
//============================================================+
// File name   : class.imagetools.php
// Version     : 1.0.0
// Last update : 2013-02-06
// Author      : Dmitri Migunov (TuxoH)
// -------------------------------------------------------------------
// Copyright (C) 2013 Dmitri Migunov (TuxoH)
//
// -------------------------------------------------------------------
//
// Description :
//   This is a PHP class for generating images: resize, crop and smart crop.
//
// Main features :
//  * Easy to use even for baby;
//  * ImageTools::smartCrop() trying optimally resize image and crop;
//  * The library can be implement in all frameworks, systems or scripts;
//
// Warning :
//   ImageTools::smartCrop() will be create temporary files in the temp directory!
//   Default ImageTools::$temp='/tmp'
//   You must change this path if this path is not right.
//============================================================+
// Required: PHP 5.3, GD 2, ImageMagick

class ImageTools{
	public static $temp					= '/tmp'; // Temporary directory
	public static $quality			= 85;	// JPEG quality of destination image
	public static $rgb					= 0xFFFFFF;// Background color
	public static $image_format	= 'jpg';

	/***********************************************************************************
	Function ImageTools::resize(): thumbnail generation
	Parameters:
	  $src             - source file name
	  $dest            - generated file name
	  $width, $height  - width and height of generated image (in pixels)
	Optional parameters:
	  $rgb             - background color, defualt = white (0xFFFFFF)
	  $quality         - quality of generated JPEG, default = 85
	***********************************************************************************/
	public static function resize( $src, $dest, $width, $height, $quality=NULL, $rgb=NULL ){
		if ( !self::checkParams( $src, $quality, $rgb ) ){
			return FALSE;
		}

		$isrc = NULL;
		
		if ( !self::imageCreateFrom( $src, $isrc ) ){
			return FALSE;
		}

		if( self::resizeGD( $isrc, $width, $height, $rgb ) ){
			return FALSE;
		}

		self::createImage( $isrc, $dest, $quality );
	  
		return TRUE;
	}

	public static function resizeGD( &$im, $width, $height, $rgb=null ){
		$size = array(
			imagesx( $im ),
			imagesy( $im )
		);
		
	  $x_ratio	= $width / $size[0];
	  $y_ratio	= $height / $size[1];
		$ratio		= 0;
		
		if( $x_ratio && $y_ratio ){
			$ratio	= min( $x_ratio, $y_ratio );
		}
		else{
			$ratio	= ( $x_ratio ) ? $x_ratio : $y_ratio;
			
			if( ! $width )	$width	= $size[0] * $y_ratio;
			if( ! $height )	$height	= $size[1] * $x_ratio;
		}
		
		if( ! $ratio ){
			return;
		}
		
		
	  $use_x_ratio = ( $x_ratio == $ratio );

	  $new_width   = $use_x_ratio  ? $width  : ceil( $size[0] * $ratio );
	  $new_height  = !$use_x_ratio ? $height : ceil( $size[1] * $ratio );
	  $new_left    = $use_x_ratio  ? 0 : ceil( ( $width - $new_width ) / 2 );
	  $new_top     = !$use_x_ratio ? 0 : ceil( ( $height - $new_height ) / 2 );

	  $idest = imagecreatetruecolor( $width, $height );
	  imagefill( $idest, 0, 0, $rgb );

	  imagecopyresampled( $idest, $im, $new_left, $new_top, 0, 0, $new_width, $new_height, $size[0], $size[1] );
	  imagedestroy( $im );
		$im = $idest;
	}

	/***********************************************************************************
	Function ImageTools::crop(): crop image
	Parameters:
	  $src             - source file name
	  $dest            - generated file name
	  $x, $y           - coordinates of the cropped region's top left corner.
                       Also $x may be 'left','center' and 'right',
                       and $y may be 'top','center' and 'bottom'
	  $width, $height  - width and height of generated image (in pixels)
	Optional parameters:
	  $rgb             - background color, defualt = white (0xFFFFFF)
	  $quality         - quality of generated JPEG, default = 85
	***********************************************************************************/
	public static function crop( $src, $dest, $x, $y, $width, $height, $quality=NULL, $rgb=NULL ){
		if ( !self::checkParams( $src, $quality, $rgb ) ){
			return FALSE;
		}

		$isrc = NULL;
		
		if ( !self::imageCreateFrom( $src, $isrc ) ){
			return FALSE;
		}

		self::cropGD( $isrc, $x, $y, $width, $height, $rgb );

		self::createImage( $isrc, $dest, $quality );
    return TRUE;
	}

	public static function cropGD( &$im, $x, $y, $width, $height, $rgb=NULL ){
		$size = array(
			imagesx( $im ),
			imagesy( $im )
		);
		
		// correction
		if ( $width > $size[0] ){
			$width	= $size[0];
		}
		
		if ( $height > $size[1] ){
			$height	= $size[1];
		}

		if ( !is_numeric( $x ) ){
			$x = strtolower( trim( $x ) );

			switch ( $x ):
				case 'left':		$x = 0; break;
				case 'center':	$x = ceil( ($size[0] - $width)/ 2 ); break;
				case 'right':		$x = $size[0] - $width; break;
				default: $x = 0;
			endswitch;
		}

		if ( !is_numeric( $y ) ){
			$y = strtolower( trim( $y ) );
			
			switch ( $y ):
				case 'top':			$y = 0; break;
				case 'center':	$y = ceil( ($size[1] - $height) / 2 ); break;
				case 'bottom':	$y = $size[1] - $height; break;
				default: $y = 0;
			endswitch;
		}

		// correction
		if ( is_numeric( $x ) && $x < 0 ){
			$x = 0;
		}
		
		if ( is_numeric( $y ) && $y < 0 ){
			$y = 0;
		}

    $idest = imagecreatetruecolor( $width, $height );

    imagefill( $idest, 0, 0, $rgb );
    imagecopyresampled( $idest, $im, 0, 0, $x, $y, $width, $height, $width, $height );

    imagedestroy( $im );
		$im = &$idest;
	}

	/***********************************************************************************
	Function ImageTools::smartCrop(): resize + optimal crop
	Parameters:
	  $src             - source file name
	  $dest            - generated file name
	  $width, $height  - width and height of generated image (in pixels)
	Optional parameters:
	  $rgb             - background color, defualt = white (0xFFFFFF)
	  $quality         - quality of generated JPEG, default = 85
	***********************************************************************************/
	public static function smartCrop(  $src, $dest, $width, $height, $quality=NULL, $rgb=NULL ){
		if ( !self::checkParams( $src, $quality, $rgb ) ){
			return FALSE;
		}

		$size = getimagesize( $src );
		
		if ( FALSE === $size ){
			return FALSE;
		}

		$W = $size[0];
		$H = $size[1];
		$k = $W / $width;

		if ( $height > ( $H / $k ) ){
			$k = $H / $height;
			$W = ceil( $W / $k );
			$H = $height;
		}
		else{
			$W = $width;
			$H = ceil( $H / $k );
		}

		if ( !self::resize( $src, $dest, $W, $H, 100 ) ){
			return FALSE;
		}

		$w2 = ceil( $width / 2 );
		$h2 = ceil( $height / 2 );
		list( $x, $y ) = self::gravity( $dest );

		$x = $x - $w2;
		if ( 0 > $x ) $x = 0;
		if ( $W < ( $x + $width ) ) $x = $W - $width;

		$y = $y - $h2;
		if ( 0 > $y ) $y = 0;
		if ( $H < ( $y + $height ) ) $y = $H - $height;

		return self::crop( $dest, $dest, $x, $y, $width, $height, $quality, $rgb );
	}

	public static function imageCreateFrom( $src, &$isrc ){
		if ( !file_exists( $src ) ){
			return FALSE;
		}

		$size = getimagesize( $src );
		
		if ( FALSE === $size ){
			return FALSE;
		}

		$format = strtolower( substr( $size['mime'], strpos($size['mime'], '/' ) + 1 ) );
		$icfunc = 'imagecreatefrom'.$format;

		if ( !function_exists( $icfunc ) ){
			return FALSE;
		}

		$isrc = $icfunc( $src );

		return TRUE;
	}

	protected static function gravity( $src ){
		// parameters for the edge-maximizing crop algorithm
		$r = 2;         // radius of edge filter

		$tmp_img = self::$temp . '/' . md5( date("r") ) . '.jpg';

		$size = getimagesize( $src );

		$w = $size[0];
		$h = $size[1];

		$img = new Imagick( $src );
		
		// compute center of edginess
		$img->edgeImage( $r );
		$img->modulateImage( 100, 0, 100 ); // grayscale
		$img->blackThresholdImage( "#0f0f0f" );
		$img->writeImage( $tmp_img );
		$img->destroy();
		
		unset( $img );
		
		// use gd for random pixel access
		$im = imagecreatefromjpeg( $tmp_img );
		$xcenter = $ycenter = $sum = 0;
		$n = 100000;
		
		for ( $k=0; $k<$n; $k++ ){
			$i		= mt_rand( 0, $w-1 );
			$j		= mt_rand( 0, $h-1 );
			$val	= imagecolorat( $im, $i, $j ) & 0xFF;

			$sum			+= $val;
			$xcenter	+= ( $i+1 ) * $val;
			$ycenter	+= ( $j+1 ) * $val;
		}
		
		$xcenter /= $sum;
		$ycenter /= $sum;

		imagedestroy( $im );
		unlink( $tmp_img );
		
		return array( ceil( $xcenter ),  ceil( $ycenter ) );
	}

	protected static function checkParams( &$src, &$quality, &$rgb ){
		if ( !file_exists( $src ) ){
			return FALSE;
		}

		if ( !$quality ){
			$quality = self::$quality;
		}
		
		if ( !$quality ){
			return FALSE;
		}

		if ( !$rgb ){
			$rgb = self::$rgb;
		}
		
		if ( !$rgb ){
			return FALSE;
		}

		return TRUE;
	}

	protected static function createImage( &$idest, &$dest, &$quality ){
		switch ( self::$image_format ){
			case 'png':
				self::createPNG( $idest, $dest, $quality );
				return;

			default:
				self::createJPG( $idest, $dest, $quality );
		}
	}

	protected static function createJPG( &$idest, &$dest, &$quality ){
		if( !preg_match( "#\.(jpe?g)$#i", $dest ) ){
			$dest .= '.jpg';
		}

		imagejpeg( $idest, $dest, $quality );
	  imagedestroy( $idest );
	}

	protected static function createPNG( &$idest, &$dest, &$quality ){
		if( !preg_match( "#\.(png)$#i", $dest ) ){
			$dest .= '.png';
		}

		$png_quality = intval( 9 * $quality / 100 );

		imagepng( $idest, $dest, $png_quality );
	  imagedestroy( $idest );
	}

}