<?php
/** Deterministic offline harness for Task 2.66. */
declare( strict_types=1 );

define( 'ABSPATH', __DIR__ . '/' );
define( 'WP_JOB_BOARD_PRO_APPLICANT_PREFIX', '_applicant_' );

$GLOBALS['hooks'] = array();
$GLOBALS['posts'] = array();
$GLOBALS['meta'] = array();
$GLOBALS['users'] = array();
$GLOBALS['current_user'] = 0;
$GLOBALS['logged_in'] = false;
$GLOBALS['notifications'] = array();
$GLOBALS['notification_result'] = true;
$GLOBALS['observations'] = array();
$GLOBALS['valid_nonce'] = true;
$GLOBALS['locks'] = array();

final class WP_Error {
    private $code;
    private $message;
    public function __construct( $code = '', $message = '' ) { $this->code = $code; $this->message = $message; }
    public function get_error_code() { return $this->code; }
    public function get_error_message() { return $this->message; }
}
final class Test_Json_Response extends Exception { public $success; public $data; public function __construct( $success, $data ) { $this->success = $success; $this->data = $data; parent::__construct(); } }
final class Test_WPDB {
    public function prepare( $query, $value ) { return str_replace( '%s', (string) $value, $query ); }
    public function get_var( $query ) {
        if ( 0 === strpos( $query, 'SELECT GET_LOCK' ) ) {
            if ( ! empty( $GLOBALS['locks'][ $query ] ) ) { return '0'; }
            $GLOBALS['locks'][ $query ] = true;
            return '1';
        }
        if ( 0 === strpos( $query, 'SELECT RELEASE_LOCK' ) ) {
            $needle = substr( $query, strlen( "SELECT RELEASE_LOCK(" ), -1 );
            foreach ( array_keys( $GLOBALS["locks"] ) as $held ) { if ( false !== strpos( $held, $needle ) ) { unset( $GLOBALS["locks"][ $held ] ); } }
            return '1';
        }
        return null;
    }
}
$GLOBALS['wpdb'] = new Test_WPDB();

