<?php
/**
 * Offline regression harness for the employer free job-posting completion.
 */

define( 'ABSPATH', __DIR__ );
define( 'DAY_IN_SECONDS', 86400 );

$GLOBALS['hooks']        = array();
$GLOBALS['terms']        = array();
$GLOBALS['posts']        = array();
$GLOBALS['postmeta']     = array();
$GLOBALS['job_terms']    = array();
$GLOBALS['current_user'] = 10;
$GLOBALS['is_admin_user'] = false;
$GLOBALS['lock_events']  = array();

function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
    $GLOBALS['hooks'][ $hook ][] = array( $callback, $priority, $accepted_args );
}
function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
    add_filter( $hook, $callback, $priority, $accepted_args );
}
function apply_filters( $hook, $value ) {
    return $value;
}
function do_action( $hook, ...$args ) {
    if ( 'raspitajse_free_job_quota_lock_event' === $hook ) {
        $GLOBALS['lock_events'][] = $args;
    }
}
function __( $text ) {
    return $text;
}
function _n( $single, $plural, $number ) {
    return 1 === (int) $number ? $single : $plural;
}
function esc_html( $value ) {
    return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}
function esc_attr( $value ) {
    return esc_html( $value );
}
function esc_html__( $text ) {
    return $text;
}
function absint( $value ) {
    return abs( (int) $value );
}
function wp_unslash( $value ) {
    return $value;
}
function wp_strip_all_tags( $value ) {
    return strip_tags( (string) $value );
}
function remove_accents( $value ) {
    return (string) $value;
}
function sanitize_key( $value ) {
    return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $value ) );
}
function current_user_can( $capability ) {
    return 'manage_options' === $capability ? $GLOBALS['is_admin_user'] : true;
}
function get_current_user_id() {
    return $GLOBALS['current_user'];
}
function get_current_blog_id() {
    return 1;
}
function wp_timezone() {
    return new DateTimeZone( 'UTC' );
}
function current_datetime() {
    return new DateTimeImmutable( '2026-10-05 10:00:00', new DateTimeZone( 'UTC' ) );
}
function taxonomy_exists( $taxonomy ) {
    return 'job_listing_category' === $taxonomy;
}
function get_taxonomy( $taxonomy ) {
    return taxonomy_exists( $taxonomy ) ? (object) array( 'object_type' => array( 'job_listing' ) ) : null;
}
function get_term( $term_id, $taxonomy ) {
    $term_id = (int) $term_id;
    if ( ! isset( $GLOBALS['terms'][ $term_id ] ) || $taxonomy !== $GLOBALS['terms'][ $term_id ]->taxonomy ) {
        return new WP_Error( 'missing_term' );
    }
    return clone $GLOBALS['terms'][ $term_id ];
}
function get_terms( $args ) {
    if ( ! is_array( $args ) || 'job_listing_category' !== ( $args['taxonomy'] ?? '' ) ) {
        return new WP_Error( 'wrong_taxonomy' );
    }
    return array_values(
        array_filter(
            $GLOBALS['terms'],
            static function ( $term ) {
                return 'job_listing_category' === $term->taxonomy;
            }
        )
    );
}
function get_post_type( $post_id ) {
    return isset( $GLOBALS['posts'][ $post_id ] ) ? $GLOBALS['posts'][ $post_id ]['post_type'] : '';
}
function get_post_field( $field, $post_id ) {
    return $GLOBALS['posts'][ $post_id ][ $field ] ?? '';
}
function get_post_meta( $post_id, $key, $single = false ) {
    $values = $GLOBALS['postmeta'][ $post_id ][ $key ] ?? array();
    if ( $single ) {
        return array_key_exists( 0, $values ) ? $values[0] : '';
    }
    return $values;
}
function update_post_meta( $post_id, $key, $value ) {
    $GLOBALS['postmeta'][ $post_id ][ $key ] = array( $value );
    return true;
}
function delete_post_meta( $post_id, $key ) {
    unset( $GLOBALS['postmeta'][ $post_id ][ $key ] );
    return true;
}
function wp_get_post_terms( $post_id, $taxonomy, $args = array() ) {
    if ( 'job_listing_category' !== $taxonomy ) {
        return new WP_Error( 'wrong_taxonomy' );
    }
    $ids = $GLOBALS['job_terms'][ $post_id ] ?? array();
    if ( isset( $args['fields'] ) && 'ids' === $args['fields'] ) {
        return $ids;
    }
    return array_map(
        static function ( $id ) use ( $taxonomy ) {
            return get_term( $id, $taxonomy );
        },
        $ids
    );
}
function wp_set_object_terms( $post_id, $terms, $taxonomy, $append = false ) {
    if ( 'job_listing_category' !== $taxonomy || $append ) {
        return new WP_Error( 'bad_write' );
    }
    $GLOBALS['job_terms'][ $post_id ] = array_values( array_map( 'intval', (array) $terms ) );
    return $GLOBALS['job_terms'][ $post_id ];
}
function register_post_meta() {
    return true;
}
function add_query_arg( $key, $value, $location ) {
    return $location . '?' . $key . '=' . $value;
}
function wp_json_encode( $value ) {
    return json_encode( $value );
}

