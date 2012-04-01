<?php

namespace wordefinery;

class MailruCounterWidget extends \WP_Widget {
    function __construct() {
        $this->plugin = \Wordefinery::Plugin('MailruCounter');
        parent::__construct(
            'wordefinery_mailrucounter_widget',
            __('Rating@Mail.ru Counter'),
            array(
                'description' => __('Rating@Mail.ru Counter Widget')
            )
        );
    }

    function widget( $args, $instance ) {
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        echo $before_widget;
        if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }
        echo $this->plugin->Counter();
        ?><div class="textwidget"><div style="text-align:<?php echo $this->plugin->store->align ?>"><!-- Rating@Mail.ru counter -->
<a href="http://top.mail.ru/jump?from=<?php echo $this->plugin->store->site_id; ?>">
<img src="http://top.mail.ru/counter?id=<?php echo $this->plugin->store->site_id; ?>;t=<?php echo $this->plugin->store->style + $this->plugin->store->color; ?>;l=1"
style="border:0;" height="<?php echo $this->plugin->store->height; ?>" width="<?php echo $this->plugin->store->width; ?>" alt="Rating@Mail.ru" /></a>
<!-- //Rating@Mail.ru counter --></div></div><?php
        echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'style' => '23', 'title' => '', 'color' => '6') );
        $title = esc_attr( $instance['title'] );
        ?>
        <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?>:</label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <?php
    }

}