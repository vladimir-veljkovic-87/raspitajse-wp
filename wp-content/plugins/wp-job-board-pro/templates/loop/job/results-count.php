<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="results-count">
	<?php
		if ( $total <= $per_page || -1 === $per_page ) {
			/* prevod: %d: ukupni rezultati */
			printf( _n( 'Prikazan je jedan rezultat', 'Prikazano svih %d rezultata', $total, 'wp-job-board-pro' ), $total );
		} else {
			$first = ( $per_page * $current ) - $per_page + 1;
			$last  = min( $total, $per_page * $current );
			/* prevod: 1: prvi rezultat 2: poslednji rezultat 3: ukupno rezultata */
			printf( _nx( 'Prikazan je jedan rezultat', 'Prikazano <span class="first">%1$d</span> &ndash; <span class="last">%2$d</span> od %3$d rezultata', $total, 'sa prvim i poslednjim rezultatom', 'wp-job-board-pro' ), $first, $last, $total );
		}
	?>
</div>