class WP_Error {
    private $code;
    private $message;
    public function __construct( $code = '', $message = '' ) {
        $this->code = $code;
        $this->message = $message;
    }
    public function get_error_code() {
        return $this->code;
    }
    public function get_error_message() {
        return $this->message;
    }
}
function is_wp_error( $value ) {
    return $value instanceof WP_Error;
}
class WP_Post {
    public $ID;
    public $post_type;
    public $post_status;
    public function __construct( $id, $type, $status ) {
        $this->ID = $id;
        $this->post_type = $type;
        $this->post_status = $status;
    }
}

class WP_Job_Board_Pro_User {
    public static function get_user_id( $user_id = 0 ) {
        $user_id = $user_id ? (int) $user_id : get_current_user_id();
        return 11 === $user_id ? 10 : $user_id;
    }
    public static function get_employer_by_user_id( $user_id ) {
        $map = array( 10 => 100, 20 => 200 );
        return $map[ (int) $user_id ] ?? 0;
    }
    public static function get_user_by_employer_id( $employer_id ) {
        $map = array( 100 => 10, 200 => 20 );
        return $map[ (int) $employer_id ] ?? 0;
    }
}

class Test_WPDB {
    public $posts = 'wp_posts';
    public $postmeta = 'wp_postmeta';

    public function prepare( $query, ...$args ) {
        return array( 'query' => $query, 'args' => $args );
    }

    public function get_col( $prepared ) {
        $args       = $prepared['args'];
        $exclude_id = (int) $args[3];
        $profile_id = (int) $args[4];
        $user_id    = (int) $args[5];
        $ids        = array();

        foreach ( $GLOBALS['posts'] as $id => $post ) {
            if (
                'job_listing' !== $post['post_type']
                || 'publish' !== $post['post_status']
                || $exclude_id === (int) $id
            ) {
                continue;
            }
            $stored_profile = (int) get_post_meta( $id, '_job_employer_posted_by', true );
            if ( $stored_profile === $profile_id || ( ! $stored_profile && (int) $post['post_author'] === $user_id ) ) {
                $ids[] = (int) $id;
            }
        }
        sort( $ids );
        return $ids;
    }

    public function get_var( $prepared ) {
        return 1;
    }
}
$GLOBALS['wpdb'] = new Test_WPDB();

