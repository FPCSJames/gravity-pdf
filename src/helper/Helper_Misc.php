<?php

namespace GFPDF\Helper;

use GFPDF\Model\Model_PDF;
use WP_Error;
use GFAPI;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Exception;

/**
 * Common Functions shared throughour Gravity PDF
 *
 * @package     Gravity PDF
 * @copyright   Copyright (c) 2015, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       4.0
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
    This file is part of Gravity PDF.

    Gravity PDF Copyright (C) 2015 Blue Liquid Designs

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * @since  4.0
 */
class Helper_Misc
{

	/**
	 * Check if the current admin page is a Gravity PDF page
	 * @since 4.0
	 * @return void
	 */
	public function is_gfpdf_page() {
		if ( is_admin() ) {
			if ( isset($_GET['page']) && 'gfpdf-' === (substr( $_GET['page'], 0, 6 )) ||
			(isset($_GET['subview']) && 'PDF' === strtoupper( $_GET['subview'] )) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if we are on the current global settings page / tab
	 * @since 4.0
	 * @return void
	 */
	public function is_gfpdf_settings_tab( $name ) {
		if ( is_admin() ) {
			if ( $this->is_gfpdf_page() ) {
				$tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'general';

				if ( $name === $tab ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Gravity Forms has a 'type' for each field.
	 * Based on that type, attempt to match it to Gravity PDFs field classes
	 * @param  String $type The field type we are looking up
	 * @return String / Boolean       The Fully Qualified Namespaced Class we matched, or false
	 * @since 4.0
	 */
	public function get_field_class( $type ) {

		/* change our product field types to use a single master product class */
		$convert_product_type = array( 'quantity', 'option', 'shipping', 'total' );

		if ( in_array( strtolower( $type ), $convert_product_type ) ) {
			$type = 'product';
		}

		/* Format the type name correctly */
		$typeArray = explode( '_', $type );
		$typeArray = array_map( 'ucwords', $typeArray );
		$type      = implode( '_', $typeArray );

		/* See if we have a class that matches */
		$fqns = 'GFPDF\Helper\Fields\Field_';
		if ( class_exists( $fqns.$type ) ) {
			return $fqns.$type;
		}

		return false;
	}

	/**
	 * Converts a name into something a human can more easily read
	 * @param  String $name The string to convert
	 * @return String
	 * @since  4.0
	 */
	public function human_readable( $name ) {
		$name = str_replace( array( '-', '_' ), ' ', $name );

		return mb_convert_case( $name, MB_CASE_TITLE );
	}

	/**
	 * mPDF currently has no cascading CSS ability to target 'inline' elements. Fix image display issues in header / footer
	 * by adding a specific class name we can target
	 * @param  String $html The HTML to parse
	 * @return String
	 */
	public function fix_header_footer( $html ) {
		try {
			/* return the modified HTML */
			return qp( $html, 'img' )->addClass( 'header-footer-img' )->top( 'body' )->children()->html();
		} catch (Exception $e) {
			/* if there was any issues we'll just return the $html */
			return $html;
		}
	}

	/**
	 * Processes a hex colour and returns an appopriately contrasting black or white
	 * @param  String $color The Hex to be inverted
	 * @return String
	 * @since 4.0
	 */
	public function get_contrast( $hexcolor ) {
		$hexcolor = str_replace( '#', '', $hexcolor );

		if ( 6 !== strlen( $hexcolor ) ) {
			$hexcolor = str_repeat( substr( $hexcolor, 0, 1 ), 2 ).str_repeat( substr( $hexcolor, 1, 1 ), 2 ).str_repeat( substr( $hexcolor, 2, 1 ), 2 );
		}

		$r   = hexdec( substr( $hexcolor, 0, 2 ) );
		$g   = hexdec( substr( $hexcolor, 2, 2 ) );
		$b   = hexdec( substr( $hexcolor, 4, 2 ) );
		$yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

		return ($yiq >= 128) ? 'black' : 'white';
	}

	/**
	 * Push an associative array onto the beginning of an existing array
	 * @param  Array  $arr The array to push onto
	 * @param  String $key The key to use for the newly-pushed array
	 * @param  Mixed  $val The value being pushed
	 * @return Array  The modified array
	 */
	public function array_unshift_assoc( $arr, $key, $val ) {
		$arr       = array_reverse( $arr, true );
		$arr[$key] = $val;

		return array_reverse( $arr, true );
	}

	/**
	 * This function recursively deletes all files and folders under the given directory, and then the directory itself
	 * equivalent to Bash: rm -r $dir
	 * @param String $dir The path to be deleted
	 */
	public function rmdir( $dir ) {
		try {
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::CHILD_FIRST
			);

			foreach ( $files as $fileinfo ) {
				$function = ($fileinfo->isDir()) ? 'rmdir' : 'unlink';
				$function($fileinfo->getRealPath());
			}
		} catch (Exception $e) {
			return new WP_Error( 'recursion_delete_problem', $e );
		}

		return rmdir( $dir );
	}

	/**
	 * This function recursively copies all files and folders under a given directory
	 * equivalent to Bash: cp -R $dir
	 * @param  String $source      The path to be copied
	 * @param  String $destination The path to copy to
	 * @return Boolean
	 * @since 4.0
	 */
	public function copyr( $source, $destination ) {
		try {
			if ( ! is_dir( $destination ) ) {
				wp_mkdir_p( $destination );
			}

			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $source, RecursiveDirectoryIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::SELF_FIRST
			);

			foreach ( $files as $fileinfo ) {
				if ( $fileinfo->isDir() ) {
					mkdir( $destination.DIRECTORY_SEPARATOR.$files->getSubPathName() );
				} else {
					copy( $fileinfo, $destination.DIRECTORY_SEPARATOR.$files->getSubPathName() );
				}
			}
		} catch (Exception $e) {
			return new WP_Error( 'recursion_copy_problem', $e );
		}

		return true;
	}

	/**
	 * Get a path relative to the root WP directory, provided a user hasn't moved the wp-content directory outside the ABSPATH
	 * @param  String $path    The relative path
	 * @param  String $replace What ABSPATH should be replaced with
	 * @return String
	 * @since 4.0
	 */
	public function relative_path( $path, $replace = '' ) {
		return str_replace( ABSPATH, $replace, $path );
	}

	/**
	 * Check if the web server can write a file to the path specified
	 * @param  String $path The path to check
	 * @return Boolean
	 * @since  4.0
	 */
	public function is_directory_writable( $path ) {
		$tmp_file = $path.'.tmpFile';

		if ( is_writable( $path ) ) {
			if ( touch( $tmp_file ) && is_file( $tmp_file ) ) {
				unlink( $tmp_file );

				return true;
			}
		}

		return false;
	}

	/**
	 * Modified version of get_upload_path() which just focuses on the base directory
	 * no matter if single or multisite installation
	 * We also only needed the basedir and baseurl so stripped out all the extras
	 * @return Array Base dir and url for the upload directory
	 */
	public function get_upload_details() {
		$siteurl     = get_option( 'siteurl' );
		$upload_path = trim( get_option( 'upload_path' ) );
		$dir         = $upload_path;

		if ( empty($upload_path) || 'wp-content/uploads' == $upload_path ) {
			$dir = WP_CONTENT_DIR.'/uploads';
		} elseif ( 0 !== strpos( $upload_path, ABSPATH ) ) {
			/* $dir is absolute, $upload_path is (maybe) relative to ABSPATH */
			$dir = path_join( ABSPATH, $upload_path );
		}

		if ( ! $url = get_option( 'upload_url_path' ) ) {
			if ( empty($upload_path) || ('wp-content/uploads' == $upload_path) || ($upload_path == $dir) ) {
				$url = WP_CONTENT_URL.'/uploads';
			} else {
				$url = trailingslashit( $siteurl ).$upload_path;
			}
		}

		/*
         * Honor the value of UPLOADS. This happens as long as ms-files rewriting is disabled.
         * We also sometimes obey UPLOADS when rewriting is enabled -- see the next block.
         */
		if ( defined( 'UPLOADS' ) && ! (is_multisite() && get_site_option( 'ms_files_rewriting' )) ) {
			$dir = ABSPATH.UPLOADS;
			$url = trailingslashit( $siteurl ).UPLOADS;
		}

		return array(
			'path' => $dir,
			'url' => $url,
		);
	}

	/**
	 * Attempt to convert the current URL to an internal path
	 * @param  String $url The Url to convert
	 * @return Mixed  (String / Object)      Path on success or WP_Error on failure
	 * @since  4.0
	 */
	public function convert_url_to_path( $url ) {

		/* If $url is empty we'll return early */
		if ( empty(trim( $url )) ) {
			return $url;
		}

		/* Mostly we'll be accessing files in the upload directory, so attempt that first */
		$upload = wp_upload_dir();

		$try_path = str_replace( $upload['baseurl'], $upload['basedir'], $url );

		if ( is_file( $try_path ) ) {
			return $try_path;
		}

		/* If WP_CONTENT_DIR and WP_CONTENT_URL are set we'll try them */
		if ( defined( 'WP_CONTENT_DIR' ) && defined( 'WP_CONTENT_URL' ) ) {
			$try_path = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $url );

			if ( is_file( $try_path ) ) {
				return $try_path;
			}
		}

		/* Include our get_home_path functionality */
		if ( ! function_exists( 'get_home_path' ) ) {
			require_once ABSPATH.'wp-admin/includes/file.php';
		}

		/* If that didn't work let's try use home_url() and get_home_path() */
		$try_path = str_replace( home_url(), get_home_path(), $url );

		if ( is_file( $try_path ) ) {
			return $try_path;
		}

		/* If that didn't work let's try use site_url() and ABSPATH */
		$try_path = str_replace( site_url(), ABSPATH, $url );

		if ( is_file( $try_path ) ) {
			return $try_path;
		}

		/* If we are here we couldn't locate the file */
		return $url;
	}

	/**
	 * Get the arguments array that should be passed to our PDF Template
	 * @param  Array $entry    Gravity Form Entry
	 * @param  Array $settings PDF Settings Array
	 * @return Array
	 * @since 4.0
	 */
	public function get_template_args( $entry, $settings ) {
		/*
         * @todo TEMP FIX so we can render PDF
         */
		error_reporting( E_ALL ^ E_NOTICE );

		$form = GFAPI::get_form( $entry['form_id'] );
		$pdf  = new Model_PDF();

		return apply_filters('gfpdf_template_args', array(

			'form_id'   => $entry['form_id'], /* backwards compat */
			'lead_ids'  => array( $entry['id'] ), /* backwards compat */
			'lead_id'   => $entry['id'], /* backwards compat */

			'form'      => $form,
			'entry'     => $entry,
			'lead'      => $entry,
			'form_data' => $pdf->get_form_data( $entry ),

			'settings' => $settings,

		), $entry, $settings, $form);
	}

	/**
	 * Do a lookup for the current template image (if any) and return the path
	 * @param  String $template The template name to look for
	 * @return String Full URL to image
	 */
	public function get_template_image( $template ) {
		global $gfpdf;

		/* Add our extension */
		$template .= '.png';

		$relative_image_path   = 'initialisation/templates/images/';
		$default_template_path = PDF_PLUGIN_DIR.$relative_image_path;
		$default_template_url  = PDF_PLUGIN_URL.$relative_image_path;

		if ( is_file( $gfpdf->data->template_location.'images/'.$template ) ) {
			return $gfpdf->data->template_location_url.'images/'.$template;
		} elseif ( is_file( $default_template_path.$template ) ) {
			return $default_template_url.$template;
		}

		return false;
	}
}