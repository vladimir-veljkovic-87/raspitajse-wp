<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
$user_id = superio_private_message_user_id();
$display_name = get_the_author_meta('display_name', $rpost->post_author);
$display_name = apply_filters('superio-private-message-user-display-name', $display_name, $rpost->post_author);
?>
<li class="<?php echo esc_attr($rpost->post_author == $user_id ? 'yourself-reply' : 'user-reply'); ?> author-id-<?php echo esc_attr($rpost->post_author); ?>">
  <div class="top-reply-content flex-middle">
    <?php if ( $rpost->post_author != $user_id ) { ?>
      <div class="avatar">
        <?php superio_private_message_user_avarta( $rpost->post_author ); ?>
      </div>
    <?php } ?>
    <div class="reply-content">
      <div class="flex-middle">
        <?php if ( $rpost->post_author != $user_id ) { ?>
          <h4 class="name-reply"><?php echo esc_html($display_name); ?></h4>
        <?php } ?>
        <!-- date -->
        <?php
          $date = get_the_time( get_option('date_format'), $rpost );
          $current = strtotime(date("Y-m-d"));
          $date    = strtotime( get_the_time('Y-m-d', $rpost) );

          $datediff = $date - $current;
          $difference = floor($datediff/(60*60*24));
          if ( $difference == 0 ) {
            $date = esc_html__('Today', 'superio');
          } elseif ( $difference == -1 ) {
            $date = esc_html__('Yesterday', 'superio');
          } else {
            $date = get_the_time( get_option('date_format'), $rpost );
          }
        ?>
        <div class="post-date"><?php echo trim($date); ?>, <?php echo get_the_time( get_option('time_format'), $rpost ); ?></div>
      </div>
    </div>
  </div>
  <div class="post-content"><?php echo esc_html($rpost->post_content); ?></div>
</li>