function test_fail( $message ) {
    fwrite( STDERR, "FAIL: {$message}\n" );
    exit( 1 );
}
function test_true( $value, $message ) {
    if ( true !== $value ) {
        test_fail( $message );
    }
}
function test_false( $value, $message ) {
    if ( false !== $value ) {
        test_fail( $message );
    }
}
function test_same( $expected, $actual, $message ) {
    if ( $expected !== $actual ) {
        test_fail( $message . ' expected=' . var_export( $expected, true ) . ' actual=' . var_export( $actual, true ) );
    }
}
function test_error_code( $code, $value, $message ) {
    test_true( is_wp_error( $value ), $message . ' must be WP_Error' );
    test_same( $code, $value->get_error_code(), $message . ' code' );
}
function term_record( $id, $name, $parent, $taxonomy = 'job_listing_category' ) {
    return (object) array(
        'term_id'  => $id,
        'name'     => $name,
        'parent'   => $parent,
        'taxonomy' => $taxonomy,
    );
}
function add_job( $id, $status, $owner = 100, $author = 10, $expiry = '', $terms = array( 101 ), $workers = 1 ) {
    $GLOBALS['posts'][ $id ] = array(
        'post_type'   => 'job_listing',
        'post_status' => $status,
        'post_author' => $author,
        'post_title'  => 'Naslov ' . $id,
        'post_content' => 'Sadržaj ' . $id,
    );
    if ( $owner ) {
        update_post_meta( $id, '_job_employer_posted_by', $owner );
    }
    update_post_meta( $id, '_job_expiry_date', $expiry );
    if ( null !== $workers ) {
        update_post_meta( $id, '_raspitajse_workers_needed', $workers );
    }
    $GLOBALS['job_terms'][ $id ] = $terms;
}
function set_active_count( $count ) {
    foreach ( array( 701, 702, 703 ) as $index => $id ) {
        $GLOBALS['posts'][ $id ]['post_status'] = $index < $count ? 'publish' : 'draft';
        update_post_meta( $id, '_job_expiry_date', '' );
    }
}
function finalize_policy_frame( $post_id, $data ) {
    Raspitajse_Free_Job_Access_Policy::after_insert_post(
        $post_id,
        new WP_Post( $post_id, 'job_listing', $data['post_status'] ),
        true,
        null
    );
}
function publish_request( $post_id = 0, $author = 10, $profile = 100, $position = array( 101 ), $workers = '1' ) {
    $data = array(
        'post_type'   => 'job_listing',
        'post_status' => 'publish',
        'post_author' => $author,
        'post_title'  => 'Sačuvan naslov',
        'post_content' => 'Sačuvan sadržaj',
    );
    $postarr = array(
        'ID'         => $post_id,
        'tax_input'  => array( 'job_listing_category' => $position ),
        'meta_input' => array(
            '_job_employer_posted_by' => $profile,
            '_raspitajse_workers_needed' => $workers,
            '_job_expiry_date' => '',
        ),
    );
    return array(
        Raspitajse_Free_Job_Access_Policy::enforce_publication_quota( $data, $postarr, $postarr, (bool) $post_id ),
        $postarr,
    );
}

$GLOBALS['terms'] = array(
    100 => term_record( 100, 'Tehnologija', 0 ),
    101 => term_record( 101, 'Backend', 100 ),
    102 => term_record( 102, 'QA', 100 ),
    200 => term_record( 200, 'Administracija', 0 ),
    201 => term_record( 201, 'Asistent', 200 ),
    300 => term_record( 300, 'Pogrešna taksonomija', 0, 'other_taxonomy' ),
);
$GLOBALS['posts'][100] = array( 'post_type' => 'employer', 'post_status' => 'publish', 'post_author' => 10 );
$GLOBALS['posts'][200] = array( 'post_type' => 'employer', 'post_status' => 'publish', 'post_author' => 20 );
add_job( 701, 'publish' );
add_job( 702, 'publish', 100, 10, '2026-12-31', array( 102 ), 2 );
add_job( 703, 'draft' );
add_job( 704, 'preview', 100, 10, '', array(), null );
add_job( 800, 'publish', 200, 20, '', array( 201 ), 1 );

require dirname( __DIR__ ) . '/includes/class-raspitajse-employer-job-posting.php';
require dirname( __DIR__ ) . '/includes/class-raspitajse-free-job-access-policy.php';
Raspitajse_Employer_Job_Posting::boot();

test_true( isset( $GLOBALS['hooks']['wp-job-board-pro-job_listing-fields'] ), 'front fields hook must be registered' );
test_true( isset( $GLOBALS['hooks']['cmb2_override__job_category_meta_value'] ), 'position read override must be registered' );
test_true( isset( $GLOBALS['hooks']['wp-job-board-pro-process-submission-after-save'] ), 'post-save hook must be registered' );
test_false( class_exists( 'WP_Job_Board_Pro_Submit_Form' ), 'vendor submit component intentionally remains inactive in harness' );

// Create form: one child-only selector, workers default 1, and authoritative used / 3 summary.
set_active_count( 2 );
$fields = Raspitajse_Employer_Job_Posting::normalize_front_fields(
    array(
        array( 'id' => '_job_title', 'type' => 'text' ),
        array( 'id' => '_job_category', 'type' => 'pw_taxonomy_multiselect' ),
        array( 'id' => 'legacy-workers', 'name' => 'Potreban broj radnika', 'type' => 'text' ),
        array( 'id' => '_job_post_type', 'type' => 'hidden' ),
    ),
    0
);
$ids = array_column( $fields, 'id' );
test_same( 1, count( array_keys( $ids, '_job_category', true ) ), 'form must contain one position field' );
$position_field = $fields[ array_search( '_job_category', $ids, true ) ];
$workers_field  = $fields[ array_search( '_raspitajse_workers_needed', $ids, true ) ];
$quota_field    = $fields[ array_search( '_raspitajse_active_job_quota', $ids, true ) ];
test_same( 'pw_select', $position_field['type'], 'position must be a single select' );
test_false( in_array( 'legacy-workers', $ids, true ), 'duplicate visible workers meaning must be removed' );
test_false( isset( $position_field['options'][100] ), 'root term must not be selectable' );
test_true( isset( $position_field['options'][101] ), 'child term must be selectable' );
test_same( 1, $workers_field['default'], 'new workers field must default to 1' );
test_same( 'Potreban broj radnika', $workers_field['name'], 'workers label must be exact' );
test_true( false !== strpos( $quota_field['name'], '2 od 3' ), 'quota summary must render used / 3' );

