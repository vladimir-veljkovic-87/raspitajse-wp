<?php
/** Deterministic cross-feature release-candidate harness for Task 2.67. */
declare( strict_types=1 );

define( 'ABSPATH', __DIR__ . '/' );
define( 'DAY_IN_SECONDS', 86400 );
define( 'WP_JOB_BOARD_PRO_CANDIDATE_PREFIX', '_candidate_' );
define( 'WP_JOB_BOARD_PRO_APPLICANT_PREFIX', '_applicant_' );

$GLOBALS['hooks'] = array();
$GLOBALS['posts'] = array();
$GLOBALS['meta'] = array();
$GLOBALS['terms'] = array();
$GLOBALS['job_terms'] = array();
$GLOBALS['users'] = array();
$GLOBALS['current_user'] = 0;
$GLOBALS['logged_in'] = true;
$GLOBALS['enqueued'] = array();
$GLOBALS['localized'] = array();
$GLOBALS['mail'] = array();
$GLOBALS['application_notices'] = array();
$GLOBALS['observations'] = array();
$GLOBALS['external_paths'] = array( 'package' => 0, 'order' => 0, 'cart' => 0, 'checkout' => 0, 'payment' => 0, 'scheduler' => 0, 'http' => 0 );
$GLOBALS['options'] = array(
    'raspitajse_free_job_launch_enabled' => '1',
    'raspitajse_communications_transport_enabled' => '0',
    'date_format' => 'Y-m-d',
);
$GLOBALS['pm_options'] = array(
    'user_notice_add_new_message' => true,
    'user_notice_add_new_message_subject' => 'Vendor initial',
    'user_notice_replied_message_subject' => 'Vendor reply',
    'message_dashboard_page_id' => 1701,
);
$_POST = array();
$_COOKIE = array();

final class WP_Error {
    private $code; private $message;
    public function __construct( $code = '', $message = '' ) { $this->code = (string) $code; $this->message = (string) $message; }
    public function get_error_code() { return $this->code; }
    public function get_error_message() { return $this->message; }
}
final class WP_Post {
    public $ID; public $post_type; public $post_status; public $post_author; public $post_title; public $post_parent; public $post_content;
    public function __construct( $id, $type, $status = 'publish', $author = 0, $title = '', $parent = 0 ) {
        $this->ID = $id; $this->post_type = $type; $this->post_status = $status; $this->post_author = $author; $this->post_title = $title; $this->post_parent = $parent; $this->post_content = 'PRIVATE_CONTENT';
    }
}
final class Test_Json_Response extends Exception { public $success; public $data; public function __construct( $success, $data ) { $this->success = $success; $this->data = $data; } }

