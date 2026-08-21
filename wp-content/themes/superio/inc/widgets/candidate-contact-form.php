<?php

class Superio_Widget_Candidate_Contact_Form extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'apus_candidate_contact_form',
            esc_html__('Candidate Detail:: Contact Form', 'superio'),
            array( 'description' => esc_html__( 'Show candidate contact form', 'superio' ), )
        );
        $this->widgetName = 'candidate_contact_form';
    }

    public function widget( $args, $instance ) {
        get_template_part('widgets/candidate-contact-form', '', array('args' => $args, 'instance' => $instance));
    }
    
    public function form( $instance ) {
        $defaults = array(
            'title' => 'Contact %1s',
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        // Widget admin form
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_html_e( 'Title:', 'superio' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
            <span class="desc"><?php esc_html_e('Enter %1s for candidate name', 'superio'); ?></span>
        </p>
<?php
    }

    public function update( $new_instance, $old_instance ) {
        return $new_instance;
    }
}
register_widget('Superio_Widget_Candidate_Contact_Form');
