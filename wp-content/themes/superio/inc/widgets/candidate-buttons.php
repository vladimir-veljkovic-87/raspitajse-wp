<?php

class Superio_Widget_Candidate_Buttons extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'apus_candidate_buttons',
            esc_html__('Candidate Detail:: Candidate Buttons', 'superio'),
            array( 'description' => esc_html__( 'Show candidate buttons', 'superio' ), )
        );
        $this->widgetName = 'candidate_buttons';
    }
    
    public function widget( $args, $instance ) {
        get_template_part('widgets/candidate-buttons', '', array('args' => $args, 'instance' => $instance));
    }

    public function form( $instance ) {
        $defaults = array(
            'title' => '',
            'show_shortlist_button' => 1,
            'show_cv_button' => 1,
            'show_invite_button' => 1,
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        // Widget admin form
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_html_e( 'Title:', 'superio' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['show_shortlist_button'], 1 ); ?> id="<?php echo esc_attr( 'show_shortlist_button' ); ?>" name="<?php echo esc_attr($this->get_field_name('show_shortlist_button')); ?>" value="1"/>
            <label for="<?php echo esc_attr($this->get_field_name('show_shortlist_button') ); ?>">
                <?php esc_html_e('Show Shortlist Button', 'superio'); ?>
            </label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['show_cv_button'], 1 ); ?> id="<?php echo esc_attr( 'show_cv_button' ); ?>" name="<?php echo esc_attr($this->get_field_name('show_cv_button')); ?>" value="1"/>
            <label for="<?php echo esc_attr($this->get_field_name('show_cv_button') ); ?>">
                <?php esc_html_e('Show Download CV Button', 'superio'); ?>
            </label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['show_invite_button'], 1 ); ?> id="<?php echo esc_attr( 'show_invite_button' ); ?>" name="<?php echo esc_attr($this->get_field_name('show_invite_button')); ?>" value="1"/>
            <label for="<?php echo esc_attr($this->get_field_name('show_invite_button') ); ?>">
                <?php esc_html_e('Show Invite Button', 'superio'); ?>
            </label>
        </p>
<?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['show_shortlist_button'] = (isset( $new_instance['show_shortlist_button'] ) ) ? strip_tags( $new_instance['show_shortlist_button'] ) : '';
        $instance['show_cv_button'] = (isset( $new_instance['show_cv_button'] ) ) ? strip_tags( $new_instance['show_cv_button'] ) : '';
        $instance['show_invite_button'] = (isset( $new_instance['show_invite_button'] ) ) ? strip_tags( $new_instance['show_invite_button'] ) : '';
        return $instance;
    }
}
register_widget('Superio_Widget_Candidate_Buttons');
