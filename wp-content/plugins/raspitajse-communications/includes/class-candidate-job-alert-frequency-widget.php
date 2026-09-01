<?php
/**
 * Render-only wrapper for the WPJBP candidate-to-job alert widget.
 *
 * Loaded during widgets_init after the vendor widget class exists.
 *
 * @package raspitajse-communications
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Raspitajse_Communications_Candidate_Job_Alert_Frequency_Widget extends WP_Job_Board_Pro_Widget_Job_Alert_Form {

    public function widget( $args, $instance ) {
        return Raspitajse_Communications_Candidate_Job_Alert_Frequency_UI::with_create_form_frequencies(
            function () use ( $args, $instance ) {
                return parent::widget( $args, $instance );
            }
        );
    }
}