function callback_id( $callback ) { return is_array( $callback ) ? implode( '::', array_map( 'strval', $callback ) ) : ( is_string( $callback ) ? $callback : spl_object_hash( $callback ) ); }
function add_filter( $hook, $callback, $priority = 10, $accepted = 1 ) { $GLOBALS['hooks'][ $hook ][ (int) $priority ][ callback_id( $callback ) ] = array( $callback, (int) $accepted ); return true; }
function add_action( $hook, $callback, $priority = 10, $accepted = 1 ) { return add_filter( $hook, $callback, $priority, $accepted ); }
function remove_action( $hook, $callback, $priority = 10 ) { $id = callback_id( $callback ); if ( isset( $GLOBALS['hooks'][ $hook ][ (int) $priority ][ $id ] ) ) { unset( $GLOBALS['hooks'][ $hook ][ (int) $priority ][ $id ] ); return true; } return false; }
function remove_filter( $hook, $callback, $priority = 10 ) { return remove_action( $hook, $callback, $priority ); }
function apply_filters( $hook, $value, ...$args ) {
    if ( 'raspitajse_application_status_notification_transport' === $hook ) { $GLOBALS['application_notices'][] = $args[0]; return true; }
    if ( empty( $GLOBALS['hooks'][ $hook ] ) ) { return $value; }
    ksort( $GLOBALS['hooks'][ $hook ], SORT_NUMERIC );
    foreach ( $GLOBALS['hooks'][ $hook ] as $entries ) { foreach ( $entries as $entry ) { $value = call_user_func_array( $entry[0], array_slice( array_merge( array( $value ), $args ), 0, $entry[1] ) ); } }
    return $value;
}
function do_action( $hook, ...$args ) {
    if ( false !== strpos( $hook, '_observation' ) ) { $GLOBALS['observations'][] = array( $hook, $args[0] ?? array() ); }
    if ( empty( $GLOBALS['hooks'][ $hook ] ) ) { return; }
    ksort( $GLOBALS['hooks'][ $hook ], SORT_NUMERIC );
    foreach ( $GLOBALS['hooks'][ $hook ] as $entries ) { foreach ( $entries as $entry ) { call_user_func_array( $entry[0], array_slice( $args, 0, $entry[1] ) ); } }
}
function register_activation_hook() {} function register_deactivation_hook() {}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function __( $text ) { return $text; } function _n( $one, $many, $number ) { return 1 === (int) $number ? $one : $many; }
function esc_html__( $text ) { return $text; } function esc_attr__( $text ) { return $text; }
function esc_html( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); } function esc_attr( $value ) { return esc_html( $value ); }
function wp_strip_all_tags( $value ) { return strip_tags( (string) $value ); } function remove_accents( $value ) { return (string) $value; }
function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $value ) ); }
function sanitize_text_field( $value ) { return trim( preg_replace( '/\s+/', ' ', strip_tags( (string) $value ) ) ); }
function sanitize_textarea_field( $value ) { return sanitize_text_field( $value ); }
function absint( $value ) { return abs( (int) $value ); } function wp_unslash( $value ) { return $value; }
function wp_json_encode( $value ) { return json_encode( $value ); }
function wp_get_environment_type() { return 'staging'; }
function get_option( $key, $default = false ) { return array_key_exists( $key, $GLOBALS['options'] ) ? $GLOBALS['options'][ $key ] : $default; }
function update_option( $key, $value ) { $GLOBALS['options'][ $key ] = $value; return true; }
function delete_option( $key ) { unset( $GLOBALS['options'][ $key ] ); return true; }
function wp_private_message_get_option( $key ) { return $GLOBALS['pm_options'][ $key ] ?? false; }
function is_user_logged_in() { return $GLOBALS['logged_in']; } function get_current_user_id() { return $GLOBALS['current_user']; }
function current_user_can( $capability ) { return 99 === (int) $GLOBALS['current_user'] || 'edit_posts' === $capability; }
function user_can( $user_id, $capability, ...$args ) { return 99 === (int) $user_id; }
function wp_timezone() { return new DateTimeZone( 'UTC' ); } function current_datetime() { return new DateTimeImmutable( '2026-10-05 10:00:00', new DateTimeZone( 'UTC' ) ); }
function get_current_blog_id() { return 1; }
function taxonomy_exists( $taxonomy ) { return 'job_listing_category' === $taxonomy; }
function get_taxonomy( $taxonomy ) { return taxonomy_exists( $taxonomy ) ? (object) array( 'object_type' => array( 'job_listing' ) ) : null; }
function get_term( $id, $taxonomy ) { return isset( $GLOBALS['terms'][ (int) $id ] ) && $GLOBALS['terms'][ (int) $id ]->taxonomy === $taxonomy ? clone $GLOBALS['terms'][ (int) $id ] : new WP_Error( 'missing_term' ); }
function get_terms( $args ) { return 'job_listing_category' === ( $args['taxonomy'] ?? '' ) ? array_values( $GLOBALS['terms'] ) : new WP_Error( 'wrong_taxonomy' ); }
function get_post( $id ) { return $GLOBALS['posts'][ (int) $id ] ?? null; }
function get_post_type( $id ) { $post = get_post( $id ); return $post ? $post->post_type : ''; }
function get_post_field( $field, $id ) { $post = get_post( $id ); return $post && isset( $post->$field ) ? $post->$field : ''; }
function get_the_title( $id ) { return (string) get_post_field( 'post_title', $id ); }
function get_post_meta( $id, $key, $single = false ) { $exists = array_key_exists( $key, $GLOBALS['meta'][ (int) $id ] ?? array() ); if ( ! $exists ) { return $single ? '' : array(); } $value = $GLOBALS['meta'][ (int) $id ][ $key ]; return $single ? $value : array( $value ); }
function add_post_meta( $id, $key, $value, $unique = false ) { if ( $unique && array_key_exists( $key, $GLOBALS['meta'][ (int) $id ] ?? array() ) ) { return false; } $GLOBALS['meta'][ (int) $id ][ $key ] = $value; return true; }
function update_post_meta( $id, $key, $value, $previous = null ) { $old = $GLOBALS['meta'][ (int) $id ][ $key ] ?? ''; if ( null !== $previous && $old !== $previous ) { return false; } if ( $old === $value ) { return false; } $GLOBALS['meta'][ (int) $id ][ $key ] = $value; return true; }
function delete_post_meta( $id, $key ) { unset( $GLOBALS['meta'][ (int) $id ][ $key ] ); return true; }
function wp_get_post_terms( $id, $taxonomy, $args = array() ) { if ( 'job_listing_category' !== $taxonomy ) { return new WP_Error(); } $ids = $GLOBALS['job_terms'][ (int) $id ] ?? array(); return ( $args['fields'] ?? '' ) === 'ids' ? $ids : array_map( function ( $term_id ) use ( $taxonomy ) { return get_term( $term_id, $taxonomy ); }, $ids ); }
function wp_set_object_terms( $id, $terms, $taxonomy, $append = false ) { if ( 'job_listing_category' !== $taxonomy || $append ) { return new WP_Error(); } $GLOBALS['job_terms'][ (int) $id ] = array_map( 'intval', (array) $terms ); return $GLOBALS['job_terms'][ (int) $id ]; }
function get_posts( $args ) { $ids = array(); foreach ( $GLOBALS['posts'] as $id => $post ) { if ( $post->post_type !== ( $args['post_type'] ?? '' ) || 'trash' === $post->post_status ) { continue; } $match = true; foreach ( $args['meta_query'] ?? array() as $clause ) { if ( is_array( $clause ) && isset( $clause['key'] ) && (string) get_post_meta( $id, $clause['key'], true ) !== (string) $clause['value'] ) { $match = false; } } if ( $match ) { $ids[] = $id; } } return array_slice( $ids, 0, $args['posts_per_page'] ?? count( $ids ) ); }
function get_userdata( $id ) { return $GLOBALS['users'][ (int) $id ] ?? false; } function is_email( $email ) { return false !== filter_var( $email, FILTER_VALIDATE_EMAIL ); }
function get_permalink( $id ) { return 1701 === (int) $id ? 'https://stage.example.test/poruke/' : 'https://stage.example.test/dashboard/'; }
function home_url( $path = '/' ) { return 'https://stage.example.test' . $path; }
function add_query_arg( $key, $value, $url ) { return $url . ( false === strpos( $url, '?' ) ? '?' : '&' ) . rawurlencode( (string) $key ) . '=' . rawurlencode( (string) $value ); }
function remove_query_arg( $key, $url ) { return strtok( $url, '?' ); } function esc_url_raw( $url ) { return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : ''; }
function wp_date( $format, $timestamp ) { return gmdate( $format, $timestamp ); }
function wp_create_nonce( $action ) { return 'nonce-' . $action; } function wp_verify_nonce() { return true; }
function wp_send_json_error( $data ) { throw new Test_Json_Response( false, $data ); } function wp_send_json_success( $data ) { throw new Test_Json_Response( true, $data ); }
function wp_enqueue_script( $handle ) { $GLOBALS['enqueued'][ $handle ] = ( $GLOBALS['enqueued'][ $handle ] ?? 0 ) + 1; }
function wp_enqueue_style( $handle ) { $GLOBALS['enqueued'][ $handle ] = ( $GLOBALS['enqueued'][ $handle ] ?? 0 ) + 1; }
function wp_localize_script( $handle, $name, $data ) { $GLOBALS['localized'][ $handle ] = array( $name, $data ); }
function plugins_url( $path ) { return $path; } function plugin_dir_url() { return '/plugin/'; } function admin_url( $path ) { return 'https://stage.example.test/' . $path; }
function register_post_meta() { return true; }
function wp_next_scheduled() { return false; } function wp_schedule_event() { $GLOBALS['external_paths']['scheduler']++; return true; } function wp_clear_scheduled_hook() { return true; }
function wp_generate_uuid4() { return 'synthetic-uuid'; }
function wp_mail( $to, $subject, $message, $headers = array(), $attachments = array() ) { $atts = apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) ); $pre = apply_filters( 'pre_wp_mail', null, $atts ); if ( null !== $pre ) { return $pre; } $GLOBALS['mail'][] = $atts; return true; }
function wp_job_board_pro_get_option( $key, $default = false ) { return 'user_dashboard_page_id' === $key ? 1702 : $default; }
function cmb2_get_metabox() { return new class { public function nonce() { return 'nonce'; } }; }

