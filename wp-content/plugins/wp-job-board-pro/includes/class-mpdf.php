<?php
/**
 * Mpdf
 *
 * @package    wp-job-board-pro
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
  	exit;
}

class WP_Job_Board_Pro_Mpdf {

	public static function mpdf_exec($post) {
		$pdfName = $post->ID.'-'.$post->post_name . '.pdf';
		$pdf_file = self::mpdf_getcachedir() . $pdfName;

		if ( !file_exists( $pdf_file ) ) {
			
			$templatePath = WP_JOB_BOARD_PRO_PLUGIN_DIR;
			$templateFile = WP_Job_Board_Pro_Template_Loader::locate('misc/cv-pdf-template');

			setup_postdata( $GLOBALS['post'] =& $post );
			$pdf_output = '';
			require( $templateFile );
			wp_reset_postdata();

			$pdf_file = self::mpdf_output( $post, $pdf_output, $pdfName, $templatePath );
		}

		return $pdf_file;
	}

	public static function mpdf_output( $post, $wp_content = '', $pdfName = '', $templatePath = '' ) {
		$pdf_filename = $pdfName;

		$cacheDirectory = self::mpdf_getcachedir();
		if ( ! is_dir( $cacheDirectory . 'tmp' ) ) {
			@mkdir( $cacheDirectory . 'tmp' );
		}

		define( '_MPDF_PATH', WP_JOB_BOARD_PRO_PLUGIN_DIR . 'libraries/mpdf/' );
		define( '_MPDF_TEMP_PATH', $cacheDirectory . 'tmp/' );
		define( '_MPDF_TTFONTDATAPATH', _MPDF_TEMP_PATH );

		require_once( _MPDF_PATH . 'autoload.php' );

		$mpdf = new \Mpdf\Mpdf(['tempDir' => _MPDF_TEMP_PATH]);
		
		global $pdf_html_header;
		global $pdf_html_footer;

		
		if ( empty( $pdf_html_header ) ) {
			$pdf_html_header = false;
		}
		if ( empty( $pdf_html_footer ) ) {
			$pdf_html_footer = false;
		}

		global $pdf_orientation;
		if ( $pdf_orientation == '' ) {
			$pdf_orientation = 'P';
		}

		$cp = apply_filters( 'wp-job-board-pro-create-pdf-utf', 'utf-8' );

		// $mpdf = new \Mpdf\Mpdf(['tempDir' => _MPDF_TEMP_PATH]);

		if ( is_rtl() ) {
			$mpdf->SetDirectionality('rtl');
			$mpdf->autoScriptToLang = true;
			$mpdf->autoLangToFont = true;
		}

		//The Header and Footer
		global $pdf_footer;
		global $pdf_header;

		if ( $pdf_html_header ) {
			$mpdf->SetHTMLHeader( $pdf_header );
		} else {
			$mpdf->setHeader( $pdf_header );
		}
		if ( $pdf_html_footer ) {
			$mpdf->SetHTMLFooter( $pdf_footer );
		} else {
			$mpdf->setFooter( $pdf_footer );
		}

		$css_files = apply_filters( 'wp-job-board-pro-style-pdf', array() );
		if ( !empty( $css_files ) ) {
			foreach ($css_files as $css_file) {
				if ( file_exists( $css_file ) ) {
					//Read the StyleCSS
					$tmpCSS = file_get_contents( $css_file );
					$mpdf->WriteHTML( $tmpCSS, 1 );
				}
			}
		}
		
		$mpdf->WriteHTML( $wp_content );

		/**
		 * Allow to process the pdf by an 3th party plugin
		 */
		do_action( 'wp-job-board-pro-mpdf-output', $mpdf, $pdf_filename );

		file_put_contents( self::mpdf_getcachedir() . $pdf_filename , $post->post_modified_gmt );
		$mpdf->Output( self::mpdf_getcachedir() . $pdf_filename, 'F' );
		if ( file_exists(self::mpdf_getcachedir() . $pdf_filename) ) {
			return self::mpdf_getcachedir() . $pdf_filename;
		}
		return '';
	}

	public static function mpdf_delete_file($post) {
		if ( $post ) {
			$pdfName = $post->ID.'-'.$post->post_name . '.pdf';
			$pdf_file = WP_Job_Board_Pro_Mpdf::mpdf_getcachedir() . $pdfName;
			if ( file_exists($pdf_file) ) {
				unlink($pdf_file);
			}
		}
	}

	public static function mpdf_getcachedir() {
		$upload = wp_upload_dir();
		$upload_dir = $upload['basedir'];
		$directory = $upload_dir . '/wp-job-board-pro-resumes/';
		if ( !is_dir( $directory ) ) {
			@mkdir($directory, 0755);
		}

		return $directory;
	}
}
