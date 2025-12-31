<?php

function superio_child_enqueue_styles() {
	wp_enqueue_style( 'superio-child-style', get_stylesheet_uri() );
}

add_action( 'wp_enqueue_scripts', 'superio_child_enqueue_styles', 200 );

// Adding User Role Class to Body
function add_custom_body_class($classes) {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = $user->roles;
        if (in_array('administrator', $roles)) {
            $classes[] = 'role-administrator';
        }
        if (in_array('wp_job_board_pro_employer', $roles)) {
            $classes[] = 'role-employer';
        }
        if (in_array('wp_job_board_pro_candidate', $roles)) {
            $classes[] = 'role-candidate';
        }
    }
    return $classes;
}
add_filter('body_class', 'add_custom_body_class');

function custom_enqueue_child_styles() {
    // Dynamically generate version using current time
    wp_enqueue_style('superio-child-style', get_stylesheet_directory_uri() . '/style.css', array(), time(), 'all');
}
add_action('wp_enqueue_scripts', 'custom_enqueue_child_styles');


// Log WP Mail Calls for Debugging
// add_action('wp_mail', function ($atts) {
//     error_log(print_r($atts, true));
// });

// Get User Subscription Status [NOT IN USE]
function get_user_subscription_status() {
    if (!function_exists('wcs_get_users_subscriptions')) {
        error_log("WooCommerce Subscriptions plugin is not active.");
        return false;
    }

    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $subscriptions = wcs_get_users_subscriptions($user_id);

        foreach ($subscriptions as $subscription) {
            if ($subscription->has_status('active')) {
                $status = $subscription->get_status();
                error_log("User ID $user_id has active subscription with status: $status");
                return $status;
            }
        }
    }
    error_log("User ID " . (is_user_logged_in() ? get_current_user_id() : 'not logged in') . " has no active subscription.");
    return false;
}

// Get User's Purchased Products and Add as Body Classes [IN USE]
function get_user_purchased_products() {
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $customer_orders = wc_get_orders(array(
            'customer_id' => $user_id,
            'status' => 'completed',
        ));

        $purchased_products = array();

        foreach ($customer_orders as $order) {
            foreach ($order->get_items() as $item) {
                $product_id = $item->get_product_id();
                $product = wc_get_product($product_id);
                $purchased_products[] = sanitize_title($product->get_name());
            }
        }

        if (!empty($purchased_products)) {
            error_log("User ID $user_id has purchased: " . implode(", ", $purchased_products));
            return 'purchased-' . implode(' purchased-', $purchased_products);
        } else {
            error_log("User ID $user_id has not purchased any products.");
            return 'purchased-nothing';
        }
    }
    return '';
}

// Add the purchased products class to the body
function add_purchased_products_body_class($classes) {
    $purchased_class = get_user_purchased_products();
    if ($purchased_class) {
        $classes[] = $purchased_class;
    }
    return $classes;
}
add_filter('body_class', 'add_purchased_products_body_class');

// Check what package user have
function user_has_package($package_name) {
    // Get the user's purchased products as body classes
    $purchased_products_classes = get_user_purchased_products();

    // Sanitize the package name to match the class format
    $sanitized_package_name = 'purchased-' . sanitize_title($package_name);

    // Check if the sanitized package name exists in the purchased products class string
    if (strpos($purchased_products_classes, $sanitized_package_name) !== false) {
        return true;
    }

    return false;
}

add_action('admin_menu', 'my_custom_page');

// test POST
function my_custom_page() {
    add_menu_page(
        'Test POST', 
        'Test POST', 
        'manage_options', 
        'test-post', 
        'my_custom_page_callback' 
    );
}

