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

    if (!is_string($text) || !is_string($translated)) {
        return $translated;
    }

    $normalized = trim(preg_replace('/\s+/', ' ', $text));

    $map = [
        // --- WooCommerce checkout ---
        // --- Product detail page ---
        'You cannot add another "%s" to your cart.' => 'Ne možete dodati još jedan "%s" u vašu korpu.',
        // --- Product meta ---
        'SKU:' => 'Šifra proizvoda:',
        'Category:'   => 'Kategorija:',
        'Category'    => 'Kategorija',
        'Categories:' => 'Kategorije:',
        'Categories'  => 'Kategorije',
        'Tags:'       => 'Oznake:',
        'Tags'        => 'Oznake',
        'Tag:'        => 'Oznaka:',
        'Tag'         => 'Oznaka',
        'Tag(s):'     => 'Oznake:',

         // --- Thank you page ---
        'Thank you. Your order has been received.' => 'Hvala vam. Vaša porudžbina je uspešno primljena.',

        // --- Order overview ---
        'Order number:'   => 'Broj porudžbine:',
        'Order Number:'   => 'Broj porudžbine:',
        'Order Date:'     => 'Datum porudžbine:',
        'Date:'           => 'Datum:',
        'Total:'          => 'Ukupno:',
        'Payment method:' => 'Način plaćanja:',
        'Invoice Number:' => 'Broj fakture:',
        'Due Date:'       => 'Rok plaćanja:',
        'INVOICE'         => 'FAKTURA',

        // --- Order details section ---
        'Order details' => 'Detalji porudžbine',

        // --- Table headers ---
        'Product'   => 'Proizvod',
        'Subtotal:' => 'Međuzbir:',
        'Total:'    => 'Ukupno:',

        // --- Footer rows ---
        'Payment method:' => 'Način plaćanja:',
        // --- Order again ---
        'Order again' => 'Poruči ponovo',
        'Billing address' => 'Adresa za fakturisanje',

        // --- Reviews ---
        'Reviews (%d)' => 'Recenzije (%d)',
        '%d Reviews' => '%d recenzija',
        'Reviews' => 'Recenzije',
        'Add a review'  => 'Dodaj recenziju',      
        'There are no reviews yet.' => 'Još uvek nema recenzija.',
        'Be the first to review “%s”' => 'Budite prvi koji će oceniti „%s“',
        'Your Rating' => 'Vaša ocena',
        'Your comment is awaiting approval' => 'Vaš komentar čeka odobrenje',         
        'Rate…' => 'Oceni…',
        'Perfect' => 'Odlično',
        'Good' => 'Dobro',
        'Average' => 'Prosečno',
        'Not that bad' => 'Nije loše',
        'Very Poor' => 'Veoma loše',
        'submit review' => 'Pošalji recenziju',
        'Cancel reply' => 'Otkaži odgovor',
        
        // --- Cart table headers ---
        'Image' => 'Slika',
        'Product Name' => 'Naziv proizvoda',
        'Price' => 'Cena',
        'Quantity' => 'Količina',
        'Add to cart' => 'Dodaj u korpu',

        // --- Variations / meta ---
        'Job Listing:' => 'Oglas za posao:',

        // --- Remove link accessibility label ---
        'Remove this item' => 'Ukloni ovu stavku',

        // --- Cart actions ---
        'Update cart' => 'Ažuriraj korpu',

        // --- Totals box ---
        'Cart totals' => 'Ukupno u korpi',

        // --- Checkout button ---
        'Proceed to Checkout' => 'Nastavi na plaćanje',

        '"%s" has been added to your cart.' => '"%s" je dodat u vašu korpu.',
        'View cart' => 'Pogledaj korpu',
        'Have a coupon? Click here to enter your code' => 'Imate kupon? Kliknite ovde da unesete kod',
        'If you have a coupon code, please apply it below.' => 'Ako imate kupon, unesite ga ispod.',
        'Apply coupon' => 'Primeni kupon',
        'Coupon code' => 'Kod kupona',

        // --- Coupon toggle / coupon form ---
        'Have a coupon?' => 'Imate kupon?',
        'Click here to enter your code' => 'Kliknite ovde da unesete kod',
        'Coupon:' => 'Kupon:',
        'Coupon code' => 'Kod kupona',
        'Apply coupon' => 'Primeni kupon',

        // --- Billing (company field) ---
        'Company name' => 'Naziv kompanije',
        '(optional)' => '(opciono)',

        // --- Address placeholders (Woo default) ---
        'House number and street name' => 'Ulica i broj',
        'npr. 76/11' => 'npr. 76/11', // ovo već jeste srpski, ali ostavljam ako se menja
        'Select a country / region…' => 'Izaberite državu / region…',
        'Update country / region' => 'Ažuriraj državu / region',

        // --- Additional info section / notes textarea placeholder ---
        'Additional Information' => 'Dodatne informacije',
        'Notes about your order, e.g. special notes for delivery.' => 'Napomena uz porudžbinu, npr. dodatne informacije.',

        // --- Order review variations ---
        'Job Listing:' => 'Oglas za posao:',

        // --- No-JS notice + buttons ---
        'Since your browser does not support JavaScript, or it is disabled, please ensure you click the Update Totals button before placing your order. You may be charged more than the amount stated above if you fail to do so.' =>
        'Pošto vaš pregledač ne podržava JavaScript ili je isključen, obavezno kliknite dugme „Ažuriraj ukupno“ pre slanja porudžbine. U suprotnom može biti naplaćen veći iznos od prikazanog.',
        'Update Totals' => 'Ažuriraj ukupno',
        'Update totals' => 'Ažuriraj ukupno',

        // --- Privacy policy text ---
        // Privacy policy (Woo koristi placeholder za link)
        'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our %s.' =>
        'Vaši lični podaci biće korišćeni za obradu porudžbine, podršku vašem iskustvu na ovom sajtu i u druge svrhe opisane u našoj %s.',
        'Privacy policy' => 'Politika privatnosti',

        // --- Place order button ---
        'Place order' => 'Pošalji porudžbinu',

        // --- Checkout sections ---
        'Billing details' => 'Podaci za fakturisanje',
        'Additional information' => 'Dodatne informacije',
        'Your order' => 'Vaša porudžbina',

        // --- Order table ---
        'Product' => 'Proizvod',
        'Subtotal' => 'Međuzbir',
        'Total' => 'Ukupno',
        'Payment' => 'Plaćanje',

        // --- Common fields (ako se negde pojave kao stringovi) ---
        'Company name (optional)' => 'Naziv kompanije (opciono)',
        'First name' => 'Ime',
        'Last name' => 'Prezime',
        'Country / Region' => 'Država / Region',
        'Street address' => 'Ulica',
        'Postcode / ZIP' => 'Poštanski broj',
        'Town / City' => 'Grad',
        'Phone' => 'Telefon',
        'Email address' => 'Email adresa',

        // --- Existing (tvoja) ---
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

add_filter('date_i18n', function ($date, $format, $timestamp, $gmt) {

    $months = [
        'January '   => 'Januar ',
        'February '  => 'Februar ',
        'March '     => 'Mart ',
        'April '     => 'April ',
        'May '       => 'Maj ',
        'June '      => 'Jun ',
        'July '      => 'Jul ',
        'August '    => 'Avgust ',
        'September ' => 'Septembar ',
        'October '   => 'Oktobar ',
        'November '  => 'Novembar ',
        'December '  => 'Decembar ',
    ];

    // menja samo naziv meseca, ostalo ostaje: "24, 2026"
    return strtr($date, $months);

}, 10, 4);


/**
 * =========================================================
 * WP Job Board Pro – User Package Expiration Fix
 * =========================================================
 */

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
 * WooCommerce – Serbian country names
 * =========================================================
 */
add_filter( 'woocommerce_countries', function ( $countries ) {

    $sr = [
        'RS' => 'Srbija',
        'DE' => 'Nemačka',
        'AT' => 'Austrija',
        'CH' => 'Švajcarska',
        'FR' => 'Francuska',
        'IT' => 'Italija',
        'ES' => 'Španija',
        'HR' => 'Hrvatska',
        'BA' => 'Bosna i Hercegovina',
        'ME' => 'Crna Gora',
        'MK' => 'Severna Makedonija',
        'SI' => 'Slovenija',
        'HU' => 'Mađarska',
        'PL' => 'Poljska',
        'CZ' => 'Češka',
        'SK' => 'Slovačka',
        'RO' => 'Rumunija',
        'BG' => 'Bugarska',
        'GR' => 'Grčka',
        'NL' => 'Holandija',
        'BE' => 'Belgija',
        'SE' => 'Švedska',
        'NO' => 'Norveška',
        'DK' => 'Danska',
        'FI' => 'Finska',
        'IE' => 'Irska',
        'PT' => 'Portugal',
        'GB' => 'Ujedinjeno Kraljevstvo',
        'US' => 'Sjedinjene Američke Države',
        'CA' => 'Kanada',
        'AU' => 'Australija',
        'TR' => 'Turska',
        'AE' => 'Ujedinjeni Arapski Emirati',
        'SA' => 'Saudijska Arabija',
    ];

    foreach ( $sr as $code => $name ) {
        if ( isset( $countries[ $code ] ) ) {
            $countries[ $code ] = $name;
        }
    }

    return $countries;
});


/**
 * =========================================================
 * Employer profile – WC Country Select (NEW FIELD)
 * =========================================================
 */
add_action( 'wp_footer', function () {

    // samo na profile stranici
    if ( ! is_page( 'profile' ) ) {
        return;
    }

    if ( ! function_exists( 'WC' ) || ! WC()->countries ) {
        return;
    }

    $countries = WC()->countries->get_countries();
    ?>
    <script>
        jQuery(function ($) {

            const countries = <?php echo wp_json_encode( $countries ); ?>;
            const select = $('#custom-select-40692190');

            if (!select.length) {
                return;
            }

            // ako su već ubačene opcije – ne diraj
            if (select.find('option').length > 1) {
                return;
            }

            select.empty();
            select.append('<option></option>');

            Object.entries(countries).forEach(([code, name]) => {
                select.append(
                    $('<option>', {
                        value: code,
                        text: name
                    })
                );
            });

            // refresh select2
            if (select.hasClass('select2-hidden-accessible')) {
                select.trigger('change.select2');
            }
        });
    </script>
    <?php
});

/**         
 * =========================================================
 * WooCommerce – Remove product tabs: Description
 */
add_filter('woocommerce_product_tabs', function ($tabs) {
    unset($tabs['description']);
    return $tabs;
}, 98);



/**
 * =========================================================
 * Checkout – Legal entities only (Company, PIB, MB)
 * =========================================================
 */

/**
 * Notice before billing fields
 */
add_action( 'woocommerce_checkout_before_customer_details', function () {
    ?>
    <div class="legal-entity-notice" style="margin-bottom:20px;padding:15px;background:#f5f9ff;border-left:4px solid #2a90cc;">
        <strong>Važno obaveštenje</strong><br>
        Molimo vas da uplatu izvršite <strong>isključivo sa računa pravnog lica</strong>.
        Podaci koje unesete biće korišćeni za izdavanje fakture i moraju biti tačni i potpuni.
    </div>
    <?php
});

/**
 * =========================================================
 * Checkout – Address layout (2 by 2) + Serbian labels (FIXED)
 * =========================================================
 */
add_filter( 'woocommerce_checkout_fields', function ( $fields ) {

    /* -----------------------------------------------------
     * UKLANJANJE
     * ----------------------------------------------------- */
    unset( $fields['billing']['billing_state'] );      // District
    unset( $fields['billing']['billing_address_2'] ); // Apartment
    unset( $fields['billing']['billing_first_name'] );
    unset( $fields['billing']['billing_last_name'] );

    /**
     * NAZIV KOMPANIJE (OBAVEZNO)
     */
    $fields['billing']['billing_company'] = array_merge(
        $fields['billing']['billing_company'] ?? [],
        [
            'label'       => 'Naziv kompanije',
            'required'    => true,
            'class'       => ['form-row-wide'],
            'priority'    => 30,
            'placeholder' => 'npr. Dots Agencija',
        ]
    );


    /**
     * PIB
     */
    $fields['billing']['billing_pib'] = [
        'label'    => 'Poreski Identifikacioni Broj (PIB)',
        'required' => true,
        'class'    => ['form-row-first'],
        'priority' => 15,
    ];

    /**
     * MATIČNI BROJ
     */
    $fields['billing']['billing_mb'] = [
        'label'    => 'Matični broj',
        'required' => true,
        'class'    => ['form-row-last'],
        'priority' => 16,
    ];

    /* -----------------------------------------------------
     * RED 2 – ULICA | BROJ
     * ----------------------------------------------------- */

    $fields['billing']['billing_address_1'] = array_merge(
        $fields['billing']['billing_address_1'],
        [
            'label'       => 'Ulica',
            'placeholder' => 'npr. Nemanjina',
            'required'    => true,
            'class'       => ['form-row-first'],
            'priority'    => 30,
        ]
    );

    $fields['billing']['billing_house_number'] = [
        'label'       => 'Broj',
        'placeholder' => 'npr. 76/11',
        'required'    => true,
        'class'       => ['form-row-last'],
        'priority'    => 31,
    ];

    /* -----------------------------------------------------
     * RED 3 – POŠTANSKI BROJ | GRAD
     * ----------------------------------------------------- */

    $fields['billing']['billing_postcode'] = array_merge(
        $fields['billing']['billing_postcode'],
        [
            'label'    => 'Poštanski broj',
            'required' => true,
            'class'    => ['form-row-first'],
            'priority' => 40,
        ]
    );

    $fields['billing']['billing_city'] = array_merge(
        $fields['billing']['billing_city'],
        [
            'label'    => 'Grad',
            'required' => true,
            'class'    => ['form-row-last'],
            'priority' => 41,
        ]
    );

    /* -----------------------------------------------------
     * RED 4 – DRŽAVA (FULL WIDTH)
     * ----------------------------------------------------- */

    $fields['billing']['billing_country'] = array_merge(
        $fields['billing']['billing_country'],
        [
            'label'    => 'Država',
            'required' => true,
            'class'    => ['form-row-wide'],
            'priority' => 50,
        ]
    );

    /* -----------------------------------------------------
     * RED 5 – EMAIL | TELEFON
     * ----------------------------------------------------- */

    $fields['billing']['billing_email'] = array_merge(
        $fields['billing']['billing_email'],
        [
            'label'    => 'Email adresa',
            'class'    => ['form-row-first'],
            'priority' => 60,
        ]
    );

    $fields['billing']['billing_phone'] = array_merge(
        $fields['billing']['billing_phone'],
        [
            'label'    => 'Telefon',
            'class'    => ['form-row-last'],
            'priority' => 61,
        ]
    );

    /* -----------------------------------------------------
     * NAPOMENA
     * ----------------------------------------------------- */
    if ( isset( $fields['order']['order_comments'] ) ) {
        $fields['order']['order_comments']['label']    = 'Napomena uz porudžbinu';
        $fields['order']['order_comments']['priority'] = 90;
    }

    return $fields;
});

/**
 * =========================================================
 * Checkout – FORCE layout order via JS (Woo-safe)
 * =========================================================
 */
add_action( 'wp_footer', function () {
    if ( ! is_checkout() ) return;
    ?>
    <script>
        jQuery(function ($) {
            
            // WooCommerce mini loader functions
            function showMiniLoader() {
                const wrapper = $('.woocommerce-billing-fields__field-wrapper');
                if (!wrapper.find('.wc-mini-loader').length) {
                    wrapper.css('position', 'relative');
                    wrapper.append('<div class="wc-mini-loader"></div>');
                }
            }

            function hideMiniLoader() {
                $('.wc-mini-loader').remove();
            }

            function reorderBillingFields() {

                const wrapper = $('.woocommerce-billing-fields__field-wrapper');
                if (!wrapper.length) return;

                // Ne diraj Select2 dok je otvoren
                if ($('body').hasClass('select2-container--open')) {
                    return;
                }

                const company  = $('#billing_company_field');
                const pib      = $('#billing_pib_field');
                const mb       = $('#billing_mb_field');
                const street   = $('#billing_address_1_field');
                const number   = $('#billing_house_number_field');
                const postcode = $('#billing_postcode_field');
                const city     = $('#billing_city_field');
                const country  = $('#billing_country_field');
                const email    = $('#billing_email_field');
                const phone    = $('#billing_phone_field');

                const fields = [
                    company,
                    pib,
                    mb,
                    street,
                    number,
                    postcode,
                    city,
                    country,
                    email,
                    phone
                ];

                fields.forEach(el => {
                    if (el.length) wrapper.append(el);
                });

                // 🧩 SAMO KLASE (bez pomeranja)
                company.attr('class', 'form-row form-row-wide validate-required');

                pib.attr('class', 'form-row form-row-first validate-required');
                mb.attr('class', 'form-row form-row-last validate-required');

                street.attr('class', 'form-row form-row-first validate-required');
                number.attr('class', 'form-row form-row-last validate-required');

                postcode.attr('class', 'form-row form-row-first validate-required');
                city.attr('class', 'form-row form-row-last validate-required');

                country.attr('class', 'form-row form-row-wide validate-required');

                email.attr('class', 'form-row form-row-first validate-required');
                phone.attr('class', 'form-row form-row-last validate-required');
            }

            // 🟢 INIT (delay da Select2 završi init)
            setTimeout(reorderBillingFields, 300);

            // 🔁 POSLE Woo AJAX-a
            $(document.body).on('updated_checkout', function () {
                setTimeout(reorderBillingFields, 300);
            });

            // 🛡️ POSLE ZATVARANJA Select2 (kritično)
            $(document).on('select2:close', function () {
                showMiniLoader();

                setTimeout(function () {
                    reorderBillingFields();
                    hideMiniLoader();
                }, 200);
            });

        });
    </script>
    <?php
});


/**
 * =========================================================
 * FINAL FIX: Merge street + house number AFTER Woo saves order
 * =========================================================
 */
add_action( 'woocommerce_checkout_create_order', function ( $order ) {

    if ( ! $order instanceof WC_Order ) {
        return;
    }

    $street = isset( $_POST['billing_address_1'] )
        ? sanitize_text_field( $_POST['billing_address_1'] )
        : '';

    $house_number = isset( $_POST['billing_house_number'] )
        ? sanitize_text_field( $_POST['billing_house_number'] )
        : '';

    if ( $street && $house_number ) {
        $order->set_billing_address_1(
            trim( $street . ' ' . $house_number )
        );

        // opciono – čuvamo posebno
        $order->update_meta_data(
            '_billing_house_number',
            $house_number
        );
    }

}, 20 );

/**
 * 
 * Save PIB & MB to order meta
 */
add_action( 'woocommerce_checkout_update_order_meta', function ( $order_id ) {

    // PIB
    if ( isset( $_POST['billing_pib'] ) ) {
        update_post_meta(
            $order_id,
            '_billing_pib',
            sanitize_text_field( $_POST['billing_pib'] )
        );
    }

    // MB
    if ( isset( $_POST['billing_mb'] ) ) {
        update_post_meta(
            $order_id,
            '_billing_mb',
            sanitize_text_field( $_POST['billing_mb'] )
        );
    }

});


/**
 * Display House number, PIB & MB in admin order details
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', function ( $order ) {

    $mb  = $order->get_meta( '_billing_mb' );
    $pib = $order->get_meta( '_billing_pib' );


    if ( $pib || $mb ) {
        echo '<p><strong>Podaci o kompaniji</strong></p>';
        if ( $mb )  echo '<p>Matični broj: ' . esc_html( $mb ) . '</p>';
        if ( $pib ) echo '<p>PIB: ' . esc_html( $pib ) . '</p>';
    }

});


/**
 * =========================================================
 * HELPER: Get Employer ID by logged-in User
 * (WP Job Board Pro compatible)
 * =========================================================
 */
if ( ! function_exists( 'raspitajse_get_employer_id_by_user' ) ) {
    function raspitajse_get_employer_id_by_user( $user_id ) {

        if ( ! $user_id ) {
            return 0;
        }

        $posts = get_posts([
            'post_type'      => 'employer',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'meta_query'     => [
                [
                    // WP Job Board Pro stores user → employer link here
                    'key'   => '_employer_user_id',
                    'value' => (int) $user_id,
                ],
            ],
        ]);

        return ! empty( $posts ) ? (int) $posts[0]->ID : 0;
    }
}

/**
 * =========================================================
 * FRONTEND FORCE: Prefill Woo Checkout from Employer Profile
 * =========================================================
 */
add_action( 'wp_footer', function () {

    /**
     * 🛡 SAFETY FIRST
     * - no AJAX
     * - no REST
     * - checkout only
     * - logged-in users only
     */
    if ( wp_doing_ajax() || defined( 'REST_REQUEST' ) ) {
        return;
    }

    if ( ! is_checkout() || ! is_user_logged_in() ) {
        return;
    }

    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        return;
    }

    $employer_id = raspitajse_get_employer_id_by_user( $user_id );
    if ( ! $employer_id ) {
        return;
    }

    /**
     * =====================================================
     * WP Job Board Pro – VERIFIED DB META MAPPING
     *
     * Company title        → post_title
     * Matični broj (MB)    → custom-text-2726709
     * PIB                  → custom-text-2842853
     * Email                → _employer_email custom-text-32314799
     * Phone                → _employer_phone custom-text-3318838
     * Ulica                → custom-text-36619838
     * Broj                 → custom-number-37930732
     * Poštanski broj       → custom-number-38584023
     * Grad                 → custom-text-35868429
     * Drzava               → custom-select-40692190
     * =====================================================
     */

    $company = get_the_title( $employer_id );

    if ( empty( $company ) ) {
        $company = get_post_meta( $employer_id, '_employer_title', true );
    }

    $company = $company ?: '';

    $country = get_post_meta( $employer_id, 'custom-select-40692190', true );

    if ( $country ) {
        update_user_meta( get_current_user_id(), 'billing_country', $country );
    }

    $data = [
        'company' => $company,
        'mb'      => get_post_meta( $employer_id, 'custom-text-2726709', true ) ?: '',
        'pib'     => get_post_meta( $employer_id, 'custom-text-2842853', true ) ?: '',
        'email'   => get_post_meta( $employer_id, '_employer_email', true ) ?: '',
        'phone'   => get_post_meta( $employer_id, '_employer_phone', true ) ?: '',
        'ulica'   => get_post_meta( $employer_id, 'custom-text-36619838', true ) ?: '',
        'broj'   => get_post_meta( $employer_id, 'custom-number-37930732', true ) ?: '',
        'postanski_broj'   => get_post_meta( $employer_id, 'custom-number-38584023', true ) ?: '',
        'grad'   => get_post_meta( $employer_id, 'custom-text-35868429', true ) ?: '',
    ];

    // PHP debug (wp-content/debug.log)
    error_log( '[CHECKOUT → EMPLOYER DATA] ' . print_r( $data, true ) );
    ?>
    <script>
        jQuery(function ($) {

            const employer = <?php echo wp_json_encode(
                $data,
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            ); ?>;

            console.log('✅ Employer data injected into checkout:', employer);

            function fillCheckoutFields() {

                if (employer.company) {
                    $('#billing_company').val(employer.company).trigger('change');
                }

                if (employer.pib) {
                    $('#billing_pib').val(employer.pib).trigger('change');
                }

                if (employer.mb) {
                    $('#billing_mb').val(employer.mb).trigger('change');
                }

                if (employer.ulica) {
                    $('#billing_address_1').val(employer.ulica).trigger('change');
                }

                if (employer.broj) {
                    $('#billing_house_number').val(employer.broj).trigger('change');
                }

                if (employer.postanski_broj) {
                    $('#billing_postcode').val(employer.postanski_broj).trigger('change');
                }

                if (employer.grad) {
                    $('#billing_city').val(employer.grad).trigger('change');
                }

                if (employer.email) {
                    $('#billing_email').val(employer.email).trigger('change');
                }

                if (employer.phone) {
                    $('#billing_phone').val(employer.phone).trigger('change');
                }
            }

            // Initial fill
            fillCheckoutFields();

            // Re-fill after Woo updates checkout via AJAX
            $(document.body).on('updated_checkout', function () {
                fillCheckoutFields();
            });

        });
    </script>
    <?php
});

