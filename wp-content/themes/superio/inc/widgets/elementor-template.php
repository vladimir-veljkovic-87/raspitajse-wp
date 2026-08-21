<?php

class Superio_Widget_Elementor_Template extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'apus_elementor_template',
            esc_html__('Apus Show Elementor Template Widget', 'superio'),
            array( 'description' => esc_html__( 'Show Elementor Template', 'superio' ), )
        );
        $this->widgetName = 'elementor_template';
    }

    public function widget( $args, $instance ) {
        get_template_part('widgets/elementor-template', '', array('args' => $args, 'instance' => $instance));
    }
    
    public function form( $instance ) {
        $defaults = array(
            'item_template_id' => '',
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        // Widget admin form
        $ele_obj = \Elementor\Plugin::$instance;
        $templates = $ele_obj->templates_manager->get_source( 'local' )->get_items();
        
        $options = [ '' => esc_html__('Choose a template', 'superio' ) ];
        if ( !empty( $templates ) ) {
            foreach ( $templates as $template ) {
                $options[ $template['template_id'] ] = $template['title'] . ' (' . $template['type'] . ')';
            }
        }
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('item_template_id')); ?>">
                <?php echo esc_html__('Choose Template:', 'superio' ); ?>
            </label>
            <br>
            <select id="<?php echo esc_attr($this->get_field_id('item_template_id')); ?>" name="<?php echo esc_attr($this->get_field_name('item_template_id')); ?>">
                <?php foreach ($options as $key => $value) { ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected($instance['item_template_id'],$key); ?> ><?php echo esc_html( $value ); ?></option>
                <?php } ?>
            </select>
        </p>
<?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['item_template_id'] = ( ! empty( $new_instance['item_template_id'] ) ) ? strip_tags( $new_instance['item_template_id'] ) : '';
        return $instance;

    }
}
if ( did_action( 'elementor/loaded' ) ) {
    register_widget('Superio_Widget_Elementor_Template');
}