// test POST
function my_custom_page_callback() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['job_id']) && is_numeric($_POST['job_id'])) {
            $job_id = intval($_POST['job_id']);
            
            // Log the Job ID from POST
            error_log("Job ID from POST: " . print_r($job_id, true));

            // Retrieve job data
            $job_title = get_the_title($job_id);
            $job_url = get_permalink($job_id);
            $job_publish_date = get_the_date('Y-m-d', $job_id);
            $job_expiry_date = get_post_meta($job_id, '_job_expiry_date', true);
            $job_apply_email = get_post_meta($job_id, '_job_apply_email', true);
            $location = get_post_meta($job_id, '_job_address', true);
            $salary = get_post_meta($job_id, '_job_salary', true);
            
            // Log each value
            error_log("Job Title: " . $job_title);
            error_log("Job URL: " . $job_url);
            error_log("Job Publish Date: " . $job_publish_date);
            error_log("Job Expiry Date: " . $job_expiry_date);
            error_log("Job Apply Email: " . $job_apply_email);
            error_log("Location: " . $location);
            error_log("Salary: " . $salary);

			// Fetch the employer ID from the job post
			$employer_id = get_post_meta($job_id, '_job_employer_posted_by', true);
			// Log employer name
			error_log("Employer ID: " . $employer_id); // Log the employer ID to check if it's correct
			
			if ($employer_id) {
				// Get the title of the employer
				$employer_name = get_the_title($employer_id);

				// Log employer name
				error_log("Employer Name: " . $employer_name);
				echo "<p>Employer Name: $employer_name</p>"; // Display employer name
			} else {
				error_log("No employer ID found for job ID: $job_id");
			}

// 			error_log("Employer Name: " . $employer_name); // Log the employer name
            
            // Provide user feedback
            echo "<p>Job Title: $job_title</p>";
            echo "<p>Employer Name: $employer_name</p>";
        } else {
            echo "<p>Invalid Job ID provided.</p>";
        }
    }
    ?>
    <form method="POST"> 
        <input type="hidden" name="job_id" value="8742"> <!-- Ensure a valid job ID -->
        <label for="additional_info">Additional Info:</label>
        <input type="text" name="additional_info" id="additional_info">
        <input type="submit" value="Submit Job ID">
    </form>
    <?php
}

// Restrict wp-admin but allow AJAX requests
function restrict_wp_admin_access() {
    $allowed_user_id = 1; // ID korisnika kojem je dozvoljen pristup wp-admin

    if ( is_admin()
        && !( defined('DOING_AJAX') && DOING_AJAX ) // Dozvoli admin-ajax.php
        && get_current_user_id() != $allowed_user_id
    ) {
        wp_redirect( home_url() );
        exit;
    }
}
add_action( 'init', 'restrict_wp_admin_access' );


// Template for Job allert email used in class-job-alert.php
function set_job_entry_template() {
    $template = '
		<table>
			<tr>
				<td align="left" valign="bottom" style="padding:10px 0 0 0; font-family: Calibri; font-style: normal; font-size:18px; font-weight:800; color:#3068d0;">
					<a href="{{job_url}}" target="_blank" style="display: inline-block; line-height: 0; font-weight:600; color:#3068d0; text-decoration: none;">
						{{job_title}}
					</a>
				</td>
			</tr>
			<tr>
				<td align="left" valign="bottom" style="font-family: Calibri; font-style: normal; font-size:18px; font-weight:400; color:#838589; text-transform: uppercase;">
					{{employer_name}}
				</td>  
			</tr>
			<tr>
				<td align="left" valign="bottom" style="font-family: Calibri; font-style: normal; font-size:16px; font-weight:400; color:#838589;">
					{{location}}
				</td>  
			</tr>
			<tr>
				<td align="left" valign="bottom" style="font-family: Calibri; font-style: normal; font-size:16px; font-weight:400; color:#838589;">
					{{job_publish_date}}
				</td>  
			</tr>
			<tr>
				<td align="left" valign="bottom" style="padding:12px 0 0 0; font-style: normal; font-size:16px; border-bottom: 1px solid #a9ddff;">
				</td>
			</tr>
		</table>';
    
    update_option('job_entry_template', $template);
}

// Template for Canidate allert email used in class-candidate-alert.php
function set_candidate_entry_template() {
    $template = '
		<table style="width:100%;">
			<tr>
				<td align="left" valign="bottom" style="padding:10px 0 0 0; font-family: Calibri; font-style: normal; font-size:18px; font-weight:800; color:#3068d0;">
					<a href="{{candidate_url}}" target="_blank" style="display: inline-block; line-height: 0; font-weight:600; color:#3068d0; text-decoration: none;">
						{{candidate_title}}
					</a>
				</td>
			</tr>
			<tr>
				<td align="left" valign="bottom" style="font-family: Calibri; font-style: normal; font-size:18px; font-weight:400; color:#838589;">
					Zanimanje: <span style="font-weight:600;">{{candidate_job_title}}</span>
				</td>  
			</tr>
			<tr>
				<td align="left" valign="bottom" style="font-family: Calibri; font-style: normal; font-size:16px; font-weight:400; color:#838589;">
					Radno iskustvo: <span style="font-weight:600;">{{candidate_experience_time}}</span>
				</td>  
			</tr>
			<tr>
				<td align="left" valign="bottom" style="font-family: Calibri; font-style: normal; font-size:16px; font-weight:400; color:#838589;">
					Datum rođenja: <span style="font-weight:600;">{{candidate_birth_date}}</span>
				</td>  
			</tr>
			<tr>
				<td align="left" valign="bottom" style="padding:12px 0 0 0; font-style: normal; font-size:16px; border-bottom: 1px solid #a9ddff;">
				</td>
			</tr>
		</table>';
    
    update_option('candidate_entry_template', $template);
}

