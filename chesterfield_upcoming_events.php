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

add_action( 'load-post.php', 'cf_event_meta_boxes_setup' );
add_action( 'load-post-new.php', 'cf_event_meta_boxes_setup' );

function cf_event_meta_boxes_setup() {

  add_action( 'add_meta_boxes', 'cf_event_add_meta_boxes' );
  add_action( 'save_post', 'cf_save_event_meta', 10, 2 );
}

function cf_event_add_meta_boxes() {
  $screens = array( 'post',  'page' );
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
}

add_action( 'widgets_init', function() {
  register_widget( 'Event_Widget' );
});
