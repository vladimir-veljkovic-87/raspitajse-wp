<?php
/**
 * Social: Social
 *
 * @package    wp-realestate
 * @author     Habq 
 * @license    GNU General Public License, version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Job_Board_Pro_Social {
    /**
     * Initialize social
     *
     * @access public
     * @return void
     */
    public static function init() {
        add_action( 'wp_head', array( __CLASS__, 'open_graph_meta' ), 1 );
    }

    /**
     * The Open Graph protocol meta
     * http://ogp.me/
     *
     * @access public
     * @return string
     */
    public static function open_graph_meta() {
        if ( is_singular() ) {
            global $post;
            echo '<meta property="og:title" content="' . get_the_title() . '" />';

            $author_id = WP_Job_Board_Pro_Job_Listing::get_author_id($post->ID);
            $employer_id = WP_Job_Board_Pro_User::get_employer_by_user_id($author_id);

            if ( $employer_id ) {
                if ( has_post_thumbnail( $employer_id ) ) {
                    $src = wp_get_attachment_image_src( get_post_thumbnail_id( $employer_id ), 'full' );
                    $image_url = $src ? $src[0] : '';
                } elseif ( ! empty( has_post_thumbnail( $post->ID ) ) ) {
                    $src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
                    $image_url = $src ? $src[0] : '';
                }
            }

            if ( ! empty( $image_url ) ) {
                echo '<meta property="og:image" content="' . esc_attr( $image_url ) . '" />';
            }
        }
    }
}

WP_Job_Board_Pro_Social::init();