// Hook the function to an appropriate action
function enqueue_phone_field_scripts() {
    // Enqueue intlTelInput CSS
    wp_enqueue_style(
        'intl-tel-input-css',
        'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css',
        array(),
        null
    );

    // Enqueue intlTelInput JS
    wp_enqueue_script(
        'intl-tel-input-js',
        'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js',
        array(),
        null,
        true // Load in footer
    );

    // Enqueue custom phone field JS (external file) with dynamic versioning to prevent caching
    wp_enqueue_script(
        'phone-field-js',
        get_template_directory_uri() . '/js/phone-field.js', // Path to your JS file
        array('intl-tel-input-js'), // Make sure intl-tel-input.js is loaded first
        time(), // Dynamic version using file modification time
        true // Load in footer
    );
}
add_action('wp_enqueue_scripts', 'enqueue_phone_field_scripts');

function register_half_job_detail_sidebar() {
    register_sidebar(array(
        'name'          => 'Jobs filter Half Job Detail  sidebar',
		'id'            => 'jobs-filter-top-half-sidebar',
		'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'superio' ),
		'before_widget' => '<aside class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title"><span>',
		'after_title'   => '</span></h2>',
    ));
}
add_action('widgets_init', 'register_half_job_detail_sidebar');

// Allow frontend AJAX requests even for non-admin users
add_action('init', function () {
    // Ako je ovo AJAX poziv (kao admin-ajax.php)
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return; // Dozvoli sve, ne radi redirect
    }

    // Sve ostalo neka radi po starom
});


/**
 * ==============================================
 * PHPMailer SMTP Setup for Amazon SES (STATIC)
 * ==============================================
 */
add_action( 'phpmailer_init', function( $phpmailer ) {

    // SMTP settings
    $phpmailer->isSMTP();
    $phpmailer->Host       = SMTP_HOST;
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = SMTP_PORT;
    $phpmailer->Username   = SMTP_USER;
    $phpmailer->Password   = SMTP_PASS;
    $phpmailer->SMTPSecure = 'tls';

    $phpmailer->IsHTML(true);

    /**
     * IMPORTANT:
     * Do NOT force From / FromName here.
     * If your headers already set From, PHPMailer will USE IT.
     */
    
    if ( empty( $phpmailer->From ) || strpos( $phpmailer->From, '@raspitajse.com' ) === false ) {
        $phpmailer->From     = 'noreply@raspitajse.com';
        $phpmailer->FromName = 'Raspitajse.com - Vaš pouzdan AI model';
    }
});


/**
 * Capture headers before PHPMailer is created
 */
add_action('wp_mail', function($args){
    $GLOBALS['rasp_last_mail_headers'] = is_array($args['headers']) 
        ? implode("\n", $args['headers']) 
        : $args['headers'];
});


/**
 * Global sender for all system emails (unless a header overrides it)
 */
add_filter( 'wp_mail_from', function( $email ) {

    // If custom From header exists, do not override
    if ( ! empty( $GLOBALS['rasp_last_mail_headers'] ) &&
         strpos( $GLOBALS['rasp_last_mail_headers'], 'From:' ) !== false ) {
        return $email;
    }

    return 'noreply-system@raspitajse.com';
});


add_filter( 'wp_mail_from_name', function( $name ) {

    // Respect custom FromName
    if ( ! empty( $GLOBALS['rasp_last_mail_headers'] ) &&
         strpos( $GLOBALS['rasp_last_mail_headers'], 'From:' ) !== false ) {
        return $name;
    }

    return 'Raspitajse.com - Vaš pouzdan AI model';
});



/**
 * ===========================
 * SEND TEST EMAIL (STATIC)
 * ===========================
 */
