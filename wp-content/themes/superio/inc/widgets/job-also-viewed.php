<?php

class Superio_Widget_Job_Also_Viewed extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'apus_job_also_viewed',
            esc_html__('Job Detail:: People also viewed', 'superio'),
            array( 'description' => esc_html__( 'Show job people also viewed', 'superio' ), )
        );
        $this->widgetName = 'job_also_viewed';
    }

    public function widget( $args, $instance ) {
        get_template_part('widgets/job-also-viewed', '', array('args' => $args, 'instance' => $instance));
    }
    
    public function form( $instance ) {
        $defaults = array(
            'title' => 'People Also Viewed',
            'limit' => 4,
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        // Widget admin form
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_html_e( 'Title:', 'superio' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'limit' )); ?>"><?php esc_html_e( 'Limit:', 'superio' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'limit' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'limit' )); ?>" type="text" value="<?php echo esc_attr( $instance['limit'] ); ?>" />
        </p>
<?php
    }

    public function update( $new_instance, $old_instance ) {
        return $new_instance;
    }
}
register_widget('Superio_Widget_Job_Also_Viewed');
