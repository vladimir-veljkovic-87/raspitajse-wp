<?php

class Superio_Widget_Employer_Info extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'apus_employer_info',
            esc_html__('Employer Detail:: Information', 'superio'),
            array( 'description' => esc_html__( 'Show employer information', 'superio' ), )
        );
        $this->widgetName = 'employer_info';
    }

    public function widget( $args, $instance ) {
        get_template_part('widgets/employer-info', '', array('args' => $args, 'instance' => $instance));
    }
    
    public function form( $instance ) {
        $defaults = array(
            'title' => '',
            'styles' => '',
        );
        $styles = array(
            esc_html__('Default', 'superio') => '',
            esc_html__('Avatar', 'superio') => 'style1'
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        // Widget admin form
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_html_e( 'Title:', 'superio' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('styles')); ?>">
                <?php echo esc_html__('Style:', 'superio' ); ?>
            </label>
            <br>
            <select id="<?php echo esc_attr($this->get_field_id('styles')); ?>" name="<?php echo esc_attr($this->get_field_name('styles')); ?>">
                <?php foreach ( $styles as $key => $value ) { ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected($instance['styles'],$value); ?> ><?php echo esc_html( $key ); ?></option>
                <?php } ?>
            </select>
        </p>
<?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance['styles'] = ( ! empty( $new_instance['styles'] ) ) ? $new_instance['styles'] : 'style1';
        return $new_instance;

    }
}
register_widget('Superio_Widget_Employer_Info');