/**
 * =========================================================
 * FIX: Prefill Employer country select from post meta
 * =========================================================
 */
add_action( 'wp_footer', function () {

    if ( ! is_user_logged_in() ) {
        return;
    }

    // samo employer profile stranica
    if ( ! is_page( 'profile' ) ) {
        return;
    }

    if ( ! function_exists( 'raspitajse_get_employer_id_by_user' ) ) {
        return;
    }

    $user_id     = get_current_user_id();
    $employer_id = raspitajse_get_employer_id_by_user( $user_id );

    if ( ! $employer_id ) {
        return;
    }

    $country = get_post_meta( $employer_id, 'custom-select-40692190', true );
    if ( ! $country ) {
        return;
    }
    ?>
    <script>
        jQuery(function ($) {

            const country = '<?php echo esc_js( strtoupper( $country ) ); ?>';

            const select = $('#custom-select-40692190');

            if (select.length) {
                select.val(country).trigger('change.select2').trigger('change');
            }

        });
    </script>
    <?php
});


/**
 * =========================================================
 * WooCommerce Checkout – Prefill billing_country
 * from Employer profile (post meta)
 * =========================================================
 */
add_filter( 'woocommerce_checkout_get_value', function ( $value, $input ) {

    // Only target billing_country
    if ( $input !== 'billing_country' ) {
        return $value;
    }

    // User must be logged in
    if ( ! is_user_logged_in() ) {
        return $value;
    }

    $user_id = get_current_user_id();

    // Get employer ID linked to user
    if ( ! function_exists( 'raspitajse_get_employer_id_by_user' ) ) {
        return $value;
    }

    $employer_id = raspitajse_get_employer_id_by_user( $user_id );

    if ( ! $employer_id ) {
        return $value;
    }

    // ✅ Employer country (ISO code, e.g. RS)
    $country = get_post_meta( $employer_id, 'custom-select-40692190', true );

    if ( ! empty( $country ) ) {
        return $country; // MUST be ISO (RS, DE, AT…)
    }

    return $value;

}, 10, 2 );


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

