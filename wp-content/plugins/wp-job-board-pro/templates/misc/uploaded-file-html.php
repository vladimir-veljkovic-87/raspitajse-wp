<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$input_name = isset($input_name) ? $input_name : '';
?>
<div class="wp-job-board-pro-uploaded-file">
	<?php
	
	$mime_type = $file_url = '';
	if ( $value ) {
		if ( is_numeric( $value ) ) {
			$file_post = get_post($value);
			$mime_type = $file_post->post_mime_type;
			switch ($file_post->post_mime_type) {
				case 'image/jpeg':
				case 'image/png':
				case 'image/gif':
					$image_src = wp_get_attachment_image_src( absint( $value ) );
					$file_url = $image_src ? $image_src[0] : '';
					break;
				default:
					$file_url = get_attached_file($value);
					break;
			}
			
		} else {
			$file_url = $value;
			$value = WP_Job_Board_Pro_Image::get_attachment_id_from_url($value);

			$mime_type = get_post_mime_type($value);
		}
	}
	if ( $mime_type ) {
		switch ($mime_type) {
			case 'image/jpeg':
			case 'image/png':
			case 'image/gif':
				?>
				<span class="wp-job-board-pro-uploaded-file-preview"><img src="<?php echo $file_url; ?>" /> <a class="wp-job-board-pro-remove-uploaded-file" href="#">[<?php _e( 'remove', 'wp-job-board-pro' ); ?>]</a></span>
				<?php
				break;
			default:
				?>
				<div class="wp-job-board-pro-uploaded-file-name flex-middle">
					<?php
						$file_info = pathinfo($file_url);
					?>
					<a href="javascript:void(0);" class="candidate-detail-cv">
		                <span class="icon_type">
		                    <i class="flaticon-file"></i>
		                </span>
		                <?php if ( !empty($file_info['filename']) ) { ?>
		                    <div class="filename"><?php echo esc_html($file_info['filename']); ?></div>
		                <?php } ?>
		                <?php if ( !empty($file_info['extension']) ) { ?>
		                    <div class="extension"><?php echo esc_html($file_info['extension']); ?></div>
		                <?php } ?>
		            </a>

					<a class="wp-job-board-pro-remove-uploaded-file" href="#">[<?php _e( 'remove', 'wp-job-board-pro' ); ?>]</a>
				</div>
				<?php
				break;
		}
	} else {
		?>
		<span class="wp-job-board-pro-uploaded-file-preview"><img src="<?php echo $file_url; ?>" /> <a class="wp-job-board-pro-remove-uploaded-file" href="#">[<?php _e( 'remove', 'wp-job-board-pro' ); ?>]</a></span>

		<div class="wp-job-board-pro-uploaded-file-name flex-middle">
			<a href="javascript:void(0);" class="candidate-detail-cv"><?php echo esc_html( basename( $file_url ) ); ?></a>
			<a class="wp-job-board-pro-remove-uploaded-file" href="#">[<?php _e( 'remove', 'wp-job-board-pro' ); ?>]</a>
		</div>

		<?php
	}
	
	?>

	<input type="hidden" class="input-text" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
	
</div>