function add_action( $hook, $callback, $priority = 10, $accepted = 1 ) { $GLOBALS['hooks'][ $hook ][] = $callback; }
function add_filter( $hook, $callback, $priority = 10, $accepted = 1 ) { add_action( $hook, $callback, $priority, $accepted ); }
function remove_action() { return true; }
function apply_filters( $hook, $value, ...$args ) {
    if ( 'raspitajse_application_status_notification_transport' === $hook ) {
        $GLOBALS['notifications'][] = $args[0];
        return $GLOBALS['notification_result'];
    }
    return $value;
}
function do_action( $hook, ...$args ) { if ( 'raspitajse_application_status_notification_observation' === $hook ) { $GLOBALS['observations'][] = $args[0]; } }
function __( $text ) { return $text; }
function esc_html__( $text ) { return $text; }
function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $text ) { return esc_html( $text ); }
function absint( $value ) { return abs( (int) $value ); }
function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $value ) ); }
function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function wp_unslash( $value ) { return $value; }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function is_user_logged_in() { return $GLOBALS['logged_in']; }
function get_current_user_id() { return $GLOBALS['current_user']; }
function user_can( $user_id, $capability ) { return 99 === (int) $user_id; }
function get_current_blog_id() { return 1; }
function get_post( $id ) { return $GLOBALS['posts'][ (int) $id ] ?? null; }
function get_post_meta( $id, $key, $single = true ) { return $GLOBALS['meta'][ (int) $id ][ $key ] ?? ''; }
function add_post_meta( $id, $key, $value, $unique = false ) {
    if ( $unique && array_key_exists( $key, $GLOBALS['meta'][ (int) $id ] ?? array() ) ) { return false; }
    $GLOBALS['meta'][ (int) $id ][ $key ] = $value;
    return true;
}
function update_post_meta( $id, $key, $value, $previous = null ) {
    $id = (int) $id;
    $old = $GLOBALS['meta'][ $id ][ $key ] ?? '';
    if ( null !== $previous && $old !== $previous ) { return false; }
    if ( $old === $value ) { return false; }
    $GLOBALS['meta'][ $id ][ $key ] = $value;
    return true;
}
function get_posts( $args ) {
    $result = array();
    foreach ( $GLOBALS['posts'] as $id => $post ) {
        if ( 'job_applicant' !== $post->post_type || 'trash' === $post->post_status ) { continue; }
        $matches = true;
        foreach ( $args['meta_query'] as $clause ) {
            if ( ! is_array( $clause ) || ! isset( $clause['key'] ) ) { continue; }
            if ( (string) get_post_meta( $id, $clause['key'], true ) !== (string) $clause['value'] ) { $matches = false; }
        }
        if ( $matches ) { $result[] = $id; }
    }
    return array_slice( $result, 0, 1 );
}
function get_userdata( $id ) { return $GLOBALS['users'][ (int) $id ] ?? false; }
function is_email( $email ) { return false !== filter_var( $email, FILTER_VALIDATE_EMAIL ); }
function wp_job_board_pro_get_option( $key ) { return 700; }
function get_permalink( $id ) { return 'https://stage.example.test/dashboard/'; }
function home_url( $path = '/' ) { return 'https://stage.example.test' . $path; }
function add_query_arg( $key, $value, $url ) { return $url . '?' . rawurlencode( $key ) . '=' . rawurlencode( (string) $value ); }
function esc_url_raw( $url ) { return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : ''; }
function get_option( $key, $default = false ) { return 'Y-m-d'; }
function wp_date( $format, $timestamp ) { return gmdate( $format, $timestamp ); }
function wp_create_nonce( $action ) { return 'nonce-' . $action; }
function wp_verify_nonce( $nonce, $action ) { return $GLOBALS['valid_nonce']; }
function wp_send_json_error( $data ) { throw new Test_Json_Response( false, $data ); }
function wp_send_json_success( $data ) { throw new Test_Json_Response( true, $data ); }
function wp_enqueue_script() {}
function wp_localize_script() {}
function plugins_url( $path ) { return $path; }
function admin_url( $path ) { return 'https://stage.example.test/' . $path; }

final class WP_Job_Board_Pro_User {
    public static function get_candidate_by_user_id( $id ) { return array( 11 => 101, 12 => 102 )[ (int) $id ] ?? 0; }
    public static function get_user_by_candidate_id( $id ) { return array( 101 => 11, 102 => 12 )[ (int) $id ] ?? 0; }
    public static function get_user_id( $id = 0 ) { return 22 === (int) $id ? 21 : (int) $id; }
    public static function is_employer( $id = 0 ) { return in_array( (int) $id, array( 21, 23 ), true ); }
    public static function is_employee( $id = 0 ) { return 22 === (int) $id; }
    public static function is_employee_can_edit_job( $job, $id ) { return 22 === (int) $id && 201 === (int) $job; }
}
final class WP_Job_Board_Pro_Job_Listing { public static function get_author_id( $job ) { return 201 === (int) $job ? 21 : 23; } }
final class Raspitajse_Communications_Sender_Policy { const CHANNEL_APPLICATION_STATUS = 'application_status'; }
final class Raspitajse_Communications_Transport { public static function send() { throw new Exception( 'real transport forbidden' ); } }

require dirname( __DIR__ ) . '/includes/class-candidate-application-tracking.php';

