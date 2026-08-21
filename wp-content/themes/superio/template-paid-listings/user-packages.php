<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( $user_packages ) :
    $current_user_id = get_current_user_id();

    error_log('=== USER PACKAGES DEBUG START ===');
    error_log('Current user ID: ' . $current_user_id);
    error_log('User packages RAW: ' . print_r($user_packages, true));
?>

<div class="widget widget-your-packages">
    <h2 class="widget-title"><?php esc_html_e( 'Vaši paketi', 'superio' ); ?></h2>

    <div class="inner-list">
        <ul class="user-job-packaged">

            <?php
            $prefix  = WP_JOB_BOARD_PRO_WC_PAID_LISTINGS_PREFIX;
            $checked = 1;
            $has_active_package = false;

            foreach ( $user_packages as $package ) :

                $package_id = $package->ID;

                $package_count = get_post_meta( $package_id, $prefix . 'package_count', true );
                $job_limit     = get_post_meta( $package_id, $prefix . 'job_limit', true );
                $job_duration  = get_post_meta( $package_id, $prefix . 'job_duration', true );

                error_log('--- PACKAGE LOOP ---');
                error_log('Package ID: ' . $package_id);
                error_log('Package title: ' . $package->post_title);

                // EXPIRATION META
                $expires_at = get_user_meta(
                    $current_user_id,
                    '_wjbp_package_expiration_' . $package_id,
                    true
                );

                error_log('Expiration meta key: _wjbp_package_expiration_' . $package_id);
                error_log('Expiration value (raw): ' . print_r($expires_at, true));

                /**
                 * Fallback: izračunaj expiration ako ne postoji
                 */
                if ( empty($expires_at) && ! empty($job_duration) ) {
                    $assigned_at = strtotime( $package->post_date );

                    if ( $assigned_at ) {
                        $expires_at = date(
                            'Y-m-d H:i:s',
                            strtotime("+{$job_duration} days", $assigned_at)
                        );

                        update_user_meta(
                            $current_user_id,
                            '_wjbp_package_expiration_' . $package_id,
                            $expires_at
                        );

                        error_log('Expiration CALCULATED & SAVED: ' . $expires_at);
                    }
                }

                // PROVERA ISTEKA
                $is_expired = false;
                if ( ! empty($expires_at) && strtotime($expires_at) < time() ) {
                    $is_expired = true;
                }

                if ( ! $is_expired ) {
                    $has_active_package = true;
                }
            ?>

            <li class="<?php echo $is_expired ? 'package-expired' : ''; ?>">

                <input
                    type="radio"
                    <?php checked( $checked, 1 ); ?>
                    <?php disabled( $is_expired ); ?>
                    name="wjbpwpl_listing_user_package"
                    value="<?php echo esc_attr( $package_id ); ?>"
                    id="user-package-<?php echo esc_attr( $package_id ); ?>"
                />

                <label for="user-package-<?php echo esc_attr( $package_id ); ?>">
                    <?php echo esc_html( $package->post_title ); ?>
                </label>
                <br/>

                <?php
                if ( $job_limit ) {
                    printf(
                        _n(
                            '%s oglas je objavljen od %d',
                            '%s oglasa je objavljeno od %d',
                            $package_count,
                            'superio'
                        ),
                        $package_count,
                        $job_limit
                    );
                } else {
                    printf(
                        _n(
                            '%s oglas je objavljen',
                            '%s oglasa je objavljeno',
                            $package_count,
                            'superio'
                        ),
                        $package_count
                    );
                }

                if ( $job_duration ) {
                    printf(
                        ', ' . __( 'oglas traje %s dana', 'superio' ),
                        $job_duration
                    );
                }
                ?>

                <?php if ( ! empty($expires_at) ) : ?>
                    <?php if ( $is_expired ) : ?>
                        <div class="package-expiration expired" style="margin-top:6px;color:#d63638;font-weight:600;">
                            <?php esc_html_e( 'Paket vam je istekao', 'superio' ); ?>
                        </div>
                    <?php else : ?>
                        <div class="package-expiration" style="margin-top:6px;">
                            <strong><?php esc_html_e( 'Paket važi do:', 'superio' ); ?></strong>
                            <?php echo esc_html( date_i18n( 'd.m.Y', strtotime($expires_at) ) ); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            </li>

            <?php
                $checked = 0;
            endforeach;
            ?>

        </ul>

        <?php if ( $has_active_package ) : ?>
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

<?php endif; ?>
