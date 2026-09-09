<?php

class Superio_Widget_Job_Maps extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'apus_job_maps',
            esc_html__('Job Detail::  Maps', 'superio'),
            array( 'description' => esc_html__( 'Show job maps', 'superio' ), )
        );
        $this->widgetName = 'job_maps';
    }

    public function widget( $args, $instance ) {
        get_template_part('widgets/job-maps', '', array('args' => $args, 'instance' => $instance));
    }
    
    public function form( $instance ) {
        $defaults = array(
            'title' => 'Job Location',
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        // Widget admin form
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_html_e( 'Title:', 'superio' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
        </p>
<?php
    }

    public function update( $new_instance, $old_instance ) {
        return $new_instance;

    }
}
register_widget('Superio_Widget_Job_Maps');