final class Test_WPDB {
    public $posts = 'wp_posts'; public $postmeta = 'wp_postmeta';
    public function prepare( $query, ...$args ) { return array( 'query' => $query, 'args' => $args ); }
    public function get_var( $prepared ) { return 1; }
    public function get_col( $prepared ) { $args = $prepared['args']; $exclude = (int) $args[3]; $profile = (int) $args[4]; $user = (int) $args[5]; $ids = array(); foreach ( $GLOBALS['posts'] as $id => $post ) { if ( 'job_listing' !== $post->post_type || 'publish' !== $post->post_status || $exclude === $id ) { continue; } $owner = (int) get_post_meta( $id, '_job_employer_posted_by', true ); if ( $owner === $profile || ( ! $owner && $post->post_author === $user ) ) { $ids[] = $id; } } sort( $ids ); return $ids; }
}
$GLOBALS['wpdb'] = new Test_WPDB();

class WP_Job_Board_Pro_User {
    public static function get_user_id( $id = 0 ) { $id = $id ?: get_current_user_id(); return 22 === (int) $id ? 21 : (int) $id; }
    public static function get_candidate_by_user_id( $id ) { return array( 11 => 1101, 12 => 1102 )[ (int) $id ] ?? 0; }
    public static function get_user_by_candidate_id( $id ) { return array( 1101 => 11, 1102 => 12 )[ (int) $id ] ?? 0; }
    public static function get_employer_by_user_id( $id ) { return array( 21 => 1201, 23 => 1202 )[ (int) $id ] ?? 0; }
    public static function get_user_by_employer_id( $id ) { return array( 1201 => 21, 1202 => 23 )[ (int) $id ] ?? 0; }
    public static function is_employer( $id = 0 ) { return in_array( (int) $id, array( 21, 23 ), true ); }
    public static function is_employee( $id = 0 ) { return 22 === (int) $id; }
    public static function is_employee_can_edit_job( $job, $id ) { return 22 === (int) $id && 1301 === (int) $job; }
}
class WP_Job_Board_Pro_Job_Listing { public static function get_author_id( $job ) { $post = get_post( $job ); return $post ? $post->post_author : 0; } }