update_post_meta( 701, '_job_category', array( 102, 201 ) );
test_same( 101, Raspitajse_Employer_Job_Posting::override_position_value( null, 701, array( 'field_id' => '_job_category' ), null ), 'edit preselection must use canonical taxonomy, not stale meta' );
test_same( 'unchanged', Raspitajse_Employer_Job_Posting::override_position_value( 'unchanged', 701, array( 'field_id' => '_other' ), null ), 'override must be field-scoped' );

// Valid values persist and round-trip exactly through the owned post-save seam.
$_POST = array(
    'submit-cmb-job_listing' => '1',
    '_job_category' => '101',
    '_raspitajse_workers_needed' => '7',
);
Raspitajse_Employer_Job_Posting::persist_frontend_fields( 704 );
$requirements = Raspitajse_Employer_Job_Posting::get_job_requirements( 704 );
test_same( 101, $requirements['position_id'], 'valid position must round-trip' );
test_same( 7, $requirements['workers_needed'], 'valid workers count must round-trip' );
test_false( $requirements['repair_required'], 'valid requirements need no repair' );

// Position validation rejects missing/root/nonexistent/wrong-taxonomy/multiple/malformed.
foreach (
    array(
        null,
        array(),
        '100',
        '999',
        '300',
        array( '101', '102' ),
        array( '101', array( '102' ) ),
        '01',
        '-1',
        '1.5',
        'abc',
    ) as $invalid_position
) {
    test_false( Raspitajse_Employer_Job_Posting::validate_position( $invalid_position )['valid'], 'invalid position must reject' );
}
test_true( Raspitajse_Employer_Job_Posting::validate_position( '101' )['valid'], 'valid child must pass' );

// Workers validation rejects every non-canonical or out-of-range value.
foreach ( array( null, '', '0', 0, '-1', '1.0', '1.5', 'abc', array( '1' ), '01', '1001' ) as $invalid_workers ) {
    test_false( Raspitajse_Employer_Job_Posting::validate_workers( $invalid_workers )['valid'], 'invalid workers value must reject' );
}
test_same( 1000, Raspitajse_Employer_Job_Posting::validate_workers( '1000' )['value'], 'documented upper bound must pass' );

// Incomplete draft is preserved and receives explicit validation state without a quota lock.
$_POST = array();
$GLOBALS['lock_events'] = array();
$draft_data = array( 'post_type' => 'job_listing', 'post_status' => 'draft', 'post_author' => 10, 'post_title' => 'Nacrt', 'post_content' => 'Tekst' );
$draft_arr  = array( 'ID' => 704, 'tax_input' => array( 'job_listing_category' => array() ), 'meta_input' => array( '_raspitajse_workers_needed' => '' ) );
$draft_result = Raspitajse_Free_Job_Access_Policy::enforce_publication_quota( $draft_data, $draft_arr, $draft_arr, true );
test_same( 'draft', $draft_result['post_status'], 'incomplete draft must remain a draft' );
finalize_policy_frame( 704, $draft_result );
test_same( 'invalid_job_position', get_post_meta( 704, Raspitajse_Free_Job_Access_Policy::META_REASON, true ), 'draft must retain validation reason' );
test_same( array(), $GLOBALS['lock_events'], 'draft validation must not acquire quota lock' );
test_same( 'Nacrt', $draft_result['post_title'], 'draft title must be preserved' );
test_same( 'Tekst', $draft_result['post_content'], 'draft content must be preserved' );

// Valid draft remains available at 3 / 3 and clears only an obsolete content reason.
set_active_count( 3 );
$valid_draft_arr = array(
    'ID' => 704,
    'tax_input' => array( 'job_listing_category' => array( 101 ) ),
    'meta_input' => array( '_raspitajse_workers_needed' => '3' ),
);
$valid_draft = Raspitajse_Free_Job_Access_Policy::enforce_publication_quota( $draft_data, $valid_draft_arr, $valid_draft_arr, true );
test_same( 'draft', $valid_draft['post_status'], 'valid draft must remain saveable at 3 / 3' );
finalize_policy_frame( 704, $valid_draft );
test_same( '', get_post_meta( 704, Raspitajse_Free_Job_Access_Policy::META_REASON, true ), 'valid draft clears obsolete field reason' );

