<?php
/**
 * Offline regression harness for candidate desired-position preferences.
 */

declare( strict_types=1 );

define( 'ABSPATH', __DIR__ . '/' );
define( 'WP_JOB_BOARD_PRO_CANDIDATE_PREFIX', '_candidate_' );

$GLOBALS['test_hooks']       = array();
$GLOBALS['test_terms']       = array();
$GLOBALS['test_post_types']  = array();
$GLOBALS['test_meta']        = array();
$GLOBALS['test_job_terms']   = array();
$GLOBALS['test_caps']        = array();
$GLOBALS['test_candidate_map'] = array();
$GLOBALS['test_current_user'] = 0;
$GLOBALS['test_nonce_valid']  = true;
$GLOBALS['test_updates']      = array();
$GLOBALS['test_vendor_calls'] = 0;
$_SESSION = array();
$_POST    = array();

final class WP_Error {}

function is_wp_error( $value ) {
    return $value instanceof WP_Error;
}

function test_callback_equal( $left, $right ) {
    return $left === $right;
}

function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
    $GLOBALS['test_hooks'][ $hook ][ (int) $priority ][] = array( $callback, (int) $accepted_args );
    return true;
}

function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
    return add_filter( $hook, $callback, $priority, $accepted_args );
}

function remove_action( $hook, $callback, $priority = 10 ) {
    if ( empty( $GLOBALS['test_hooks'][ $hook ][ (int) $priority ] ) ) {
        return false;
    }
    foreach ( $GLOBALS['test_hooks'][ $hook ][ (int) $priority ] as $index => $entry ) {
        if ( test_callback_equal( $entry[0], $callback ) ) {
            unset( $GLOBALS['test_hooks'][ $hook ][ (int) $priority ][ $index ] );
            return true;
        }
    }
    return false;
}

function apply_filters( $hook, $value, ...$args ) {
    if ( empty( $GLOBALS['test_hooks'][ $hook ] ) ) {
        return $value;
    }
    $priorities = array_keys( $GLOBALS['test_hooks'][ $hook ] );
    sort( $priorities, SORT_NUMERIC );
    foreach ( $priorities as $priority ) {
        $entries = $GLOBALS['test_hooks'][ $hook ][ $priority ];
        foreach ( $entries as $entry ) {
            $params = array_merge( array( $value ), $args );
            $value  = call_user_func_array( $entry[0], array_slice( $params, 0, $entry[1] ) );
        }
    }
    return $value;
}

function do_action( $hook, ...$args ) {
    if ( empty( $GLOBALS['test_hooks'][ $hook ] ) ) {
        return;
    }
    $priorities = array_keys( $GLOBALS['test_hooks'][ $hook ] );
    sort( $priorities, SORT_NUMERIC );
    foreach ( $priorities as $priority ) {
        $entries = isset( $GLOBALS['test_hooks'][ $hook ][ $priority ] )
            ? $GLOBALS['test_hooks'][ $hook ][ $priority ]
            : array();
        foreach ( $entries as $entry ) {
            $still_registered = false;
            foreach ( $GLOBALS['test_hooks'][ $hook ][ $priority ] ?? array() as $current ) {
                if ( test_callback_equal( $current[0], $entry[0] ) ) {
                    $still_registered = true;
                    break;
                }
            }
            if ( $still_registered ) {
                call_user_func_array( $entry[0], array_slice( $args, 0, $entry[1] ) );
            }
        }
    }
}

function __( $text ) {
    return $text;
}