require dirname( __DIR__, 3 ) . '/mu-plugins/raspitajse-candidate-desired-positions.php';
require dirname( __DIR__ ) . '/raspitajse-commerce.php';
require dirname( __DIR__, 2 ) . '/raspitajse-communications/raspitajse-communications.php';

function fail_test( $message ) { fwrite( STDERR, 'FAIL: ' . $message . PHP_EOL ); exit( 1 ); }
function ok( $condition, $message ) { if ( ! $condition ) { fail_test( $message ); } }
function same( $expected, $actual, $message ) { if ( $expected !== $actual ) { fail_test( $message . ' expected=' . var_export( $expected, true ) . ' actual=' . var_export( $actual, true ) ); } }
function term( $id, $name, $parent ) { return (object) array( 'term_id' => $id, 'name' => $name, 'parent' => $parent, 'taxonomy' => 'job_listing_category' ); }
function add_job( $id, $status, $position = 501, $workers = 1 ) { $GLOBALS['posts'][ $id ] = new WP_Post( $id, 'job_listing', $status, 21, 'Synthetic job' ); $GLOBALS['meta'][ $id ]['_job_employer_posted_by'] = 1201; $GLOBALS['meta'][ $id ]['_job_expiry_date'] = ''; $GLOBALS['meta'][ $id ]['_raspitajse_workers_needed'] = $workers; $GLOBALS['job_terms'][ $id ] = array( $position ); }
function hook_count( $hook ) { $count = 0; foreach ( $GLOBALS['hooks'][ $hook ] ?? array() as $entries ) { $count += count( $entries ); } return $count; }

