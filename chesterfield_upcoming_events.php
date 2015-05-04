<?php defined( 'ABSPATH' ) or die( 'No longer allowed at the Chesterfield' );
/**
 * @package chesterfield_upcoming_events
 */

/*
Plugin Name: Chesterfield Upcoming Events
Description: Display upcoming events in widget
Version: 0.0.1
Author: Zach Metcalf
License: GPLv2 or later
*/

/*  Copyright 2015 Zach Metcalf

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action( 'init', 'add_event_type' );

function add_event_type() {
  register_post_type( 'chesterfield_event',
    array(
      'labels' => array(
        'name' => __( 'Events' ),
        'singular_name' => __( 'Event' )
      ),
      'public' => true,
      'supports' => array( 'thumbnail', 'title', 'revisions' )
    )
  );
}

add_action( 'load-post.php', 'cf_event_meta_boxes_setup' );
add_action( 'load-post-new.php', 'cf_event_meta_boxes_setup' );

function cf_event_meta_boxes_setup() {

  add_action( 'add_meta_boxes', 'cf_event_add_meta_boxes' );
  add_action( 'save_post', 'cf_save_event_meta', 10, 2 );
}

function cf_event_add_meta_boxes() {
  $screens = array( 'post',  'page', 'attachment', 'chesterfield_event' );
  $screens = apply_filters( 'cf_post_types_with_events', $screen );

  add_meta_box(
    'cf_event',
    esc_html__( 'Event', 'cf_domain' ),
    'cf_event_meta_box',
    $screen,
    'side',
    'default'
  );
}

function cf_event_meta_box( $object, $box ) {
  wp_nonce_field( basename( __FILE__ ), 'cf_event_nonce' ); ?>

  <p>
    <label for="cf_event_date"><?php _e( "Add a date for event", 'cf_domain' ); ?></label>
    <br />
    <input class="widefat" type="date" name="cf_event_date" value="<?php
      echo esc_attr( get_post_meta( $object->ID, 'cf_event_date', true) ); ?>" size="30" />
  </p> <?php
}

function cf_save_event_meta( $post_id, $post ) {
  // Verify nonce
  if ( !isset( $_POST['cf_event_nonce'] ) || !wp_verify_nonce(
       $_POST['cf_event_nonce'], basename( __FILE__ ) ) ) {
         return $post_id;
  }

  // Get post type
  $post_type = get_post_type_object( $post->post_type );

  // Check if user has permission to edit
  if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ) {
    return $post_id;
  }

  // Get meta value of custom field
  $new_meta_value = ( isset( $_POST['cf_event_date'] ) ?
    sanitize_html_class( $_POST['cf_event_date'] ) : '' );

  // Get the meta key
  $meta_key = 'cf_event_date';

  // Get meta value of custom key
  $meta_value = get_post_meta( $post_id, $meta_key, true );

  // If new value - add it
  if ( $new_meta_value && '' == $meta_value ) {
    add_post_meta( $post_id, $meta_key, $new_meta_value, true );
  } elseif ( $new_meta_value && $new_meta_value != $meta_value ) {
    // Update unmatched values
    update_post_meta( $post_id, $meta_key, $new_meta_value );
  } elseif ( '' == $new_meta_value && $meta_value ) {
    // Delete if absence of data
    delete_post_meta( $post_id, $meta_key, $meta_value );
  }
}


class Event_Widget extends WP_Widget {

  public function __construct() {
    parent::__construct(
      'event_widget',
      __( 'Event Widget', 'cf_domain'),
      array( 'description' => __( 'A widget to display upcoming or recent events', 'cf_domain'), )
    );
  }

  function widget( $args, $instance ) {
    extract( $args );
    $title = apply_filters( 'widget_title', $instance['title'] );

    $qry_args = array(
      'post_type' => 'any',
      'published' => true,
      'order' => 'ASC',
      'orderby' => 'meta_value',
      'meta_key'=> 'cf_event_date',
      'meta_value' => date( 'Y-m-d' ),
      'meta_compare' => '>=',
    );
    $the_query = new WP_Query( $qry_args );

    ?>
      <?php echo $before_widget; ?>
        <?php if ( $title ): ?>
          <?php echo $before_title . $title . $after_title; ?>
        <?php endif; ?>
        <ul>
        <?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
          <li>
            <?php if ( has_post_thumbnail() ): ?>
              <a href="<?php the_permalink(); ?>"><?php echo get_the_post_thumbnail( get_the_ID(), array ( 50, 50 ) ); ?></a>
            <?php endif ?>
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> - <?php echo date("F j", strtotime( get_post_meta( get_the_ID(), 'cf_event_date', TRUE ) ) ); ?>
          </li>
        <?php endwhile ?>
        </ul>
        <?php wp_reset_postdata(); ?>
      <?php echo $after_widget; ?>
    <?php
  }

  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance['title'] = strip_tags( $new_instance['title'] );
    return $instance;
  }

  function form( $instance ) {
    $title = esc_attr( $instance['title'] );
    ?>
    <p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title: ', 'cf_domain' ); ?>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
      </label>
    </p>
    <?php
  }
}

add_action( 'widgets_init', function() {
  register_widget( 'Event_Widget' );
});