function esc_attr( $value ) {
    return htmlspecialchars( (string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
}

function esc_html( $value ) {
    return htmlspecialchars( (string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
}

function wp_strip_all_tags( $value ) {
    return strip_tags( (string) $value );
}

function remove_accents( $value ) {
    return (string) $value;
}

function wp_unslash( $value ) {
    return $value;
}

function taxonomy_exists( $taxonomy ) {
    return 'job_listing_category' === $taxonomy;
}

function get_taxonomy( $taxonomy ) {
    if ( ! taxonomy_exists( $taxonomy ) ) {
        return false;
    }
    return (object) array( 'name' => $taxonomy, 'object_type' => array( 'job_listing' ) );
}

function get_terms( $args ) {
    if ( 'job_listing_category' !== $args['taxonomy'] ) {
        return new WP_Error();
    }
    return array_values( $GLOBALS['test_terms']['job_listing_category'] );
}

function get_term( $term_id, $taxonomy ) {
    return $GLOBALS['test_terms'][ $taxonomy ][ (int) $term_id ] ?? new WP_Error();
}

function get_post_type( $post_id ) {
    return $GLOBALS['test_post_types'][ (int) $post_id ] ?? false;
}

function get_post_meta( $post_id, $key, $single = false ) {
    return $GLOBALS['test_meta'][ (int) $post_id ][ $key ] ?? '';
}

function update_post_meta( $post_id, $key, $value ) {
    $GLOBALS['test_meta'][ (int) $post_id ][ $key ] = $value;
    $GLOBALS['test_updates'][] = array( (int) $post_id, (string) $key, $value );
    return true;
}

function wp_get_post_terms( $post_id, $taxonomy, $args = array() ) {
    if ( 'job_listing_category' !== $taxonomy ) {
        return new WP_Error();
    }
    return $GLOBALS['test_job_terms'][ (int) $post_id ] ?? array();
}

function user_can( $user_id, $capability, ...$args ) {
    $key = $capability;
    if ( 'edit_post' === $capability && isset( $args[0] ) ) {
        $key .= ':' . (int) $args[0];
    }
    return ! empty( $GLOBALS['test_caps'][ (int) $user_id ][ $key ] );
}

function cmb2_get_metabox() {
    return new class {
        public function nonce() {
            return '_candidate_front_nonce';
        }
    };
}

function wp_verify_nonce() {
    return true === $GLOBALS['test_nonce_valid'];
}

require dirname( __DIR__ ) . '/raspitajse-candidate-desired-positions.php';

function test_fail( $message ) {
    fwrite( STDERR, 'FAIL: ' . $message . PHP_EOL );
    exit( 1 );
}

function test_assert( $condition, $message ) {
    if ( ! $condition ) {
        test_fail( $message );
    }
}

function test_same( $expected, $actual, $message ) {
    if ( $expected !== $actual ) {
        test_fail( $message . ' expected=' . var_export( $expected, true ) . ' actual=' . var_export( $actual, true ) );
    }
}

function test_term( $id, $name, $parent, $taxonomy = 'job_listing_category', $order = 0 ) {
    return (object) array(
        'term_id'    => $id,
        'name'       => $name,
        'parent'     => $parent,
        'taxonomy'   => $taxonomy,
        'term_order' => $order,
    );
}

function test_seed() {
    $GLOBALS['test_terms'] = array(
        'job_listing_category' => array(
            100 => test_term( 100, 'Tehnologija', 0, 'job_listing_category', 2 ),
            101 => test_term( 101, 'Backend', 100, 'job_listing_category', 2 ),
            102 => test_term( 102, 'Frontend', 100, 'job_listing_category', 1 ),
            103 => test_term( 103, 'QA <script>alert</script> & test', 100, 'job_listing_category', 3 ),
            200 => test_term( 200, 'Administracija', 0, 'job_listing_category', 1 ),
            201 => test_term( 201, 'Asistent', 200, 'job_listing_category', 1 ),
        ),
        'wrong_taxonomy' => array(
            301 => test_term( 301, 'Pogrešna taksonomija', 200, 'wrong_taxonomy', 1 ),
        ),
    );
    $GLOBALS['test_post_types'] = array(
        501 => 'candidate',
        502 => 'candidate',
        701 => 'job_listing',
        702 => 'job_listing',
        703 => 'job_listing',
        704 => 'job_listing',
    );
    $GLOBALS['test_meta'] = array(
        501 => array(
            '_candidate_user_id' => 11,
            'unrelated_field' => 'preserve-me',
            Raspitajse_Candidate_Desired_Positions::META_KEY => array(),
        ),
        502 => array(
            '_candidate_user_id' => 12,
            Raspitajse_Candidate_Desired_Positions::META_KEY => array( 201 ),
        ),
    );
    $GLOBALS['test_job_terms'] = array(
        701 => array( 101 ),
        702 => array( 102 ),
        703 => array(),
        704 => array( 101, 102 ),
    );
    $GLOBALS['test_caps'] = array(
        99 => array( 'manage_options' => true, 'edit_post:501' => true ),
    );
    $GLOBALS['test_candidate_map'] = array( 11 => 501, 12 => 502 );
    $GLOBALS['test_current_user'] = 11;
    $GLOBALS['test_nonce_valid']  = true;
    $GLOBALS['test_updates']      = array();
    $GLOBALS['test_vendor_calls'] = 0;
    $_SESSION = array();
    $_POST    = array();
}

function test_register_vendor_save() {
    remove_action( 'cmb2_after_init', array( 'WP_Job_Board_Pro_User', 'process_change_profile' ), 10 );
    add_action( 'cmb2_after_init', array( 'WP_Job_Board_Pro_User', 'process_change_profile' ), 10 );
}

function test_submit( $actor_id, $target_id, $raw, $nonce_valid = true, $present = true ) {
    $GLOBALS['test_current_user'] = $actor_id;
    $GLOBALS['test_nonce_valid']  = $nonce_valid;
    $_POST = array(
        'submit-cmb-profile'       => '1',
        '_candidate_post_type'     => 'candidate',
        'object_id'                => $target_id,
        '_candidate_front_nonce'   => 'fixture-nonce',
    );
    if ( $present ) {
        $_POST[ Raspitajse_Candidate_Desired_Positions::PRESENT_KEY ] = '1';
    }
    if ( null !== $raw ) {
        $_POST[ Raspitajse_Candidate_Desired_Positions::META_KEY ] = $raw;
    }
    test_register_vendor_save();
    do_action( 'cmb2_after_init' );
}

// The owned module must load and register hooks before vendor symbols exist.
test_assert( ! class_exists( 'WP_Job_Board_Pro_User' ), 'vendor class must be absent during module load' );
test_assert( class_exists( 'Raspitajse_Candidate_Desired_Positions' ), 'owned module must load independently' );

eval( 'class WP_Job_Board_Pro_User {
    public static function get_user_id() { return $GLOBALS["test_current_user"]; }
    public static function get_candidate_by_user_id( $user_id ) { return $GLOBALS["test_candidate_map"][(int) $user_id] ?? 0; }
    public static function process_change_profile() {
        $GLOBALS["test_vendor_calls"]++;
        $candidate_id = self::get_candidate_by_user_id( self::get_user_id() );
        do_action( "wp-job-board-pro-process-profile-after-change", $candidate_id, WP_JOB_BOARD_PRO_CANDIDATE_PREFIX );
    }
}' );

test_seed();

// UI source: child positions only, deterministic site order, safe labels.
$options = Raspitajse_Candidate_Desired_Positions::selectable_options();
test_same( array( 201, 102, 101, 103 ), array_keys( $options ), 'child terms must follow deterministic group/site order' );
test_assert( ! isset( $options[100] ) && ! isset( $options[200] ), 'root terms must not be selectable' );
test_assert( false !== strpos( $options[101], 'Tehnologija — Backend' ), 'child label must include safe group context' );
test_assert( false === strpos( $options[103], '<script>' ), 'option labels must strip markup' );
$fields = Raspitajse_Candidate_Desired_Positions::add_profile_field( array(), 501 );
test_same( 1, count( $fields ), 'candidate field must be appended once' );
test_same( 'Pozicije koje tražiš', $fields[0]['name'], 'required field label must be used' );
test_same( 'pw_multiselect', $fields[0]['type'], 'current mobile-compatible select2 component must be used' );
test_same( false, $fields[0]['save_field'], 'vendor CMB must not save unvalidated field data' );
test_same( '3', $fields[0]['attributes']['data-maximum-selection-length'], 'client UX must advertise max three' );
test_assert( false === strpos( $fields[0]['name'] . $fields[0]['desc'], Raspitajse_Candidate_Desired_Positions::META_KEY ), 'visible UI must not leak internal meta key' );

// Zero, one and three values save and reload exactly.
test_submit( 11, 501, null );
test_same( array(), Raspitajse_Candidate_Desired_Positions::get_ids( 501 ), 'valid empty selection must save canonical empty value' );
test_same( 1, $GLOBALS['test_vendor_calls'], 'valid empty profile flow must proceed' );
test_same( 'preserve-me', $GLOBALS['test_meta'][501]['unrelated_field'], 'empty save must preserve unrelated data' );

test_submit( 11, 501, array( '101' ) );
test_same( array( 101 ), Raspitajse_Candidate_Desired_Positions::get_ids( 501 ), 'one valid position must round-trip' );

test_submit( 11, 501, array( '201', '102', '101' ) );
test_same( array( 201, 102, 101 ), Raspitajse_Candidate_Desired_Positions::get_ids( 501 ), 'three valid positions must round-trip exactly' );

// Four positions are rejected before any vendor profile mutation.
$GLOBALS['test_meta'][501][ Raspitajse_Candidate_Desired_Positions::META_KEY ] = array( 101 );
$GLOBALS['test_vendor_calls'] = 0;
test_submit( 11, 501, array( '201', '102', '101', '103' ) );
test_same( 0, $GLOBALS['test_vendor_calls'], 'four values must stop the complete profile save' );
test_same( array( 101 ), Raspitajse_Candidate_Desired_Positions::get_ids( 501 ), 'four-value rejection must preserve old value' );
test_assert( ! empty( $_SESSION['messages'][0][1] ), 'rejection must expose a user-visible validation message' );

// Root, missing, wrong-taxonomy, malformed, duplicate and tampered values reject.
$invalid_payloads = array(
    array( '100' ),
    array( '999' ),
    array( '301' ),
    array( '1x' ),
    array( '101', '101' ),
);
foreach ( $invalid_payloads as $payload ) {
    $result = Raspitajse_Candidate_Desired_Positions::validate_change( 501, $payload, 11, true, true );
    test_same( false, $result['valid'], 'invalid payload must be rejected' );
}
test_same( false, Raspitajse_Candidate_Desired_Positions::validate_change( 501, '101', 11, true, true )['valid'], 'scalar tampering must reject' );
test_same( false, Raspitajse_Candidate_Desired_Positions::validate_change( 501, array(), 11, true, false )['valid'], 'missing presence sentinel must reject' );
test_same( false, Raspitajse_Candidate_Desired_Positions::validate_change( 501, array( '101', '101', '102', '201' ), 11, true, true )['valid'], 'duplicates cannot bypass maximum' );

// Nonce, actor and profile boundaries.
test_same( false, Raspitajse_Candidate_Desired_Positions::validate_change( 501, array( '101' ), 11, false, true )['valid'], 'invalid nonce must reject' );
test_same( false, Raspitajse_Candidate_Desired_Positions::validate_change( 501, array( '101' ), 22, true, true )['valid'], 'employer or outsider must reject' );
test_same( false, Raspitajse_Candidate_Desired_Positions::validate_change( 502, array( '101' ), 11, true, true )['valid'], 'candidate cannot edit another profile' );
test_same( true, Raspitajse_Candidate_Desired_Positions::validate_change( 501, array( '101' ), 11, true, true )['valid'], 'candidate owner must pass' );
test_same( true, Raspitajse_Candidate_Desired_Positions::validate_change( 501, array( '101' ), 99, true, true )['valid'], 'authorized administrator must pass' );
$GLOBALS['test_vendor_calls'] = 0;
test_submit( 11, 502, array( '101' ) );
test_same( 0, $GLOBALS['test_vendor_calls'], 'tampered object_id must stop vendor save' );
test_same( array( 201 ), Raspitajse_Candidate_Desired_Positions::get_ids( 502 ), 'wrong profile must remain unchanged' );
$GLOBALS['test_vendor_calls'] = 0;
test_submit( 11, 501, array( '102' ), false );
test_same( 0, $GLOBALS['test_vendor_calls'], 'invalid nonce must stop vendor save' );

// Stale stored values are filtered without repair writes.
$GLOBALS['test_updates'] = array();
$GLOBALS['test_meta'][501][ Raspitajse_Candidate_Desired_Positions::META_KEY ] = array( 101, 999, 100, 301, 101, 102 );
test_same( array( 101, 102 ), Raspitajse_Candidate_Desired_Positions::get_ids( 501 ), 'reads must ignore stale/root/wrong/duplicate IDs' );
test_same( array(), $GLOBALS['test_updates'], 'read filtering must never mutate storage' );
$labels = Raspitajse_Candidate_Desired_Positions::get_labels( 501 );
test_assert( false === strpos( implode( ' ', $labels ), '<script>' ), 'presentation labels must be escaped' );
test_assert( false === strpos( implode( ' ', $labels ), Raspitajse_Candidate_Desired_Positions::META_KEY ), 'presentation labels must not leak internal key' );

// Matching contract: configured overlap only, one canonical job position only.
$GLOBALS['test_meta'][501][ Raspitajse_Candidate_Desired_Positions::META_KEY ] = array( 101 );
test_same( true, Raspitajse_Candidate_Desired_Positions::has_preferences( 501 ), 'configured preference must be detectable' );
test_same( true, Raspitajse_Candidate_Desired_Positions::job_matches( 501, 701 ), 'overlap must match' );
test_same( false, Raspitajse_Candidate_Desired_Positions::job_matches( 501, 702 ), 'non-overlap must not match' );
test_same( false, Raspitajse_Candidate_Desired_Positions::job_matches( 501, 703 ), 'job without position must not match' );
test_same( false, Raspitajse_Candidate_Desired_Positions::job_matches( 501, 704 ), 'multi-position job must fail closed' );
$GLOBALS['test_meta'][501][ Raspitajse_Candidate_Desired_Positions::META_KEY ] = array();
test_same( false, Raspitajse_Candidate_Desired_Positions::has_preferences( 501 ), 'empty preferences must be not configured' );
test_same( false, Raspitajse_Candidate_Desired_Positions::job_matches( 501, 701 ), 'no preference must not auto-match' );

echo "PASS: CANDIDATE_DESIRED_POSITIONS_READY\n";