function fail_test( $message ) { fwrite( STDERR, 'FAIL: ' . $message . PHP_EOL ); exit( 1 ); }
function ok( $condition, $message ) { if ( ! $condition ) { fail_test( $message ); } }
function same( $expected, $actual, $message ) { if ( $expected !== $actual ) { fail_test( $message . ' expected=' . var_export( $expected, true ) . ' actual=' . var_export( $actual, true ) ); } }
function post_object( $id, $type, $author = 0, $status = 'publish', $title = '' ) { return (object) array( 'ID' => $id, 'post_type' => $type, 'post_author' => $author, 'post_status' => $status, 'post_title' => $title ); }
function seed() {
    $GLOBALS['posts'] = array(
        101 => post_object( 101, 'candidate', 11 ),
        102 => post_object( 102, 'candidate', 12 ),
        201 => post_object( 201, 'job_listing', 21, 'publish', 'Bezbedan posao' ),
        301 => post_object( 301, 'job_applicant', 11 ),
    );
    $GLOBALS['meta'] = array( 301 => array( '_applicant_candidate_id' => 101, '_applicant_job_id' => 201 ) );
    $GLOBALS['users'] = array(
        11 => (object) array( 'ID' => 11, 'user_email' => 'candidate@example.test' ),
        12 => (object) array( 'ID' => 12, 'user_email' => 'other@example.test' ),
    );
    $GLOBALS['notifications'] = array();
    $GLOBALS['notification_result'] = true;
    $GLOBALS['observations'] = array();
    $GLOBALS['locks'] = array();
    $GLOBALS['logged_in'] = true;
    $GLOBALS['current_user'] = 21;
    $GLOBALS['valid_nonce'] = true;
    Raspitajse_Communications_Candidate_Application_Tracking::release_application_lock();
}
function set_status( $status ) {
    unset( $GLOBALS['meta'][301][ Raspitajse_Communications_Candidate_Application_Tracking::STATUS_META ] );
    unset( $GLOBALS['meta'][301][ Raspitajse_Communications_Candidate_Application_Tracking::CHANGED_META ] );
    foreach ( array_keys( $GLOBALS['meta'][301] ) as $key ) {
        if ( 0 === strpos( $key, Raspitajse_Communications_Candidate_Application_Tracking::NOTICE_META_PREFIX ) ) { unset( $GLOBALS['meta'][301][ $key ] ); }
    }
    if ( null !== $status ) { $GLOBALS['meta'][301][ Raspitajse_Communications_Candidate_Application_Tracking::STATUS_META ] = $status; }
    $GLOBALS['notifications'] = array();
}

// 1: authoritative creation and initial state.
seed();
ok( Raspitajse_Communications_Candidate_Application_Tracking::initialize_application( 301, 201, 101 ), 'first application must initialize' );
same( 'submitted', Raspitajse_Communications_Candidate_Application_Tracking::read_status( 301 ), 'initial status' );
same( 101, (int) get_post_meta( 301, '_applicant_candidate_id', true ), 'candidate relation preserved' );
same( 201, (int) get_post_meta( 301, '_applicant_job_id', true ), 'job relation preserved' );

// 2: sequential duplicate finds original without writes.
$before = $GLOBALS['meta'][301];
same( 301, Raspitajse_Communications_Candidate_Application_Tracking::find_application( 101, 201 ), 'duplicate must find original' );
same( $before, $GLOBALS['meta'][301], 'duplicate check must not mutate original' );
ok( false !== strpos( file_get_contents( dirname( __DIR__ ) . '/includes/class-candidate-application-tracking.php' ), 'Već ste se prijavili na ovaj oglas.' ), 'localized duplicate message required' );

// 2b: the ordinary direct vendor insert seam refuses the same pair before insert.
$_POST = array( 'job_id' => 201 );
$row_count = count( $GLOBALS['posts'] );
try {
    Raspitajse_Communications_Candidate_Application_Tracking::guard_insert_data(
        array( 'post_type' => 'job_applicant', 'post_author' => 11 )
    );
    fail_test( 'direct duplicate response expected' );
} catch ( Test_Json_Response $response ) {
    ok( ! $response->success, 'direct duplicate must be rejected' );
    same( 'Već ste se prijavili na ovaj oglas.', $response->data['message'], 'direct duplicate localized message' );
}
same( $row_count, count( $GLOBALS['posts'] ), 'direct duplicate creates zero rows' );

