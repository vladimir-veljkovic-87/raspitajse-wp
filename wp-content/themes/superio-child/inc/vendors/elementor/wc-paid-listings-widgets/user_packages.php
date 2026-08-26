<?php
/**
 * Child override: Apus User Packages Elementor widget
 * Path:
 * wp-content/themes/superio-child/inc/vendors/elementor/wc-paid-listings-widgets/user_packages.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Superio_Elementor_Jobs_User_Packages extends \Elementor\Widget_Base {

    public function get_name() {
        return 'apus_element_jobs_user_packages'; // MUST be identical
    }

    public function get_title() {
        return esc_html__( 'Apus User Packages', 'superio' );
    }

    public function get_categories() {
        return [ 'superio-jobs-elements' ];
    }

    protected function register_controls() {

        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Content', 'superio' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title',
            [
                'label'   => esc_html__( 'Title', 'superio' ),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => '',
            ]
        );

        $this->add_control(
            'el_class',
            [
                'label'       => esc_html__( 'Extra class name', 'superio' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Add a class name and refer to it in your custom CSS.', 'superio' ),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {

        $settings = $this->get_settings();
        $title    = isset($settings['title']) ? $settings['title'] : '';
        $el_class = isset($settings['el_class']) ? $settings['el_class'] : '';

        ?>
        <div class="box-dashboard-wrapper">

            <?php if ( ! empty($title) ) : ?>
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
                    $user_id  = get_current_user_id();
                    $packages = class_exists('Raspitajse_Commerce_Job_Package_Policy')
                        ? Raspitajse_Commerce_Job_Package_Policy::get_packages_by_user($user_id, true)
                        : WP_Job_Board_Pro_Wc_Paid_Listings_Mixes::get_packages_by_user($user_id, false, 'job_package');

                    if ( ! empty($packages) ) :
                    ?>
                        <div class="widget-user-packages <?php echo esc_attr($el_class); ?>">
                            <div class="widget-content table-responsive">
                                <table class="job-table">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e('#', 'superio'); ?></th>
                                            <th><?php esc_html_e('ID', 'superio'); ?></th>
                                            <th><?php esc_html_e('Package', 'superio'); ?></th>
                                            <th><?php esc_html_e('Package Type', 'superio'); ?></th>
                                            <th><?php esc_html_e('Package Info', 'superio'); ?></th>
                                            <th><?php esc_html_e('Status', 'superio'); ?></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                    <?php
                                    $i = 1;
                                    foreach ( $packages as $package ) :

                                        $prefix            = defined('WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX') ? WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX : '_wjbpwpl_';
                                        $package_type      = get_post_meta($package->ID, $prefix . 'package_type', true);
                                        $package_types     = WP_Job_Board_Pro_Wc_Paid_Listings_Post_Type_Packages::package_types();
                                        $subscription_type = get_post_meta($package->ID, $prefix . 'subscription_type', true);
                                        ?>
                                        <tr>
                                            <td><?php echo (int) $i; ?></td>
                                            <td><?php echo (int) $package->ID; ?></td>
                                            <td class="name-package text-theme"><?php echo esc_html( $package->post_title ); ?></td>

                                            <td>
                                                <?php
                                                if ( ! empty($package_types[$package_type]) ) {
                                                    echo esc_html($package_types[$package_type]);
                                                } else {
                                                    echo '--';
                                                }
                                                ?>
                                            </td>

                                            <td>
                                                <div class="package-info-wrapper">
                                                    <?php
                                                    switch ($package_type) {

                                                        case 'cv_package':
                                                            $candidate_ids = get_post_meta($package->ID, $prefix . 'cv_viewed_count', true);
                                                            $cv_viewed_count = !empty($candidate_ids) ? count(explode(',', $candidate_ids)) : 0;

                                                            $cv_package_expiry_time = get_post_meta($package->ID, $prefix . 'cv_package_expiry_time', true);
                                                            $cv_number_of_cv        = get_post_meta($package->ID, $prefix . 'cv_number_of_cv', true);
                                                            ?>
                                                            <ul class="lists-info">
                                                                <li><span class="title-inner"><?php esc_html_e('CV Count:', 'superio'); ?></span> <span class="value"><?php echo (int)$cv_viewed_count; ?></span></li>
                                                                <?php if ( $subscription_type !== 'listing' ) : ?>
                                                                    <li><span class="title-inner"><?php esc_html_e('Expiry Time:', 'superio'); ?></span> <span class="value"><?php echo sprintf(_n('%d Day', '%d Days', (int)$cv_package_expiry_time, 'superio'), (int)$cv_package_expiry_time); ?></span></li>
                                                                <?php endif; ?>
                                                                <li><span class="title-inner"><?php esc_html_e('CV Limit:', 'superio'); ?></span> <span class="value"><?php echo (int)$cv_number_of_cv; ?></span></li>
                                                            </ul>
                                                            <?php
                                                            break;

                                                        case 'contact_package':
                                                            $candidate_ids = get_post_meta($package->ID, $prefix . 'contact_viewed_count', true);
                                                            $contact_viewed_count = !empty($candidate_ids) ? count(explode(',', $candidate_ids)) : 0;

                                                            $contact_package_expiry_time = get_post_meta($package->ID, $prefix . 'contact_package_expiry_time', true);
                                                            $contact_number_of_cv        = get_post_meta($package->ID, $prefix . 'contact_number_of_cv', true);
                                                            ?>
                                                            <ul class="lists-info">
                                                                <li><span class="title-inner"><?php esc_html_e('CV Count:', 'superio'); ?></span> <span class="value"><?php echo (int)$contact_viewed_count; ?></span></li>
                                                                <?php if ( $subscription_type !== 'listing' ) : ?>
                                                                    <li><span class="title-inner"><?php esc_html_e('Expiry Time:', 'superio'); ?></span> <span class="value"><?php echo sprintf(_n('%d Day', '%d Days', (int)$contact_package_expiry_time, 'superio'), (int)$contact_package_expiry_time); ?></span></li>
                                                                <?php endif; ?>
                                                                <li><span class="title-inner"><?php esc_html_e('CV Limit:', 'superio'); ?></span> <span class="value"><?php echo (int)$contact_number_of_cv; ?></span></li>
                                                            </ul>
                                                            <?php
                                                            break;

                                                        case 'candidate_package':
                                                            $app_ids = get_post_meta($package->ID, $prefix . 'candidate_applied_count', true);
                                                            $candidate_applied_count = !empty($app_ids) ? count(explode(',', $app_ids)) : 0;

                                                            $candidate_package_expiry_time      = get_post_meta($package->ID, $prefix . 'candidate_package_expiry_time', true);
                                                            $candidate_number_of_applications   = get_post_meta($package->ID, $prefix . 'candidate_number_of_applications', true);
                                                            ?>
                                                            <ul class="lists-info">
                                                                <li><span class="title-inner"><?php esc_html_e('Applications Count:', 'superio'); ?></span> <span class="value"><?php echo (int)$candidate_applied_count; ?></span></li>
                                                                <?php if ( $subscription_type !== 'listing' ) : ?>
                                                                    <li><span class="title-inner"><?php esc_html_e('Expiry Time:', 'superio'); ?></span> <span class="value"><?php echo sprintf(_n('%d Day', '%d Days', (int)$candidate_package_expiry_time, 'superio'), (int)$candidate_package_expiry_time); ?></span></li>
                                                                <?php endif; ?>
                                                                <li><span class="title-inner"><?php esc_html_e('Applications Limit:', 'superio'); ?></span> <span class="value"><?php echo (int)$candidate_number_of_applications; ?></span></li>
                                                            </ul>
                                                            <?php
                                                            break;

                                                        case 'resume_package':
                                                            $urgent_resumes   = get_post_meta($package->ID, $prefix . 'urgent_resumes', true);
                                                            $featured_resumes = get_post_meta($package->ID, $prefix . 'feature_resumes', true);
                                                            $resumes_duration = get_post_meta($package->ID, $prefix . 'resumes_duration', true);
                                                            ?>
                                                            <ul class="lists-info">
                                                                <li><span class="title-inner"><?php esc_html_e('Urgent:', 'superio'); ?></span> <span class="value"><?php echo ($urgent_resumes === 'on') ? esc_html__('Yes','superio') : esc_html__('No','superio'); ?></span></li>
                                                                <li><span class="title-inner"><?php esc_html_e('Featured:', 'superio'); ?></span> <span class="value"><?php echo ($featured_resumes === 'on') ? esc_html__('Yes','superio') : esc_html__('No','superio'); ?></span></li>
                                                                <?php if ( $subscription_type !== 'listing' ) : ?>
                                                                    <li><span class="title-inner"><?php esc_html_e('Resume Duration:', 'superio'); ?></span> <span class="value"><?php echo (int)$resumes_duration; ?></span></li>
                                                                <?php endif; ?>
                                                            </ul>
                                                            <?php
                                                            break;

                                                        case 'job_package':
                                                        default:
                                                            $urgent_jobs    = get_post_meta($package->ID, $prefix . 'urgent_jobs', true);
                                                            $feature_jobs   = get_post_meta($package->ID, $prefix . 'feature_jobs', true);
                                                            $package_count  = get_post_meta($package->ID, $prefix . 'package_count', true);
                                                            $job_limit      = get_post_meta($package->ID, $prefix . 'job_limit', true);
                                                            $job_duration   = get_post_meta($package->ID, $prefix . 'job_duration', true);
                                                            $valid_until   = class_exists('Raspitajse_Commerce_Job_Package_Policy')
                                                                ? Raspitajse_Commerce_Job_Package_Policy::get_valid_until($package->ID)
                                                                : 0;
                                                            ?>
                                                            <ul class="lists-info">
                                                                <li><span class="title-inner"><?php esc_html_e('Hitno:', 'superio'); ?></span> <span class="value"><?php echo ($urgent_jobs === 'on') ? esc_html__('Da','superio') : esc_html__('Ne','superio'); ?></span></li>
                                                                <li><span class="title-inner"><?php esc_html_e('Istaknuto:', 'superio'); ?></span> <span class="value"><?php echo ($feature_jobs === 'on') ? esc_html__('Da','superio') : esc_html__('Ne','superio'); ?></span></li>
                                                                <li><span class="title-inner"><?php esc_html_e('Objavljeno:', 'superio'); ?></span> <span class="value"><?php echo (int)$package_count; ?></span></li>
                                                                <li><span class="title-inner"><?php esc_html_e('Limit oglasa:', 'superio'); ?></span> <span class="value"><?php echo (int)$job_limit; ?></span></li>
                                                                <?php if ( $subscription_type !== 'listing' ) : ?>
                                                                    <li><span class="title-inner"><?php esc_html_e('Trajanje oglasa (dani):', 'superio'); ?></span> <span class="value"><?php echo (int)$job_duration; ?></span></li>
                                                                <?php endif; ?>
                                                                <?php if ( $valid_until ) : ?>
                                                                    <li><span class="title-inner"><?php esc_html_e('Paket važi do:', 'superio'); ?></span> <span class="value"><?php echo esc_html(wp_date(get_option('date_format'), $valid_until, wp_timezone())); ?></span></li>
                                                                <?php endif; ?>
                                                            </ul>
                                                            <?php
                                                            break;
                                                    }
                                                    ?>
                                                </div>
                                            </td>

                                            <td>
                                                <?php
                                                /**
                                                 * ==============================
                                                 * STATUS: Istekao / Potrošen / Aktivan
                                                 * ==============================
                                                 */

                                                $status = class_exists('Raspitajse_Commerce_Job_Package_Policy')
                                                    ? Raspitajse_Commerce_Job_Package_Policy::get_status($user_id, $package->ID)
                                                    : 'unavailable';

                                                $status_map = array(
                                                    'available'   => array('active', __('Aktivan', 'superio')),
                                                    'exhausted'   => array('finish', __('Potrošen', 'superio')),
                                                    'expired'     => array('expired', __('Istekao', 'superio')),
                                                    'revoked'     => array('finish', __('Opozvan', 'superio')),
                                                    'unavailable' => array('finish', __('Nedostupan', 'superio')),
                                                );

                                                $display = isset($status_map[$status])
                                                    ? $status_map[$status]
                                                    : $status_map['unavailable'];

                                                echo '<span class="action ' . esc_attr($display[0]) . '">' . esc_html($display[1]) . '</span>';
                                                ?>
                                            </td>

                                        </tr>
                                        <?php
                                        $i++;
                                    endforeach;
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
			

                    <?php else : ?>
                        <div class="not-found"><?php esc_html_e('Nemate aktiviran paket.', 'superio'); ?></div>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
			
			<?php
			// Prikaži "Dostupni paketi" ispod tabele (sa formom)
				// Dostupni paketi (sa formom)
				$products = WP_Job_Board_Pro_Wc_Paid_Listings_Submit_Form::get_products();
			?>

			<form method="post" class="wjbpwpl-packages-form" action="">
				<?php wp_nonce_field('wp-job-board-pro-job-submit-package-nonce', 'security-job-submit-package'); ?>

				<?php
				echo WP_Job_Board_Pro_Wc_Paid_Listings_Template_Loader::get_template_part(
					'packages',
					array('packages' => $products)
				);
				?>
			</form>
        </div>
        <?php
    }
}

/**
 * Register widget
 */
if ( defined('ELEMENTOR_VERSION') ) {
    if ( version_compare(ELEMENTOR_VERSION, '3.5.0', '<') ) {
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Superio_Elementor_Jobs_User_Packages );
    } else {
        \Elementor\Plugin::instance()->widgets_manager->register( new Superio_Elementor_Jobs_User_Packages );
    }
}