$GLOBALS['terms'] = array( 500 => term( 500, 'Root', 0 ), 501 => term( 501, 'Position A', 500 ), 502 => term( 502, 'Position B', 500 ), 503 => term( 503, 'Position C', 500 ), 504 => term( 504, 'Position D', 500 ) );
$GLOBALS['posts'][1101] = new WP_Post( 1101, 'candidate', 'publish', 11, 'Candidate' );
$GLOBALS['posts'][1102] = new WP_Post( 1102, 'candidate', 'publish', 12, 'Other candidate' );
$GLOBALS['posts'][1201] = new WP_Post( 1201, 'employer', 'publish', 21, 'Employer' );
$GLOBALS['posts'][1202] = new WP_Post( 1202, 'employer', 'publish', 23, 'Other employer' );
$GLOBALS['meta'][1101]['_candidate_user_id'] = 11;
$GLOBALS['meta'][1102]['_candidate_user_id'] = 12;
$GLOBALS['users'][11] = (object) array( 'ID' => 11, 'display_name' => 'Candidate', 'user_email' => 'candidate@example.test' );
$GLOBALS['users'][21] = (object) array( 'ID' => 21, 'display_name' => 'Employer', 'user_email' => 'employer@example.test' );

// Boot, namespace, channels and idempotence.
ok( class_exists( 'Raspitajse_Candidate_Desired_Positions' ) && class_exists( 'Raspitajse_Employer_Job_Posting' ) && class_exists( 'Raspitajse_Free_Job_Access_Policy' ) && class_exists( 'Raspitajse_Communications_Candidate_Application_Tracking' ) && class_exists( 'Raspitajse_Communications_Private_Message_Notifications' ), 'complete stack must load' );
ok( ! class_exists( 'WP_Private_Message_Message' ), 'optional vendor component may remain inactive' );
$before = hook_count( 'wp-private-message-after-add-message' );
Raspitajse_Communications_Private_Message_Notifications::boot();
same( $before, hook_count( 'wp-private-message-after-add-message' ), 'repeat boot must be idempotent' );
same( 'noreply-system@stage.raspitajse.com', Raspitajse_Communications_Sender_Policy::resolve( Raspitajse_Communications_Sender_Policy::CHANNEL_PRIVATE_MESSAGES )['from_email'], 'private message channel' );
same( 'noreply-candidates@stage.raspitajse.com', Raspitajse_Communications_Sender_Policy::resolve( Raspitajse_Communications_Sender_Policy::CHANNEL_APPLICATION_STATUS )['from_email'], 'application channel' );