// 3: synchronized lock admits one request only and is releasable.
ok( Raspitajse_Communications_Candidate_Application_Tracking::acquire_application_lock( 101, 201 ), 'first synchronized attempt must lock' );
ok( ! Raspitajse_Communications_Candidate_Application_Tracking::acquire_application_lock( 101, 201 ), 'second synchronized attempt must be refused' );
Raspitajse_Communications_Candidate_Application_Tracking::release_application_lock();
ok( Raspitajse_Communications_Candidate_Application_Tracking::acquire_application_lock( 101, 201 ), 'released lock must be reusable' );
Raspitajse_Communications_Candidate_Application_Tracking::release_application_lock();
same( 1, count( array_filter( $GLOBALS['posts'], function ( $post ) { return 'job_applicant' === $post->post_type && 'trash' !== $post->post_status; } ) ), 'exactly one non-trash application' );

// 4-5: candidate sees only own safe model.
$GLOBALS['current_user'] = 11;
$model = Raspitajse_Communications_Candidate_Application_Tracking::candidate_view_model( 301, 11 );
same( 'Prijava poslata', $model['label'], 'own localized label' );
ok( is_wp_error( Raspitajse_Communications_Candidate_Application_Tracking::candidate_view_model( 301, 12 ) ), 'forged candidate read denied' );

// 6: every allowed edge works for owner or authorized linked employee.
$edges = array(
    'submitted' => array( 'under_review', 'shortlisted', 'interview', 'hired', 'rejected' ),
    'under_review' => array( 'shortlisted', 'interview', 'hired', 'rejected' ),
    'shortlisted' => array( 'interview', 'hired', 'rejected' ),
    'interview' => array( 'hired', 'rejected' ),
);
$index = 0;
foreach ( $edges as $from => $targets ) {
    foreach ( $targets as $to ) {
        set_status( $from );
        $actor = 0 === $index++ % 2 ? 21 : 22;
        $result = Raspitajse_Communications_Candidate_Application_Tracking::change_status( 301, $to, $from, $actor );
        ok( ! is_wp_error( $result ) && $result['changed'], 'allowed transition ' . $from . '>' . $to );
        same( $to, Raspitajse_Communications_Candidate_Application_Tracking::read_status( 301 ), 'transition persisted' );
        same( 1, count( $GLOBALS['notifications'] ), 'one notification per transition' );
    }
}

// 7: other employer, candidate, anonymous and forged relation write nothing.
foreach ( array( 23, 11, 0 ) as $actor ) {
    set_status( 'submitted' );
    $snapshot = $GLOBALS['meta'][301];
    ok( is_wp_error( Raspitajse_Communications_Candidate_Application_Tracking::change_status( 301, 'under_review', 'submitted', $actor ) ), 'unauthorized actor denied' );
    same( $snapshot, $GLOBALS['meta'][301], 'unauthorized actor writes nothing' );
}
set_status( 'submitted' );
$GLOBALS['meta'][301]['_applicant_job_id'] = 999;
$snapshot = $GLOBALS['meta'][301];
ok( is_wp_error( Raspitajse_Communications_Candidate_Application_Tracking::change_status( 301, 'under_review', 'submitted', 21 ) ), 'forged relation denied' );
same( $snapshot, $GLOBALS['meta'][301], 'forged relation writes nothing' );
$GLOBALS['meta'][301]['_applicant_job_id'] = 201;

// 8: invalid nonce, unknown/disallowed/stale requests write nothing.
set_status( 'submitted' );
$GLOBALS['current_user'] = 21;
$GLOBALS['valid_nonce'] = false;
$_POST = array( 'application_id' => 301, 'nonce' => 'bad', 'status' => 'under_review', 'expected_status' => 'submitted' );
try { Raspitajse_Communications_Candidate_Application_Tracking::ajax_change_status(); fail_test( 'invalid nonce response expected' ); } catch ( Test_Json_Response $response ) { ok( ! $response->success, 'invalid nonce rejected' ); }
same( 'submitted', Raspitajse_Communications_Candidate_Application_Tracking::read_status( 301 ), 'invalid nonce writes nothing' );
foreach ( array( array( 'bogus', 'submitted' ), array( 'submitted', 'under_review' ), array( 'under_review', 'shortlisted' ) ) as $case ) {
    set_status( 'submitted' );
    $result = Raspitajse_Communications_Candidate_Application_Tracking::change_status( 301, $case[0], $case[1], 21 );
    ok( is_wp_error( $result ), 'invalid/disallowed/stale request denied' );
    same( 'submitted', Raspitajse_Communications_Candidate_Application_Tracking::read_status( 301 ), 'denied request writes nothing' );
}

