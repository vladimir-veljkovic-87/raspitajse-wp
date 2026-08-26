<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Raspitajse_Commerce_Job_Package_Policy' ) ) {
    return;
}

if ( empty( $user_package_models ) ) {
    return;
}

$first_checked = false;
$has_available = false;
$labels         = array(
    Raspitajse_Commerce_Job_Package_Policy::STATUS_AVAILABLE   => __( 'Dostupan', 'superio' ),
    Raspitajse_Commerce_Job_Package_Policy::STATUS_EXHAUSTED   => __( 'Potrošen', 'superio' ),
    Raspitajse_Commerce_Job_Package_Policy::STATUS_EXPIRED     => __( 'Istekao', 'superio' ),
    Raspitajse_Commerce_Job_Package_Policy::STATUS_REVOKED     => __( 'Opozvan', 'superio' ),
    Raspitajse_Commerce_Job_Package_Policy::STATUS_UNAVAILABLE => __( 'Nedostupan', 'superio' ),
);
?>
<div class="widget widget-your-packages">
    <h2 class="widget-title"><?php esc_html_e( 'Vaši paketi', 'superio' ); ?></h2>
    <div class="inner-list">
        <ul class="user-job-packaged">
            <?php foreach ( $user_package_models as $package ) : ?>
                <?php
                $package_id  = absint( $package['id'] );
                $status      = (string) $package['status'];
                $usable      = ! empty( $package['usable'] )
                    && Raspitajse_Commerce_Job_Package_Policy::STATUS_AVAILABLE === $status;
                $title       = (string) $package['title'];
                $unlimited   = ! empty( $package['unlimited'] );
                $remaining   = null === $package['remaining']
                    ? null
                    : absint( $package['remaining'] );
                $job_limit   = absint( $package['limit'] );
                $job_duration = absint( $package['job_duration_days'] );
                $valid_until = absint( $package['valid_until'] );

                if ( $usable ) {
                    $has_available = true;
                }
                ?>
                <li class="package-<?php echo esc_attr( $status ); ?>">
                    <input
                        type="radio"
                        <?php checked( $usable && ! $first_checked ); ?>
                        <?php disabled( ! $usable ); ?>
                        name="wjbpwpl_listing_user_package"
                        value="<?php echo esc_attr( $package_id ); ?>"
                        id="user-package-<?php echo esc_attr( $package_id ); ?>"
                    />
                    <?php if ( $usable && ! $first_checked ) { $first_checked = true; } ?>

                    <label for="user-package-<?php echo esc_attr( $package_id ); ?>">
                        <?php echo esc_html( $title ); ?>
                    </label>

                    <div class="package-status">
                        <strong><?php esc_html_e( 'Status:', 'superio' ); ?></strong>
                        <?php echo esc_html( isset( $labels[ $status ] ) ? $labels[ $status ] : $labels[ Raspitajse_Commerce_Job_Package_Policy::STATUS_UNAVAILABLE ] ); ?>
                    </div>

                    <div class="package-quota">
                        <strong><?php esc_html_e( 'Preostala kvota:', 'superio' ); ?></strong>
                        <?php if ( $unlimited ) : ?>
                            <?php esc_html_e( 'Neograničeno', 'superio' ); ?>
                        <?php else : ?>
                            <?php echo esc_html( $remaining . ' / ' . $job_limit ); ?>
                        <?php endif; ?>
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
            <a href="#packages-accordion" class="btn btn-theme btn-sm disabled">
                <?php esc_html_e( 'Izaberite novi paket', 'superio' ); ?>
            </a>
        <?php endif; ?>
    </div>
</div>
