<?php
/**
 * Child override: Apus User Packages Elementor widget.
 *
 * @package Superio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Superio_Elementor_Jobs_User_Packages extends \Elementor\Widget_Base {

    /**
     * Return the Elementor widget identifier.
     *
     * @return string
     */
    public function get_name() {
        return 'apus_element_jobs_user_packages';
    }

    /**
     * Return the widget title.
     *
     * @return string
     */
    public function get_title() {
        return esc_html__( 'Apus User Packages', 'superio' );
    }

    /**
     * Return the widget categories.
     *
     * @return array
     */
    public function get_categories() {
        return array( 'superio-jobs-elements' );
    }

    /**
     * Register widget controls.
     */
    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => esc_html__( 'Content', 'superio' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'title',
            array(
                'label'   => esc_html__( 'Title', 'superio' ),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => '',
            )
        );

        $this->add_control(
            'el_class',
            array(
                'label'       => esc_html__( 'Extra class name', 'superio' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__(
                    'Add a class name and refer to it in your custom CSS.',
                    'superio'
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render the job-package table from canonical presentation models.
     *
     * @param array  $package_models Canonical employer package view models.
     * @param string $el_class       Optional Elementor presentation class.
     */
    protected function render_package_table( array $package_models, $el_class ) {
        $status_map = array(
            'available'   => array( 'active', __( 'Aktivan', 'superio' ) ),
            'exhausted'   => array( 'finish', __( 'Potrošen', 'superio' ) ),
            'expired'     => array( 'expired', __( 'Istekao', 'superio' ) ),
            'revoked'     => array( 'finish', __( 'Opozvan', 'superio' ) ),
            'unavailable' => array( 'finish', __( 'Nedostupan', 'superio' ) ),
        );
        ?>
        <div class="widget-user-packages <?php echo esc_attr( $el_class ); ?>">
            <div class="widget-content table-responsive">
                <table class="job-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( '#', 'superio' ); ?></th>
                            <th><?php esc_html_e( 'ID', 'superio' ); ?></th>
                            <th><?php esc_html_e( 'Package', 'superio' ); ?></th>
                            <th><?php esc_html_e( 'Package Type', 'superio' ); ?></th>
                            <th><?php esc_html_e( 'Package Info', 'superio' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'superio' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $package_models as $index => $package ) : ?>
                            <?php
                            $package_id   = isset( $package['id'] )
                                ? absint( $package['id'] )
                                : 0;
                            $title        = isset( $package['title'] )
                                ? (string) $package['title']
                                : '';
                            $status       = isset( $package['status'] )
                                ? (string) $package['status']
                                : 'unavailable';
                            $used         = isset( $package['used'] )
                                ? absint( $package['used'] )
                                : 0;
                            $limit        = isset( $package['limit'] )
                                ? absint( $package['limit'] )
                                : 0;
                            $unlimited    = ! empty( $package['unlimited'] );
                            $remaining    = isset( $package['remaining'] )
                                ? absint( $package['remaining'] )
                                : 0;
                            $job_duration = isset( $package['job_duration_days'] )
                                ? absint( $package['job_duration_days'] )
                                : 0;
                            $valid_until  = isset( $package['valid_until'] )
                                ? absint( $package['valid_until'] )
                                : 0;
                            $urgent       = ! empty( $package['urgent_jobs'] );
                            $featured     = ! empty( $package['featured_jobs'] );
                            $display      = isset( $status_map[ $status ] )
                                ? $status_map[ $status ]
                                : $status_map['unavailable'];
                            ?>
                            <tr>
                                <td><?php echo esc_html( (string) ( $index + 1 ) ); ?></td>
                                <td><?php echo esc_html( (string) $package_id ); ?></td>
                                <td class="name-package text-theme">
                                    <?php echo esc_html( $title ); ?>
                                </td>
                                <td><?php esc_html_e( 'Job Package', 'superio' ); ?></td>
                                <td>
                                    <div class="package-info-wrapper">
                                        <ul class="lists-info">
                                            <li>
                                                <span class="title-inner"><?php esc_html_e( 'Hitno:', 'superio' ); ?></span>
                                                <span class="value"><?php echo esc_html( $urgent ? __( 'Da', 'superio' ) : __( 'Ne', 'superio' ) ); ?></span>
                                            </li>
                                            <li>
                                                <span class="title-inner"><?php esc_html_e( 'Istaknuto:', 'superio' ); ?></span>
                                                <span class="value"><?php echo esc_html( $featured ? __( 'Da', 'superio' ) : __( 'Ne', 'superio' ) ); ?></span>
                                            </li>
                                            <li>
                                                <span class="title-inner"><?php esc_html_e( 'Objavljeno:', 'superio' ); ?></span>
                                                <span class="value"><?php echo esc_html( (string) $used ); ?></span>
                                            </li>
                                            <li>
                                                <span class="title-inner"><?php esc_html_e( 'Preostala kvota:', 'superio' ); ?></span>
                                                <span class="value">
                                                    <?php if ( $unlimited ) : ?>
                                                        <?php esc_html_e( 'Neograničeno', 'superio' ); ?>
                                                    <?php else : ?>
                                                        <?php echo esc_html( $remaining . ' / ' . $limit ); ?>
                                                    <?php endif; ?>
                                                </span>
                                            </li>
                                            <?php if ( $job_duration ) : ?>
                                                <li>
                                                    <span class="title-inner"><?php esc_html_e( 'Trajanje pojedinačnog oglasa:', 'superio' ); ?></span>
                                                    <span class="value">
                                                        <?php echo esc_html( (string) $job_duration ); ?>
                                                        <?php esc_html_e( 'dana', 'superio' ); ?>
                                                    </span>
                                                </li>
                                            <?php endif; ?>
                                            <?php if ( $valid_until ) : ?>
                                                <li>
                                                    <span class="title-inner"><?php esc_html_e( 'Paket važi do:', 'superio' ); ?></span>
                                                    <span class="value">
                                                        <?php echo esc_html( wp_date( get_option( 'date_format' ), $valid_until, wp_timezone() ) ); ?>
                                                    </span>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                                <td>
                                    <span class="action <?php echo esc_attr( $display[0] ); ?>">
                                        <?php echo esc_html( $display[1] ); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Render the dashboard widget.
     */
    protected function render() {
        $settings = $this->get_settings();
        $title    = isset( $settings['title'] ) ? $settings['title'] : '';
        $el_class = isset( $settings['el_class'] ) ? $settings['el_class'] : '';
        ?>
        <div class="box-dashboard-wrapper">
            <?php if ( ! empty( $title ) ) : ?>
                <h2 class="title"><?php echo esc_html( $title ); ?></h2>
            <?php endif; ?>

            <div class="inner-list">
                <?php if ( ! is_user_logged_in() ) : ?>
                    <div class="box-list-2">
                        <div class="text-warning">
                            <?php esc_html_e( 'Please login as "Employer" to see this page.', 'superio' ); ?>
                        </div>
                    </div>
                <?php else : ?>
                    <?php
                    $package_models = class_exists(
                        'Raspitajse_Commerce_Job_Package_Policy'
                    )
                        ? Raspitajse_Commerce_Job_Package_Policy::get_view_models_by_user(
                            get_current_user_id(),
                            true
                        )
                        : array();
                    ?>

                    <?php if ( ! empty( $package_models ) ) : ?>
                        <?php
                        $this->render_package_table(
                            $package_models,
                            $el_class
                        );
                        ?>
                    <?php else : ?>
                        <div class="not-found">
                            <?php esc_html_e( 'Nemate aktiviran paket.', 'superio' ); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php
            $products = WP_Job_Board_Pro_Wc_Paid_Listings_Submit_Form::get_products();
            ?>
            <form method="post" class="wjbpwpl-packages-form" action="">
                <?php wp_nonce_field( 'wp-job-board-pro-job-submit-package-nonce', 'security-job-submit-package' ); ?>
                <?php
                echo WP_Job_Board_Pro_Wc_Paid_Listings_Template_Loader::get_template_part(
                    'packages',
                    array( 'packages' => $products )
                );
                ?>
            </form>
        </div>
        <?php
    }
}

/**
 * Register the widget with supported Elementor versions.
 */
if ( defined( 'ELEMENTOR_VERSION' ) ) {
    if ( version_compare( ELEMENTOR_VERSION, '3.5.0', '<' ) ) {
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new Superio_Elementor_Jobs_User_Packages()
        );
    } else {
        \Elementor\Plugin::instance()->widgets_manager->register(
            new Superio_Elementor_Jobs_User_Packages()
        );
    }
}
