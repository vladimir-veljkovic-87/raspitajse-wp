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
    if ( is_admin()
        && !( defined('DOING_AJAX') && DOING_AJAX ) // Dozvoli admin-ajax.php
        && ! current_user_can( 'manage_options' )
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
        get_stylesheet_directory_uri() . '/assets/js/phone-field.js', // Path to your JS file
        array('intl-tel-input-js'), // Make sure intl-tel-input.js is loaded first
        'a9a53425350b', // Deterministic version tied to the canonical asset bytes
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


$raspitajse_use_legacy_mail_transport =
    ! class_exists( 'Raspitajse_Communications_Transport' )
    || ! Raspitajse_Communications_Transport::is_enabled();

if ( $raspitajse_use_legacy_mail_transport ) {
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
    
    if ( empty( $phpmailer->From ) || strpos( $phpmailer->From, '@stage.raspitajse.com' ) === false ) {
        $phpmailer->From     = 'noreply@stage.raspitajse.com';
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

    return 'noreply-system@stage.raspitajse.com';
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
    $to       = defined('SMTP_FROM') ? SMTP_FROM : 'dr@stage.raspitajse.com';
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
}

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

        'View Dashboard' => 'Pogledaj kontrolnu tablu',

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
        '“%s” has been added to your cart.' => '„%s“ je dodat u vašu korpu.',
        'Terms and conditions' => 'Pogledaj Uslove korišćenja',
        'I have read and agree to the website ' => 'Pročitao sam i slažem se sa uslovima sajta',
        'Please read and accept the terms and conditions to proceed with your order.' => 'Molimo pročitajte i prihvatite uslove korišćenja da biste nastavili sa porudžbinom.',

        // --- Order table ---
        'Product' => 'Proizvod',
        'Subtotal' => 'Međuzbir',
        'Total' => 'Ukupno',
        'Payment' => 'Plaćanje',
        'Your cart is currently empty.' => 'Vaša korpa je trenutno prazna.',
        'Return to shop' => 'Povratak u prodavnicu',

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

        // --- Woocomerce emails ---
        // Order header
        'Order #%1$s'        => 'Porudžbina #%1$s',
        '[Order #%2$s'       => 'Porudžbina #%2$s',
        'Order #%s'          => 'Porudžbina #%s',

        // Totals
        'Subtotal:'         => 'Međuzbir:',
        'Payment method:'   => 'Način plaćanja:',
        'Total:'            => 'Ukupno:',

        // Addresses
        'Billing address'   => 'Adresa za naplatu',
        'Our bank details'   => 'Podaci naše banke',
        'Bank'              => 'Banka',
        'Account number'    => 'Broj računa',

        // Candidate page
        'Download CV'       => 'Preuzmi CV',
        'Invite'            => 'Pozovi',
        'About Candidate'   => 'O kandidatu',
        'Education'         => 'Obrazovanje',
        'Experience'        => 'Iskustvo',
        'Work & Experience' => 'Rad i iskustvo',
        'Professional Skills'  => 'Veštine',
        'Awards'            => 'Nagrade',
        'Send Message'      => 'Pošalji poruku',
        'Private Message'   => 'Privatna poruka',
        'View Profile'      => 'Pogledaj profil',
        'Comment'           => 'Komentar',
        'Comments'          => 'Komentari',
        'Reply'             => 'Odgovori' ,
        'Social Profiles'   => 'Društveni profili',
        'Related Candidates' => 'Povezani kandidati',
        'You must be logged in to post a review.' => 'Morate biti prijavljeni da biste ostavili recenziju.',

        // Employer page
        'Search by Keywords' => 'Pretraži po ključnim rečima',
        'Location'           => 'Lokacija',
        'Category'           => 'Kategorija',
        'Find Employers'     => 'Pronađite poslodavce',

        // Registration page
        'Candidate'          => 'Kandidat',
        'Employer'           => 'Poslodavac',

        // Jobs page
        'Save & Preview' => 'Sačuvaj i pregledaj',
        'Latitude' => 'Geografska širina',
        'Longitude' => 'Geografska dužina',
        'Internal' => 'Interna - prijava preko sajta',
        'External URL' => 'Eksterni URL',
        'By Email' => 'Preko emaila',
        'Call To Apply' => 'Poziv za prijavu',

        // Blog
        'Read more' => 'Pročitaj više',
        '#%s Comment' => '%s Komentara',
        'Recent News Articles' => 'Nedavni članci',
        'Fresh job related news content posted each day.' => 'Sveže vesti sa tržišta svakodnevno.',
        'Submit Comment' => 'Pošalji komentar',
        'Previous Post' => 'Prethodni članak',
        'Next Post' => 'Sledeći članak',
        'Leave a Comment' => 'Ostavite komentar',
        'Edit your profile' => 'Izmeni profil',
        'Log out?'  => 'Odjaviti se?',
        'Required fields are marked ' => 'Obavezna polja su označena ',
        'Your Comment' => 'Vaš komentar',
        'Logged in as %s' => 'Prijavljeni kao %s',

        // Footer
        'Thanks for shopping with us.' => 'Hvala vam na poverenju.',
        'You are not allowed to access this page.' => 'Nemate dozvolu za pristup ovoj stranici.',

        // Mobile menu
        'Post Job' => 'Objavi oglas',
        'Back' => 'Nazad',
    ];

    if (isset($map[$normalized])) {
        return (string) $map[$normalized];
    }

    return (string) $translated;
}

function raspitajse_translate_months_in_string( $date_string ) {
	$map = [
		'January'   => 'Januar',
		'February'  => 'Februar',
		'March'     => 'Mart',
		'April'     => 'April',
		'May'       => 'Maj',
		'June'      => 'Jun',
		'July'      => 'Jul',
		'August'    => 'Avgust',
		'September' => 'Septembar',
		'October'   => 'Oktobar',
		'November'  => 'Novembar',
		'December'  => 'Decembar',
	];

	$pattern = '/\b(' . implode( '|', array_keys( $map ) ) . ')\b/u';

	return preg_replace_callback( $pattern, function( $m ) use ( $map ) {
		return $map[ $m[1] ];
	}, $date_string );
}

add_filter( 'wp_date', function( $date ) {
	return raspitajse_translate_months_in_string( $date );
}, 10, 1 );

add_filter( 'date_i18n', function( $date ) {
	return raspitajse_translate_months_in_string( $date );
}, 10, 1 );


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
 * WooCommerce Emails – Custom footer text and translations
 * =========================================================
 */
add_filter( 'woocommerce_email_footer_text', function () {
    return 'RASPITAJSE — Izgrađeno u saradnji sa AI tehnologijom za bolje iskustvo zapošljavanja.';
});

add_filter( 'gettext', function( $translated, $text, $domain ) {

	if ( $domain !== 'woocommerce' ) {
		return $translated;
	}

	$replacements = [
		'Order'      => 'Porudžbina',
		'Order #'    => 'Porudžbina #',
	];

	// tačna zamena
	if ( isset( $replacements[ $text ] ) ) {
		return $replacements[ $text ];
	}

	// fallback: ako negde dođe kao "Order #" unutar većeg stringa
	if ( strpos( $text, 'Order #' ) !== false ) {
		return str_replace( 'Order #', 'Porudžbina #', $text );
	}

	return $translated;

}, 20, 3 );



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

    if ( ! class_exists( 'Raspitajse_Commerce' ) ) {
        return;
    }

    $user_id     = get_current_user_id();
    $employer_id = Raspitajse_Commerce::get_employer_id_by_user( $user_id );

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