// Candidate preferences and negative fourth/root cases.
$valid = Raspitajse_Candidate_Desired_Positions::validate_change( 1101, array( '501', '502', '503' ), 11, true, true );
ok( $valid['valid'], 'three desired child positions accepted' );
update_post_meta( 1101, Raspitajse_Candidate_Desired_Positions::META_KEY, $valid['ids'] );
$saved = get_post_meta( 1101, Raspitajse_Candidate_Desired_Positions::META_KEY, true );
$fourth = Raspitajse_Candidate_Desired_Positions::validate_change( 1101, array( '501', '502', '503', '504' ), 11, true, true );
ok( ! $fourth['valid'], 'fourth desired position rejected' ); same( $saved, get_post_meta( 1101, Raspitajse_Candidate_Desired_Positions::META_KEY, true ), 'first three unchanged' );
ok( ! Raspitajse_Candidate_Desired_Positions::validate_change( 1101, array( '500' ), 11, true, true )['valid'], 'candidate root position rejected' );
ok( ! Raspitajse_Candidate_Desired_Positions::validate_change( 1101, array( '501' ), 21, true, true )['valid'], 'employer has no candidate profile authority' );

// Employer job, canonical shared term, workers and quota 0/3 -> 1/3.
$GLOBALS['current_user'] = 21;
$quota0 = Raspitajse_Employer_Job_Posting::get_quota_presentation( 21 ); same( 0, $quota0['used'], 'quota starts 0/3' ); same( 3, $quota0['limit'], 'quota limit three' );
ok( Raspitajse_Employer_Job_Posting::validate_position( '501' )['valid'], 'employer child position accepted' );
ok( ! Raspitajse_Employer_Job_Posting::validate_position( '500' )['valid'], 'employer root rejected' );
ok( ! Raspitajse_Employer_Job_Posting::validate_workers( '0' )['valid'], 'invalid workers rejected' );
add_job( 1301, 'publish', 501, 2 );
$quota1 = Raspitajse_Employer_Job_Posting::get_quota_presentation( 21 ); same( 1, $quota1['used'], 'quota becomes 1/3' );
ok( Raspitajse_Candidate_Desired_Positions::job_matches( 1101, 1301 ), 'canonical position overlap matches' );
add_job( 1302, 'publish', 502 ); add_job( 1303, 'publish', 503 );
$data = array( 'post_type' => 'job_listing', 'post_status' => 'publish', 'post_author' => 21 );
$postarr = array( 'ID' => 1304, 'tax_input' => array( 'job_listing_category' => array( 504 ) ), 'meta_input' => array( '_job_employer_posted_by' => 1201, '_raspitajse_workers_needed' => '1', '_job_expiry_date' => '' ) );
$blocked = Raspitajse_Free_Job_Access_Policy::enforce_publication_quota( $data, $postarr, $postarr, false ); same( 'draft', $blocked['post_status'], 'fourth active job remains draft' );

// Application creation, duplicate, ownership, status surfaces and notifications.
$GLOBALS['posts'][1401] = new WP_Post( 1401, 'job_applicant', 'publish', 11, 'Application' );
$GLOBALS['meta'][1401]['_applicant_candidate_id'] = 1101; $GLOBALS['meta'][1401]['_applicant_job_id'] = 1301;
ok( Raspitajse_Communications_Candidate_Application_Tracking::initialize_application( 1401, 1301, 1101 ), 'application starts submitted' );
same( 1401, Raspitajse_Communications_Candidate_Application_Tracking::find_application( 1101, 1301 ), 'duplicate resolves original' );
$_POST = array( 'job_id' => 1301 );
try { Raspitajse_Communications_Candidate_Application_Tracking::guard_insert_data( array( 'post_type' => 'job_applicant', 'post_author' => 11 ) ); fail_test( 'duplicate must stop' ); } catch ( Test_Json_Response $response ) { same( 'Već ste se prijavili na ovaj oglas.', $response->data['message'], 'duplicate localized message' ); }
foreach ( array( 'under_review', 'shortlisted', 'interview' ) as $next ) { $current = Raspitajse_Communications_Candidate_Application_Tracking::read_status( 1401 ); $result = Raspitajse_Communications_Candidate_Application_Tracking::change_status( 1401, $next, $current, 21 ); ok( $result['changed'], 'owning employer transition' ); $view = Raspitajse_Communications_Candidate_Application_Tracking::candidate_view_model( 1401, 11 ); same( $next, $view['status'], 'candidate sees committed status' ); }
same( 3, count( $GLOBALS['application_notices'] ), 'one notice for each real transition' );
$retry = Raspitajse_Communications_Candidate_Application_Tracking::change_status( 1401, 'interview', 'interview', 21 ); ok( ! $retry['changed'], 'same status retry idempotent' ); same( 3, count( $GLOBALS['application_notices'] ), 'retry sends none' );
ok( is_wp_error( Raspitajse_Communications_Candidate_Application_Tracking::candidate_view_model( 1401, 12 ) ), 'other candidate cannot read' );
ok( is_wp_error( Raspitajse_Communications_Candidate_Application_Tracking::change_status( 1401, 'hired', 'interview', 23 ) ), 'other employer cannot change' );
$result = Raspitajse_Communications_Candidate_Application_Tracking::change_status( 1401, 'hired', 'interview', 21 ); ok( $result['changed'], 'terminal transition accepted' );
ok( is_wp_error( Raspitajse_Communications_Candidate_Application_Tracking::change_status( 1401, 'rejected', 'hired', 21 ) ), 'terminal transition rejected' );

