<?php

class Superio_Socials_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'apus_socials_widget',
            esc_html__('Apus Socials', 'superio'),
            array( 'description' => esc_html__( 'Socials for website.', 'superio' ), )
        );
        $this->widgetName = 'socials';
    }

    public function widget( $args, $instance ) {
        get_template_part('widgets/socials', '', array('args' => $args, 'instance' => $instance));
    }
    
    public function form( $instance ) {
        $list_socials = array(
            'facebook-f'      => 'Facebook',
            'twitter'       => 'Twitter',
            'youtube'       => 'Youtube',
            'pinterest-p'     => 'Pinterest',
            'google-plus-g'   => 'Google plus',
            'linkedin-in'      => 'LinkedIn',
            'instagram'      => 'Instagram'
        );
        $styles = array(
            esc_html__('Style 1', 'superio') => 'style1',
        );
        $defaults = array('title' => 'Find us on social networks', 'layout' => 'default', 'socials' => array(), 'styles' => 'style1');
        $instance = wp_parse_args((array) $instance, $defaults);
        
    ?>
    <div class="apus_socials">

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'superio'); ?></label>
            <input class="widefat" type="text" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" value="<?php echo esc_attr($instance['title']); ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'socials' )); ?>"><?php esc_html_e('Select socials:', 'superio'); ?></label>
            <br>
        <?php
            foreach ($list_socials as $key => $value):
                $checked = (isset($instance['socials'][$key]['status']) && ($instance['socials'][$key]['status'])) ? 1: 0;
                $link = (isset($instance['socials'][$key]['page_url'])) ? $instance['socials'][$key]['page_url']: '';
        ?>
                <p>
                <input class="checkbox" type="checkbox" <?php checked( $checked, 1 ); ?> id="<?php echo esc_attr( $key ); ?>"
                    name="<?php echo esc_attr($this->get_field_name('socials')); ?>[<?php echo esc_attr( $key ); ?>][status]" />
                    <label for="<?php echo esc_attr($this->get_field_name('socials') ); ?>[<?php echo esc_attr( $key ); ?>][status]">
                        <?php echo 'Show '.esc_html( $value ); ?>
                    </label>
                <input type="hidden" name="<?php echo esc_attr($this->get_field_name('socials')); ?>[<?php echo esc_attr( $key ); ?>][name]" value=<?php echo esc_attr( $value ); ?> />
                </p>
                <p id="<?php echo esc_attr($this->get_field_id($key)); ?>" class="text_url <?php echo esc_attr( $key ); ?>">
                    <label for="<?php echo esc_attr($this->get_field_name('socials')); ?>[<?php echo esc_attr( $key ); ?>][page_url]">
                        <?php echo esc_html( $value ).' Page URL: '; ?>
                    </label>
                    <input class="widefat" type="text"
                        id="<?php echo esc_attr($this->get_field_name('socials')); ?>[<?php echo esc_attr( $key ); ?>][page_url]"
                        name="<?php echo esc_attr($this->get_field_name('socials')); ?>[<?php echo esc_attr( $key ); ?>][page_url]"
                        value="<?php echo esc_url($link); ?>"
                    />
                </p>
            <?php endforeach; ?>
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
    </div>
<?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['socials'] = $new_instance['socials'];
        $instance['layout'] = ( ! empty( $new_instance['layout'] ) ) ? $new_instance['layout'] : 'default';
        $instance['styles'] = ( ! empty( $new_instance['styles'] ) ) ? $new_instance['styles'] : 'style1';
        return $instance;

    }
}
register_widget('Superio_Socials_Widget');