function rs_smtp_test_email() {

    // Primaoca možeš da staviš svoj realni mail
    $to       = defined('SMTP_FROM') ? SMTP_FROM : 'dr@raspitajse.com';
    $subject  = 'Amazon SES SMTP Test - RaspitajSe.com';
    $message  = 'Ovo je test email poslat preko Amazon SES SMTP konfiguracije.';
    $headers  = ['Content-Type: text/html; charset=UTF-8'];

    if (wp_mail($to, $subject, $message, $headers)) {
        echo '<div class="notice notice-success"><p>Test email uspešno poslat putem Amazon SES!</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>Greška: Email NIJE poslat. Proveri AWS SES SMTP podešavanja.</p></div>';
    }
}


/**
 * ===========================================
 * ADMIN PAGE — TEST EMAIL BUTTON
 * ===========================================
 */
add_action('admin_menu', function() {
    add_menu_page(
        'SES SMTP Test',
        'SES SMTP Test',
        'manage_options',
        'smtp-test',
        function() {
            echo '<h1>Amazon SES SMTP Test Email</h1>';
            echo '<p>Klikni ispod da pošalješ test email preko Amazon SES SMTP transporta.</p>';
            echo '<a href="?page=smtp-test&send=1" class="button button-primary">Pošalji test email</a>';

            if (!empty($_GET['send'])) {
                rs_smtp_test_email();
            }
        }
    );
});

/* Adding JS scripts to child theme  */
function superio_child_enqueue_scripts() {

    $file = get_stylesheet_directory() . '/assets/js/custom-mobile-scroll.js';
    $version = filemtime( $file ); // dynamic version

    wp_enqueue_script(
        'superio-child-custom-js',
        get_stylesheet_directory_uri() . '/assets/js/custom-mobile-scroll.js',
        array('jquery'),
        $version,
        true
    );
}
add_action('wp_enqueue_scripts', 'superio_child_enqueue_scripts');

/**
 * Hide specific Elementor buttons based on user login/role conditions
 */
add_filter('the_content', 'raspitajse_filter_header_buttons', 20);
add_filter('elementor/frontend/the_content', 'raspitajse_filter_header_buttons', 20);

function raspitajse_filter_header_buttons($content) {

    // 1️⃣ Ako je user ulogovan → ukloni "Registrujte se" dugme (register-btn)
    if ( is_user_logged_in() ) {
        $content = preg_replace(
            '/<a[^>]*id="register-btn"[^>]*>.*?<\/a>/si',
            '',
            $content
        );
    }

    // 2️⃣ Ako user NIJE ulogovan kandidat → ukloni "Pretražite oglase" dugme
    if ( is_user_logged_in() ) {

        $user = wp_get_current_user();
        $roles = (array) $user->roles;

        if ( !in_array('wp_job_board_pro_candidate', $roles) ) {
            $content = preg_replace(
                '/<a[^>]*id="browse-job-btn-1"[^>]*>.*?<\/a>/si',
                '',
                $content
            );
        }

    } else {
        // User nije ulogovan → potpuno ukloniti dugme "Pretražite oglase"
        $content = preg_replace(
            '/<a[^>]*id="browse-job-btn-1"[^>]*>.*?<\/a>/si',
            '',
            $content
        );
    }

    return $content;
}

add_filter('gettext', 'raspitajse_quick_translate', 999, 3);
function raspitajse_quick_translate($translated, $text, $domain) {

    // Ako nije string – ne diramo
    if (!is_string($text) || !is_string($translated)) {
        return $translated;
    }

    // Normalizacija (kritično)
    $normalized = trim(preg_replace('/\s+/', ' ', $text));

    $map = [
        'All Applicants'       => 'Svi kandidati',
        'Candidate Shortlist'  => 'Uži izbor kandidata',
        'Candidate Alerts'     => 'Obaveštenja o kandidatima',
        'My Packages'          => 'Moji paketi',

        'Title'                => 'Naziv',
        'Alert Query'          => 'Upit za obaveštenje',
        'Number Candidates'    => 'Broj kandidata',
        'Times'                => 'Učestalost',
        'Actions'              => 'Akcije',

        'Search'               => 'Pretraga',
        'Submit Job'           => 'Pošalji oglas',
        'Edit Job'             => 'Izmeni oglas',
        'Filter by job'        => 'Filtriraj po poslu',

        'Sort by:'             => 'Sortiraj po:',
        'Newest'               => 'Najnovije',
        'Oldest'               => 'Najstarije',
        'Default'              => 'Podrazumevano',

        'Total(s):'            => 'Ukupno:',
        'Approved'             => 'Odobreno',
        'Approved:'            => 'Odobreno:',
        'Rejected'             => 'Odbijeno',
        'Rejected(s):'         => 'Odbijeno:',
        'Applied date'         => 'Datum prijave',
        'Applied date:'        => 'Datum prijave:',
        'Pending'              => 'Na čekanju',

        '#'                    => '#',
        'ID'                   => 'ID',
        'Package'              => 'Paket',
        'Package Type'         => 'Tip paketa',
        'Package Info'         => 'Informacije o paketu',
        'Status'               => 'Status',
    ];

    if (isset($map[$normalized])) {
        return (string) $map[$normalized];
    }

    return (string) $translated;
}


