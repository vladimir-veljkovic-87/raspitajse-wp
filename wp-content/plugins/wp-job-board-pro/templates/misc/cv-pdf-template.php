<?php

	//Standard Plan Template
	global $post;
	global $pdf_output;
	global $pdf_header;
	global $pdf_footer;

	global $pdf_template_pdfpage;
	global $pdf_template_pdfpage_page;
	global $pdf_template_pdfdoc;

	global $pdf_html_header;
	global $pdf_html_footer;

	//Set a pdf template. if both are set the pdfdoc is used. (You didn't need a pdf template)
	$pdf_template_pdfpage 		= ''; //The filename off the pdf file (you need this for a page template)
	$pdf_template_pdfpage_page 	= 1;  //The page off this page (you need this for a page template)

	$pdf_template_pdfdoc  		= ''; //The filename off the complete pdf document (you need only this for a document template)
	
	$pdf_html_header 			= false; //If this is ture you can write instead of the array a html string on the var $pdf_header
	$pdf_html_footer 			= false; //If this is ture you can write instead of the array a html string on the var $pdf_footer

	//Set the Footer and the Header
	$pdf_header = array (
  		'odd' => 
  			array (
    			'R' => 
   					array (
						'content' => '{PAGENO}',
						'font-size' => 8,
						'font-style' => 'B',
						'font-family' => 'DejaVuSansCondensed',
    				),
    				'line' => 1,
  				),
  		'even' => 
  			array (
    			'R' => 
    				array (
						'content' => '{PAGENO}',
						'font-size' => 8,
						'font-style' => 'B',
						'font-family' => 'DejaVuSansCondensed',
    				),
    				'line' => 1,
  			),
	);
	$pdf_footer = array (
	  	'odd' => 
	 	 	array (
	    		'R' => 
	    			array (
						'content' => '{DATE d.m.Y}',
					    'font-size' => 8,
					    'font-style' => 'BI',
					    'font-family' => 'DejaVuSansCondensed',
	    			),
	    		'C' => 
	    			array (
	      				'content' => '- {PAGENO} / {nb} -',
	      				'font-size' => 8,
	      				'font-style' => '',
	      				'font-family' => '',
	    			),
	    		'L' => 
	    			array (
	      				'content' => get_bloginfo('name'),
	      				'font-size' => 8,
	      				'font-style' => 'BI',
	      				'font-family' => 'DejaVuSansCondensed',
	    			),
	    		'line' => 1,
	  		),
	  	'even' => 
			array (
	    		'R' => 
	    			array (
						'content' => '{DATE d.m.Y}',
					    'font-size' => 8,
					    'font-style' => 'BI',
					    'font-family' => 'DejaVuSansCondensed',
	    			),
	    		'C' => 
	    			array (
	      				'content' => '- {PAGENO} / {nb} -',
	      				'font-size' => 8,
	      				'font-style' => '',
	      				'font-family' => '',
	    			),
	    		'L' => 
	    			array (
	      				'content' => get_bloginfo('name'),
	      				'font-size' => 8,
	      				'font-style' => 'BI',
	      				'font-family' => 'DejaVuSansCondensed',
	    			),
	    		'line' => 1,
	  		),
	);
		

	$pdf_output = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
		<html xml:lang="en">
		
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset='.get_bloginfo( 'charset' ).'" />
			<title>' . get_bloginfo() . '</title>';
	
	$styles = apply_filters( 'wp-job-board-pro-create-resume-pdf-styles', array() );
	if ( !empty($styles) ) {
		foreach ($styles as $style) {
			$pdf_output .= '<link rel="stylesheet" href="'.esc_url($style).'">';
		}
	}
	$pdf_output .= '
		</head>
		<body xml:lang="en">
			<bookmark content="'.htmlspecialchars(get_bloginfo('name'), ENT_QUOTES).'" level="0" /><tocentry content="'.htmlspecialchars(get_bloginfo('name'), ENT_QUOTES).'" level="0" />
			<div id="content" class="widecolumn">';
			

				$pdf_output .= WP_Job_Board_Pro_Template_Loader::get_template_part( 'content-single-candidate-pdf' );
			// post finish

		$pdf_output .= '</div>';

		
	$pdf_output .= '
		</body>
		</html>';
?>