// Role-isolated rendering and assets.
$GLOBALS['enqueued'] = array(); $GLOBALS['current_user'] = 11; ob_start(); Raspitajse_Communications_Candidate_Application_Tracking::render_candidate_status( 1301 ); $candidate_html = ob_get_clean(); ok( false !== strpos( $candidate_html, 'raspitajse-application-status' ), 'candidate badge rendered' ); same( 0, $GLOBALS['enqueued']['raspitajse-application-tracking'] ?? 0, 'candidate read surface has no employer script' );
ob_start(); Raspitajse_Communications_Candidate_Application_Tracking::render_employer_controls( 1401 ); $candidate_controls = ob_get_clean(); same( '', $candidate_controls, 'candidate gets no employer controls' );
$GLOBALS['current_user'] = 21; ob_start(); Raspitajse_Communications_Candidate_Application_Tracking::render_employer_controls( 1401 ); $employer_controls = ob_get_clean(); ok( false !== strpos( $employer_controls, 'raspitajse-application-status-form' ), 'owner gets controls' ); same( 1, $GLOBALS['enqueued']['raspitajse-application-tracking'], 'employer script scoped to controls' );

// Initial private message and reply: one owned notice plus exact fallback suppression.
$GLOBALS['posts'][1501] = new WP_Post( 1501, 'private_message', 'publish', 11, 'Synthetic topic' ); $GLOBALS['meta'][1501]['_sender'] = 11; $GLOBALS['meta'][1501]['_recipient'] = 21;
do_action( 'wp-private-message-after-add-message', 1501, 21, 11 ); same( 1, count( $GLOBALS['mail'] ), 'initial owned notice' ); same( false, wp_mail( 'employer@example.test', 'Vendor initial', 'unsafe fallback' ), 'exact initial fallback suppressed' );
$GLOBALS['posts'][1502] = new WP_Post( 1502, 'private_message', 'publish', 21, 'Reply', 1501 );
do_action( 'wp-private-message-after-reply-message', 1502, 1501, 21 ); same( 2, count( $GLOBALS['mail'] ), 'reply owned notice' ); same( false, wp_mail( 'candidate@example.test', 'Vendor reply', 'unsafe fallback' ), 'exact reply fallback suppressed' );
same( true, wp_mail( 'system@example.test', 'Unrelated', 'safe unrelated' ), 'unrelated mail passes' ); same( 3, count( $GLOBALS['mail'] ), 'only exact fallbacks suppressed' );

foreach ( $GLOBALS['external_paths'] as $path => $count ) { same( 0, $count, $path . ' path must not run' ); }
fwrite( STDOUT, "PASS: STACKED_PRODUCT_JOURNEY_RELEASE_CANDIDATE_READY\n" );