add_action('added_user_meta', function ($meta_id, $user_id, $meta_key, $meta_value) {

    // WP Job Board Pro dodeljuje paket preko user meta
    if (strpos($meta_key, 'wjbpwpl_user_package') === false) {
        return;
    }

    // $meta_value je package_id
    $package_id = absint($meta_value);
    if (!$package_id) return;

    // Ako već postoji expiration – ne diramo
    $existing = get_user_meta(
        $user_id,
        '_wjbp_package_expiration_' . $package_id,
        true
    );

    if ($existing) return;

    // Paket važi 30 dana
    $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));

    update_user_meta(
        $user_id,
        '_wjbp_package_expiration_' . $package_id,
        $expires_at
    );

}, 10, 4);

/**
 * =========================================================
 * NBS EUR → RSD exchange rate (cached daily)
 * =========================================================
 */
function raspitajse_get_nbs_eur_to_rsd_rate() {

    $rate = get_transient( 'raspitajse_nbs_eur_rsd_rate' );
    if ( $rate !== false ) {
        return (float) $rate;
    }

    $response = wp_remote_get( 'https://www.nbs.rs/kursnaListaModul/zaDevize.faces?lang=lat' );
    if ( is_wp_error( $response ) ) {
        return 117.5;
    }

    $body = wp_remote_retrieve_body( $response );

    if ( preg_match( '/<td>EUR<\/td>.*?<td>([\d,]+)<\/td>/s', $body, $m ) ) {
        $rate = (float) str_replace( ',', '.', $m[1] );
        set_transient( 'raspitajse_nbs_eur_rsd_rate', $rate, DAY_IN_SECONDS );
        return $rate;
    }

    return 117.5;
}

/**
 * =========================================================
 * FINAL order conversion (ONLY place PHP touches prices)
 * =========================================================
 */
add_action( 'woocommerce_checkout_create_order', function ( $order ) {

    if ( ! WC()->session ) return;

    if ( WC()->session->get( 'chosen_payment_method' ) !== 'bank_transfer_1' ) {
        return;
    }

    $rate = raspitajse_get_nbs_eur_to_rsd_rate();

    foreach ( $order->get_items() as $item ) {
        $eur = $item->get_total();
        $rsd = round( $eur * $rate );

        $item->set_total( $rsd );
        $item->set_subtotal( $rsd );

        $item->add_meta_data(
            'Original price (EUR)',
            wc_price( $eur, [ 'currency' => 'EUR' ] ),
            true
        );

        $item->save();
    }

    $order->set_total( round( $order->get_total() * $rate ) );
});

add_action( 'wp_footer', function () {
    if ( ! is_checkout() ) return;

    $rate = raspitajse_get_nbs_eur_to_rsd_rate();
    ?>
    <script>
        jQuery(function ($) {

            const rate = <?php echo esc_js( $rate ); ?>;

            function parseEUR(text) {
                return parseFloat(
                    text.replace(/[^\d,]/g, '').replace(',', '.')
                );
            }

            function formatRSD(value) {
                return value.toLocaleString('sr-RS') + ' рсд';
            }

            function formatEUR(value) {
                return value.toFixed(2).replace('.', ',') + ' €';
            }

            function convertToRSD() {
                $('.woocommerce-Price-amount').each(function () {
                    const el = $(this);

                    if (!el.data('eur')) {
                        el.data('eur', parseEUR(el.text()));
                    }

                    const eur = el.data('eur');
                    el.text(formatRSD(Math.round(eur * rate)));
                });
            }

            function restoreEUR() {
                $('.woocommerce-Price-amount').each(function () {
                    const el = $(this);
                    const eur = el.data('eur');
                    if (eur) {
                        el.text(formatEUR(eur));
                    }
                });
            }

            $('input[name="payment_method"]').on('change', function () {
                if ($(this).val() === 'bank_transfer_1') {
                    convertToRSD();
                } else {
                    restoreEUR();
                }
            });

        });
    </script>
    <?php
});
