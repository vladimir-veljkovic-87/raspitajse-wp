<?php
if ( !function_exists ('superio_custom_styles') ) {
	function superio_custom_styles() {
		global $post;	
		
		ob_start();	
		?>
		<?php if ( superio_get_config('main_color') != "" ) {
			$main_color = superio_get_config('main_color');
		} else {
			$main_color = '#1967D2';
		}


		if ( superio_get_config('main_hover_color') != "" ) {
			$main_hover_color = superio_get_config('main_hover_color');
		} else {
			$main_hover_color = '#1451a4';
		}
		
		$main_color_rgb = superio_hex2rgb($main_color);


		// font
		$main_font = superio_get_config('main_font');
		$main_font = !empty($main_font['font-family']) ? $main_font['font-family'] : 'Jost';

		$main_font_arr = explode(',', $main_font);
		if ( count($main_font_arr) == 1 ) {
			$main_font = "'".$main_font."'";
		}

		$heading_font = superio_get_config('heading_font');
		$heading_font = !empty($heading_font['font-family']) ? $heading_font['font-family'] : 'Jost';

		$heading_font_arr = explode(',', $heading_font);
		if ( count($heading_font_arr) == 1 ) {
			$heading_font = "'".$heading_font."'";
		}
		
		?>
		:root {
		  --superio-theme-color: <?php echo esc_html($main_color); ?>;
		  --superio-theme-hover-color: <?php echo esc_html($main_hover_color); ?>;
		  --superio-theme-color-001: <?php echo esc_html(superio_generate_rgba($main_color_rgb, 0.01)); ?>
		  --superio-theme-color-01: <?php echo esc_html(superio_generate_rgba($main_color_rgb, 0.1)); ?>
		  --superio-theme-color-015: <?php echo esc_html(superio_generate_rgba($main_color_rgb, 0.15)); ?>
		  --superio-theme-color-007: <?php echo esc_html(superio_generate_rgba($main_color_rgb, 0.07)); ?>
		  --superio-theme-color-008: <?php echo esc_html(superio_generate_rgba($main_color_rgb, 0.08)); ?>
		  --superio-theme-color-08: <?php echo esc_html(superio_generate_rgba($main_color_rgb, 0.8)); ?>
		  --superio-theme-color-005: <?php echo esc_html(superio_generate_rgba($main_color_rgb, 0.05)); ?>

		  --superio-main-font: <?php echo trim($main_font); ?>;
		  --superio-heading-font: <?php echo trim($heading_font); ?>;
		}
			
		<?php if (  superio_get_config('header_mobile_color') != "" ) : ?>
			#apus-header-mobile {
				background-color: <?php echo esc_html( superio_get_config('header_mobile_color') ); ?>;
			}
		<?php endif; ?>
		

		<?php if (  superio_get_config('job_featured_background_color') != "" ) : ?>
			.job-grid.is-featured, .job-list.is-featured {
				background-color: <?php echo esc_html( superio_get_config('job_featured_background_color') ); ?>;
			}
		<?php endif; ?>

		<?php if (  superio_get_config('job_urgent_background_color') != "" ) : ?>
			.job-grid.is-urgent, .job-list.is-urgent {
				background-color: <?php echo esc_html( superio_get_config('job_urgent_background_color') ); ?>;
			}
		<?php endif; ?>

		<?php if (  superio_get_config('candidate_featured_background_color') != "" ) : ?>
			.candidate-card.is-featured .candidate-grid, .candidate-card.is-featured .candidate-list {
				background-color: <?php echo esc_html( superio_get_config('candidate_featured_background_color') ); ?>;
			}
		<?php endif; ?>

		<?php if (  superio_get_config('candidate_urgent_background_color') != "" ) : ?>
			.candidate-card.is-urgent .candidate-grid, .candidate-card.is-urgent .candidate-list {
				background-color: <?php echo esc_html( superio_get_config('candidate_urgent_background_color') ); ?>;
			}
		<?php endif; ?>

		<?php if (  superio_get_config('employer_featured_background_color') != "" ) : ?>
			.employer-card.is-featured .employer-grid, .employer-card.is-featured .employer-list {
				background-color: <?php echo esc_html( superio_get_config('employer_featured_background_color') ); ?>;
			}
		<?php endif; ?>

	<?php
		$content = ob_get_clean();
		$content = str_replace(array("\r\n", "\r"), "\n", $content);
		$lines = explode("\n", $content);
		$new_lines = array();
		foreach ($lines as $i => $line) {
			if (!empty($line)) {
				$new_lines[] = trim($line);
			}
		}
		
		return implode($new_lines);
	}
}