add_action( 'wp_footer', function () {
    if ( ! is_checkout() ) return;

    $rate = raspitajse_get_nbs_eur_to_rsd_rate();
    ?>
    <script>
        jQuery(function ($) {

            const RATE = <?php echo esc_js( $rate ); ?>;

            function getEUR(el) {
                if (el.data('eur')) {
                    return el.data('eur');
                }

                const raw = el.text()
                    .replace(/\s/g, '')
                    .replace('€', '')
                    .replace('.', '')
                    .replace(',', '.');

                const eur = parseFloat(raw);
                el.data('eur', eur);
                return eur;
            }

            function formatEUR(val) {
                return val.toFixed(2).replace('.', ',') + ' €';
            }

            function formatRSD(val) {
                return val.toLocaleString('sr-RS') + ' RSD';
            }

            function applyCurrency() {
                const method = $('input[name="payment_method"]:checked').val();

                $('.woocommerce-Price-amount bdi').each(function () {
                    const el = $(this);
                    const eur = getEUR(el);

                    if (method === 'bank_transfer_1') {
                        el.text(formatRSD(Math.round(eur * RATE)));
                    } else {
                        el.text(formatEUR(eur));
                    }
                });
            }

            // Payment method click
            $(document).on('change', 'input[name="payment_method"]', function () {
                setTimeout(applyCurrency, 50);
            });

            // Woo checkout AJAX refresh
            $(document.body).on('updated_checkout', function () {
                setTimeout(applyCurrency, 50);
            });

            // Initial page load
            setTimeout(applyCurrency, 100);

        });
    </script>
    <?php
});