// 9-10: idempotence and terminal states.
set_status( 'under_review' );
$result = Raspitajse_Communications_Candidate_Application_Tracking::change_status( 301, 'under_review', 'under_review', 21 );
ok( ! $result['changed'], 'same-status is idempotent' );
same( 0, count( $GLOBALS['notifications'] ), 'same-status has no notice' );
foreach ( array( 'hired', 'rejected' ) as $terminal ) {
    set_status( $terminal );
    ok( is_wp_error( Raspitajse_Communications_Candidate_Application_Tracking::change_status( 301, 'under_review', $terminal, 21 ) ), 'terminal status rejects further changes' );
}

// 11-13: safe correct notification, environment link and duplicate callback idempotence.
set_status( 'submitted' );
$result = Raspitajse_Communications_Candidate_Application_Tracking::change_status( 301, 'interview', 'submitted', 21 );
same( 1, count( $GLOBALS['notifications'] ), 'real transition notice exactly once' );
$payload = $GLOBALS['notifications'][0];
same( 'candidate@example.test', $payload['to'], 'authoritative candidate recipient' );
ok( false !== strpos( $payload['message'], 'Bezbedan posao' ) && false !== strpos( $payload['message'], 'Poziv na razgovor' ), 'safe job and label included' );
ok( false !== strpos( $payload['message'], 'https://stage.example.test/dashboard/?application=301' ), 'environment-aware dashboard link' );
foreach ( array( 'CV_SECRET', 'BODY_SECRET', 'PRIVATE_NOTE', 'INTERNAL_REASON', 'candidate@example.test' ) as $secret ) { ok( false === strpos( $payload['subject'] . $payload['message'], $secret ), 'private data excluded' ); }
Raspitajse_Communications_Candidate_Application_Tracking::notify_candidate( 301, 'submitted', 'interview', gmdate( 'Y-m-d H:i:s' ) );
same( 1, count( $GLOBALS['notifications'] ), 'duplicate callback sends no second notice' );

// 14: sanitized notification failure does not roll back commit.
set_status( 'submitted' );
$GLOBALS['notification_result'] = false;
$result = Raspitajse_Communications_Candidate_Application_Tracking::change_status( 301, 'under_review', 'submitted', 21 );
same( 'under_review', Raspitajse_Communications_Candidate_Application_Tracking::read_status( 301 ), 'mail failure does not roll back status' );
same( array( 'application_id' => 301, 'result' => 'failed' ), $GLOBALS['observations'][0], 'failure observation is sanitized' );

// 15: unknown legacy state is safe and non-mutating.
seed();
$GLOBALS['meta'][301]['_applicant_app_status'] = 'vendor_unknown';
$before = $GLOBALS['meta'][301];
$model = Raspitajse_Communications_Candidate_Application_Tracking::candidate_view_model( 301, 11 );
same( 'Status nije dostupan', $model['label'], 'unknown legacy label is safe' );
same( $before, $GLOBALS['meta'][301], 'legacy read does not mutate' );

// 16: inactive vendor boot/load and empty list are safe.
ok( class_exists( 'Raspitajse_Communications_Candidate_Application_Tracking' ), 'owned module loads independently' );
$GLOBALS['posts'] = array();
same( 0, Raspitajse_Communications_Candidate_Application_Tracking::find_application( 101, 201 ), 'empty list safe' );

fwrite( STDOUT, "PASS: CANDIDATE_APPLICATION_TRACKING_READY\n" );
