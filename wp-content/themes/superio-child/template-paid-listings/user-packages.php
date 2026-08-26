<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Raspitajse_Commerce_Job_Package_Policy' ) ) {
    return;
}

if ( empty( $user_packages ) ) {
    return;
}

$user_id       = get_current_user_id();
$prefix        = WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX;
$first_checked = false;
$has_available = false;
?>
<div class="widget widget-your-packages">
    <h2 class="widget-title"><?php esc_html_e( 'Vaši paketi', 'superio' ); ?></h2>
    <div class="inner-list">
        <ul class="user-job-packaged">
            <?php foreach ( $user_packages as $package ) : ?>
                <?php
                $status = Raspitajse_Commerce_Job_Package_Policy::get_status(
                    $user_id,
                    $package->ID
                );
                $usable = Raspitajse_Commerce_Job_Package_Policy::STATUS_AVAILABLE === $status;

                if ( $usable ) {
                    $has_available = true;
                }

                $package_count = absint(
                    get_post_meta(
                        $package->ID,
                        $prefix . 'package_count',
                        true
                    )
                );
                $job_limit = absint(
                    get_post_meta(
                        $package->ID,
                        $prefix . 'job_limit',
                        true
                    )
                );
                $job_duration = absint(
                    get_post_meta(
                        $package->ID,
                        $prefix . 'job_duration',
                        true
                    )
                );
                $valid_until = Raspitajse_Commerce_Job_Package_Policy::get_valid_until(
                    $package->ID
                );

                $labels = array(
                    Raspitajse_Commerce_Job_Package_Policy::STATUS_AVAILABLE   => __( 'Dostupan', 'superio' ),
                    Raspitajse_Commerce_Job_Package_Policy::STATUS_EXHAUSTED   => __( 'Potrošen', 'superio' ),
                    Raspitajse_Commerce_Job_Package_Policy::STATUS_EXPIRED     => __( 'Istekao', 'superio' ),
                    Raspitajse_Commerce_Job_Package_Policy::STATUS_REVOKED     => __( 'Opozvan', 'superio' ),
                    Raspitajse_Commerce_Job_Package_Policy::STATUS_UNAVAILABLE => __( 'Nedostupan', 'superio' ),
                );
                ?>
                <li class="package-<?php echo esc_attr( $status ); ?>">
                    <input
                        type="radio"
                        <?php checked( $usable && ! $first_checked ); ?>
                        <?php disabled( ! $usable ); ?>
                        name="wjbpwpl_listing_user_package"
                        value="<?php echo esc_attr( $package->ID ); ?>"
                        id="user-package-<?php echo esc_attr( $package->ID ); ?>"
                    />
                    <?php if ( $usable && ! $first_checked ) { $first_checked = true; } ?>

                    <label for="user-package-<?php echo esc_attr( $package->ID ); ?>">
                        <?php echo esc_html( $package->post_title ); ?>
                    </label>

                    <div class="package-status">
                        <strong><?php esc_html_e( 'Status:', 'superio' ); ?></strong>
                        <?php echo esc_html( isset( $labels[ $status ] ) ? $labels[ $status ] : $labels[ Raspitajse_Commerce_Job_Package_Policy::STATUS_UNAVAILABLE ] ); ?>
                    </div>

                    <div class="package-quota">
                        <strong><?php esc_html_e( 'Iskorišćena kvota:', 'superio' ); ?></strong>
                        <?php echo esc_html( $job_limit ? $package_count . ' / ' . $job_limit : (string) $package_count ); ?>
                    </div>

                    <?php if ( $job_duration ) : ?>
                        <div class="package-job-duration">
                            <strong><?php esc_html_e( 'Trajanje pojedinačnog oglasa:', 'superio' ); ?></strong>
                            <?php echo esc_html( $job_duration ); ?> <?php esc_html_e( 'dana', 'superio' ); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( $valid_until ) : ?>
                        <div class="package-valid-until">
                            <strong><?php esc_html_e( 'Paket važi do:', 'superio' ); ?></strong>
                            <?php echo esc_html( wp_date( get_option( 'date_format' ), $valid_until, wp_timezone() ) ); ?>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if ( $has_available ) : ?>
            <button class="btn btn-theme btn-sm" type="submit">
                <?php esc_html_e( 'Nastavi da koristiš kvotu', 'superio' ); ?>
            </button>
        <?php else : ?>
            <a href="#available-packages" class="btn btn-theme btn-sm disabled">
                <?php esc_html_e( 'Izaberite novi paket', 'superio' ); ?>
            </a>
        <?php endif; ?>
    </div>
</div>