/**
 * =========================================================
 * FINAL & SAFE: Convert order to RSD ONCE on order creation
 * =========================================================
 */
add_action( 'woocommerce_checkout_create_order', function ( $order ) {

    if ( ! $order instanceof WC_Order ) {
        return;
    }

    // Prevent double conversion
    if ( $order->get_meta( '_converted_to_rsd' ) ) {
        return;
    }

    // Only for RSD bank transfer
    if ( $order->get_payment_method() !== 'bank_transfer_1' ) {
        return;
    }

    $rate = raspitajse_get_nbs_eur_to_rsd_rate();
    if ( ! $rate || $rate <= 0 ) {
        return;
    }

    // 🔒 STORE ORIGINAL
    $order->update_meta_data( '_original_currency', 'EUR' );
    $order->update_meta_data( '_eur_to_rsd_rate', $rate );

    $new_total = 0;

    foreach ( $order->get_items() as $item ) {

        $eur_total = (float) $item->get_total();
        $rsd_total = round( $eur_total * $rate );

        $item->add_meta_data( '_total_eur', $eur_total, true );

        $item->set_total( $rsd_total );
        $item->set_subtotal( $rsd_total );

        $item->save();
        $new_total += $rsd_total;
    }

    // ✅ SET ORDER TOTAL
    $order->set_total( $new_total );

    // ✅ THIS IS THE MISSING LINE
    $order->set_currency( 'RSD' );

    // Mark converted
    $order->update_meta_data( '_converted_to_rsd', 1 );

}, 20 );

