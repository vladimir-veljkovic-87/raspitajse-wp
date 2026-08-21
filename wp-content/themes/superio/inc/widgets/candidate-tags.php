<?php

class Superio_Widget_Candidate_Tags extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'apus_candidate_tags',
            esc_html__('Candidate Detail:: Candidate Tags', 'superio'),
            array( 'description' => esc_html__( 'Show candidate Tags', 'superio' ), )
        );
        $this->widgetName = 'candidate_tags';
    }

    public function widget( $args, $instance ) {
        get_template_part('widgets/candidate-tags', '', array('args' => $args, 'instance' => $instance));
    }
    
    public function form( $instance ) {
        $defaults = array(
            'title' => '',
            'style' => '',
        );
        $options = array(
            '' => esc_html__('Default', 'superio'),
            'style1' => esc_html__('Style 1', 'superio'),
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        // Widget admin form
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_html_e( 'Title:', 'superio' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('style')); ?>">
                <?php echo esc_html__('Style:', 'superio' ); ?>
            </label>
            <br>
            <select id="<?php echo esc_attr($this->get_field_id('style')); ?>" name="<?php echo esc_attr($this->get_field_name('style')); ?>">
                <?php foreach ($options as $key => $value) { ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected($instance['style'],$key); ?> ><?php echo esc_html( $value ); ?></option>
                <?php } ?>
            </select>
        </p>
    <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance['style'] = ( ! empty( $new_instance['style'] ) ) ? strip_tags( $new_instance['style'] ) : '';
        return $new_instance;
    }
}
register_widget('Superio_Widget_Candidate_Tags');