// New publication eligibility at 0 / 3, 1 / 3 and 2 / 3.
foreach ( array( 0, 1, 2 ) as $active_count ) {
    set_active_count( $active_count );
    list( $result ) = publish_request( 0 );
    test_same( 'publish', $result['post_status'], "publication must pass at {$active_count} / 3" );
    finalize_policy_frame( 900 + $active_count, $result );
}

// Fourth publication is refused by the existing quota gate, preserving content in draft.
set_active_count( 3 );
list( $fourth ) = publish_request( 0 );
test_same( 'draft', $fourth['post_status'], 'fourth publication must be downgraded to draft' );
test_same( 'Sačuvan naslov', $fourth['post_title'], 'blocked publish must preserve title' );
test_same( 'Sačuvan sadržaj', $fourth['post_content'], 'blocked publish must preserve content' );
finalize_policy_frame( 910, $fourth );
test_same( 'active_job_limit', get_post_meta( 910, Raspitajse_Free_Job_Access_Policy::META_REASON, true ), 'existing gate must own quota reason' );

// Editing the active current job at 3 / 3 excludes only itself and remains publishable.
list( $edit_active ) = publish_request( 703 );
test_same( 'publish', $edit_active['post_status'], 'active edit at 3 / 3 must not consume a fourth slot' );
finalize_policy_frame( 703, $edit_active );
$presentation = Raspitajse_Employer_Job_Posting::get_quota_presentation( 10, 703 );
test_same( 3, $presentation['used'], 'presentation used count includes current active job' );
test_true( $presentation['current_consumes_slot'], 'presentation marks current active slot' );

// Expiry/trash/unpublish consistently free slots through the same authoritative query.
$GLOBALS['posts'][701]['post_status'] = 'trash';
$GLOBALS['posts'][702]['post_status'] = 'publish';
update_post_meta( 702, '_job_expiry_date', '2026-10-04' );
$GLOBALS['posts'][703]['post_status'] = 'publish';
$reduced = Raspitajse_Employer_Job_Posting::get_quota_presentation( 10, 0 );
test_same( 1, $reduced['used'], 'trash and expired jobs must not consume slots' );
test_same( 2, $reduced['remaining'], 'remaining slots must follow authoritative query' );

// Linked employee shares parent quota; forged cross-employer job fails closed.
$GLOBALS['current_user'] = 11;
$employee = Raspitajse_Employer_Job_Posting::get_quota_presentation( 11, 0 );
test_same( $reduced['used'], $employee['used'], 'linked employee must share parent employer quota' );
test_error_code( 'cross_employer_ownership', Raspitajse_Employer_Job_Posting::get_quota_presentation( 11, 800 ), 'cross-employer job' );
$GLOBALS['current_user'] = 10;

// Content validation still blocks administrators and never delegates to quota for malformed fields.
$GLOBALS['is_admin_user'] = true;
list( $admin_invalid ) = publish_request( 704, 10, 100, array( 100 ), '2' );
test_same( 'draft', $admin_invalid['post_status'], 'admin public transition cannot bypass content validity' );
finalize_policy_frame( 704, $admin_invalid );
$GLOBALS['is_admin_user'] = false;

// Historical multi-position/malformed records are classified, never rewritten.
add_job( 720, 'draft', 100, 10, '', array( 101, 102 ), '1.5' );
$before_terms = $GLOBALS['job_terms'][720];
$historical = Raspitajse_Employer_Job_Posting::get_job_requirements( 720 );
test_true( $historical['repair_required'], 'historical malformed record must require repair' );
test_same( $before_terms, $GLOBALS['job_terms'][720], 'compatibility read must not mutate terms' );
test_same( '1.5', get_post_meta( 720, '_raspitajse_workers_needed', true ), 'compatibility read must not mutate worker meta' );

// The new owned module contains no package/payment execution path.
$source = file_get_contents( dirname( __DIR__ ) . '/includes/class-raspitajse-employer-job-posting.php' );
foreach ( array( 'woocommerce_', 'add_to_cart', 'checkout_create_order', 'entitlement' ) as $forbidden ) {
    test_false( false !== strpos( $source, $forbidden ), "owned form module must not contain {$forbidden}" );
}

echo "PASS: EMPLOYER_JOB_POSTING_COMPLETION_READY\n";