add_action( 'wp_footer', function () {

    if ( ! is_wc_endpoint_url( 'order-received' ) ) {
        return;
    }
    ?>
    <script>
        jQuery(function ($) {

            function formatRSD(value) {
                return value.toLocaleString('sr-RS', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' RSD';
            }

            function shouldConvertToRSD() {
                const methodText = $('.woocommerce-order-overview__payment-method strong')
                    .text()
                    .toLowerCase();

                // ✅ Convert ONLY if RSD payment was used
                return methodText.includes('rsd');
            }

            function fixTotalIfNeeded() {

                if (!shouldConvertToRSD()) {
                    return true; // 🚫 EUR order → do nothing
                }

                const el = $('.woocommerce-order-overview__total bdi');

                if (!el.length) {
                    return false;
                }

                const text = el.text();

                // already converted
                if (text.includes('RSD')) {
                    return true;
                }

                const value = parseFloat(
                    text
                        .replace(/\./g, '')
                        .replace(',', '.')
                        .replace(/[^\d.]/g, '')
                );

                if (!isNaN(value)) {
                    el.text(formatRSD(value));
                    return true;
                }

                return false;
            }

            // ⏳ Wait until theme finishes rendering
            let attempts = 0;
            const interval = setInterval(function () {
                attempts++;

                if (fixTotalIfNeeded() || attempts > 20) {
                    clearInterval(interval);
                }
            }, 100);

        });
    </script>
    <?php
});

/**
 * =========================================================
 * FINAL FIX: RSD currency symbol fix on Order Received page
 * (in case some theme/plugin overrides Woo templates)
 * =========================================================
 */
add_action( 'wp_footer', 'raspitajse_fix_rsd_currency_with_js' );
function raspitajse_fix_rsd_currency_with_js() {

    if ( ! is_order_received_page() ) {
        return;
    }
    ?>
    <script>
    (function() {

        function fixRsdCurrency() {

            // proveri da li je RSD payment method (tekstualno)
            const paymentRow = document.querySelector(
                '.woocommerce-order-details tfoot tr:nth-child(2) td'
            );

            if (!paymentRow) return;

            const isRsdPayment = paymentRow.textContent.includes('RSD');

            if (!isRsdPayment) return;

            // sve cene u order details tabeli
            document.querySelectorAll(
                '.woocommerce-order-details .woocommerce-Price-amount bdi'
            ).forEach(function(bdi) {

                let text = bdi.textContent.trim();

                if (text.includes('€')) {
                    text = text.replace('€', 'RSD');
                    bdi.textContent = text;
                }
            });
        }

        // pokreni kad se DOM učita
        document.addEventListener('DOMContentLoaded', fixRsdCurrency);

        // fallback (neki theme-i renderuju kasnije)
        setTimeout(fixRsdCurrency, 500);
        setTimeout(fixRsdCurrency, 1500);

    })();
    </script>
    <?php
}


/**
 * =========================================================
 * DEBUG: Log selected payment method in console
 * =========================================================
 */

add_action( 'wp_footer', 'raspitajse_checkout_payment_method_debug' );
function raspitajse_checkout_payment_method_debug() {

    if ( ! is_checkout() ) {
        return;
    }
    ?>
    <script>
        function logSelectedPaymentMethod() {
            const checked = document.querySelector('input[name="payment_method"]:checked');
            if (checked) {
                console.log('Selected payment method:', checked.value);
            }
        }

        document.addEventListener('change', function(e) {
            if (e.target.name === 'payment_method') {
                logSelectedPaymentMethod();
            }
        });

        document.addEventListener('DOMContentLoaded', logSelectedPaymentMethod);
    </script>
    <?php
}

add_action('wp_head', function () {
    if (!is_order_received_page()) return;
    echo '<style>.woocommerce-bacs-bank-details{display:none !important;}</style>';
});

/**
 * =========================================================
 * WooCommerce – Nalog za uplatu (dinamički RSD/EUR)
 * =========================================================
 */
add_action('woocommerce_thankyou', 'raspitajse_render_payment_slip', 1);

function raspitajse_render_payment_slip($order_id) {

    if (!$order_id) return;

    $order = wc_get_order($order_id);
    if (!$order) return;

    $payment_method = $order->get_payment_method();
    if (!in_array($payment_method, ['bacs', 'bank_transfer_1'], true)) return;

    // --- Dinamički podaci iz porudžbine ---
    $billing_company = trim($order->get_billing_company());
    if ($billing_company === '') {
        $billing_company = trim($order->get_formatted_billing_full_name());
    }

    $currency = strtoupper((string) $order->get_currency()); // "RSD" / "EUR"
    $amount_raw = (float) $order->get_total();
    $amount = number_format($amount_raw, 2, ',', ''); // npr. 49,00 / 119616,00 (bez simbola)

    $order_number = $order->get_order_number();
    $purpose = "KUPOVINA PAKETA BR {$order_number}";

    // --- Primalac (fiksno, ali možeš menjati) ---
    $recipient_name = 'VLADIMIR VELJKOVIĆ PR DOTS';

    // --- Računi po valuti ---
    $recipient_account_rsd = '265-6660310001092-13';
    $recipient_iban_eur    = 'RS35265100000003681027';
    $recipient_bic_eur     = 'RZBSRSBG';

    // --- Šifra plaćanja po valuti/metodu (prilagodi kako želiš) ---
    // Ako želiš RSD=189, promeni 'RSD' => '189'
    $payment_code_by_currency = [
        'RSD' => '221',
        'EUR' => '221',
    ];
    $payment_code = $payment_code_by_currency[$currency] ?? '221';

    // Ako ti je valuta nešto treće, ne prikazuj slip (ili prikazuj default)
    if (!in_array($currency, ['RSD', 'EUR'], true)) return;

    // --- UI ---
    ?>
    <section class="raspitajse-payment-slip" style="margin:30px 0;padding:20px;border:2px solid #F5F7FC;border-radius:12px;background:#fafffb;">
        <h4 style="margin:0 0 12px;">Nalog za uplatu</h4>

        <div style="display:flex;gap:16px;flex-wrap:nowrap;margin-bottom:12px;align-items:flex-start;" class="payment-row">

            <!-- Platilac -->
            <div style="flex:3;">
                <div style="font-size:13px;color:#666;margin-bottom:4px;">Platilac</div>
                <div style="padding:10px 12px;border:1px solid #e6e6e6;border-radius:8px;background:#fff;">
                    <?php echo esc_html($billing_company); ?>
                </div>
            </div>

            <!-- Šifra plaćanja -->
            <div style="flex:1;">
                <div style="font-size:13px;color:#666;margin-bottom:4px;">Šifra plaćanja</div>
                <div style="padding:10px 12px;border:1px solid #e6e6e6;border-radius:8px;background:#fff;text-align:center;">
                    <?php echo esc_html($payment_code); ?>
                </div>
            </div>

            <!-- Valuta -->
            <div style="flex:1;">
                <div style="font-size:13px;color:#666;margin-bottom:4px;">Valuta</div>
                <div style="padding:10px 12px;border:1px solid #e6e6e6;border-radius:8px;background:#fff;text-align:center;">
                    <?php echo esc_html($currency); ?>
                </div>
            </div>

            <!-- Iznos -->
            <div style="flex:1.5;">
                <div style="font-size:13px;color:#666;margin-bottom:4px;">Iznos</div>
                <div style="padding:10px 12px;border:1px solid #e6e6e6;border-radius:8px;background:#fff;text-align:center;">
                    <?php echo esc_html($amount); ?>
                </div>
            </div>

        </div>

        <div style="display:flex;gap:20px;flex-wrap:wrap;">

            <!-- Svrha plaćanja -->
            <div style="flex:1;">
                <div style="font-size:13px;color:#666;margin-bottom:4px;">Svrha plaćanja</div>
                <div style="padding:10px 12px;border:1px solid #e6e6e6;border-radius:8px;background:#fff;">
                    <?php echo esc_html($purpose); ?>
                </div>

                <!-- Primalac (tražio si: ispod svrhe plaćanja) -->
                <div style="font-size:13px;color:#666;margin:12px 0 4px;">Primalac</div>
                <div style="padding:10px 12px;border:1px solid #e6e6e6;border-radius:8px;background:#fff;">
                    <?php echo esc_html($recipient_name); ?>
                </div>
            </div>

            <!-- Račun / IBAN + BIC (za EUR) -->
            <div style="flex:1;">
                <?php if ($currency === 'RSD') : ?>

                    <div style="font-size:13px;color:#666;margin-bottom:4px;">Račun primaoca</div>
                    <div style="padding:10px 12px;border:1px solid #e6e6e6;border-radius:8px;background:#fff;">
                        <?php echo esc_html($recipient_account_rsd); ?>
                    </div>

                <?php else : // EUR ?>

                    <div style="font-size:13px;color:#666;margin-bottom:4px;">IBAN primaoca</div>
                    <div style="padding:10px 12px;border:1px solid #e6e6e6;border-radius:8px;background:#fff;">
                        <?php echo esc_html($recipient_iban_eur); ?>
                    </div>

                    <div style="font-size:13px;color:#666;margin:12px 0 4px;">BIC / SWIFT</div>
                    <div style="padding:10px 12px;border:1px solid #e6e6e6;border-radius:8px;background:#fff;">
                        <?php echo esc_html($recipient_bic_eur); ?>
                    </div>

                <?php endif; ?>
            </div>

        </div>

        <p style="margin:12px 0 0;color:#666;font-size:13px;">
            Uplatu možete izvršiti putem e-banking servisa ili popunjavanjem naloga za uplatu.
        </p>
    </section>
    <?php
}


/**
 * =========================================================
 * WooCommerce – Smart QR Code (RSD IPS / EUR Info)
 * =========================================================
 */
add_action( 'woocommerce_thankyou', 'raspitajse_add_smart_qr_code', 2 );

function raspitajse_add_smart_qr_code( $order_id ) {

    if ( ! $order_id ) {
        return;
    }

    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }

    $payment_method = $order->get_payment_method();
    $amount = number_format( (float) $order->get_total(), 2, ',', '' );

    echo '<section class="raspitajse-qr-container" style="padding:25px;border:2px solid #F5F7FC;text-align:center;border-radius:12px;background:#fafffb;">';

    /* =====================================================
     * RSD – PRAVI IPS QR
     * payment_method = bank_transfer_1
     * ===================================================== */
    if ( $payment_method === 'bank_transfer_1' ) {

        $qr_payload =
            "K:PR|V:01|C:1|" .
            "R:265666031000109213|" .
            "N:VLADIMIR VELJKOVIĆ PR DOTS|" .
            "I:RSD{$amount}|" .
            "SF:221|" .
            "S:KUPOVINA PAKETA BR {$order_id}";

        $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode( $qr_payload );

        echo '<h3>📲 Platite skeniranjem QR koda (IPS – RSD)</h3>';
        echo '<p>Skenirajte QR kod u vašoj bankarskoj aplikaciji. Svi podaci će biti automatski popunjeni.</p>';
        echo '<img src="' . esc_url( $qr_url ) . '" width="300" height="300" alt="IPS QR">';
        echo '<p style="margin-top:15px;font-size:14px;color:#666;">Iznos: <strong>' . esc_html( $amount ) . ' RSD</strong></p>';
    }

    /* =====================================================
     * EUR – INFORMATIVNI QR
     * payment_method = bacs
     * ===================================================== */
    elseif ( $payment_method === 'bacs' ) {

        $info_text =
            "Order: {$order_id}\n" .
            "Recipient: VLADIMIR VELJKOVIC PR DOTS\n" .
            "IBAN: RS35265100000003681027\n" .
            "BIC: RZBSRSBG\n" .
            "Amount: {$amount} EUR";

        $info_qr = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode( $info_text );

        echo '<h3>🌍 International Payment (EUR)</h3>';
        echo '<p>Scan to copy payment details (manual entry required).</p>';
        echo '<img src="' . esc_url( $info_qr ) . '" width="250" height="250" alt="EUR Info QR">';
        echo '<p style="font-size:13px;color:#666;">This QR code is for information only.</p>';
    }

    echo '</section>';
}

/**
 * WP Overnight PDF Invoices:
 * - Attach invoice only for paid orders (total > 0)
 * - Attach only to Completed/Processing customer emails
 */
add_filter('wpo_wcpdf_attach_invoice', function ($attach, $order, $email_id = '') {

    if (!$order || !is_a($order, 'WC_Order')) {
        return false;
    }

    // Ne šalji fakturu za besplatne porudžbine
    if ((float) $order->get_total() <= 0) {
        return false;
    }

    // Kači samo na ove emailove
    $allowed_emails = ['customer_completed_order', 'customer_processing_order'];

    // Ako plugin šalje $email_id, filtriramo
    if (!empty($email_id) && !in_array($email_id, $allowed_emails, true)) {
        return false;
    }

    return $attach;

}, 10, 3);

add_action( 'woocommerce_admin_order_data_after_billing_address', function ( $order ) {
    echo '<pre>';
    print_r( $order->get_meta_data() );
    echo '</pre>';
});

