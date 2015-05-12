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
  $slug = 'event';
  $slug = apply_filters( 'cf_event_type', $slug );

  register_post_type( 'chesterfield_event',
    array(
      'labels' => array(
        'name' => __( 'Events' ),
        'singular_name' => __( 'Event' )
      ),
      'rewrite' => array(
        'slug' => $slug
      ),
      'public' => true,
      'supports' => array( 'thumbnail', 'title', 'revisions' )
    )
  );
}

add_filter( 'single_template', 'get_chesterfield_event_template' );

function get_chesterfield_event_template( $single_template ) {
  global $post;

	$object = get_queried_object();
	$single_postType_postName_template = locate_template(
	  "single-{$object->post_type}.php"
	);
	if( file_exists( $single_postType_postName_template ) )
	{
		return $single_postType_postName_template;
	} else {
    if ( $post->post_type == 'chesterfield_event' ) {
      $single_template = dirname( __FILE__ ) . '/chesterfield_event_template.php';
    }
		return $single_template;
	}
  return $single_template;
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
  </p>
  <p>
    <label for="cf_event_end_date"><?php _e( "If multi-day event, add end date", 'cf_domain' ); ?></label>
    <br />
    <input class="widefat" type="date" name="cf_event_end_date" value="<?php
      echo esc_attr( get_post_meta( $object->ID, 'cf_event_end_date', true) ); ?>" size="30" />
  </p>

  <?php
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

  // If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

  $meta_fields = array( 'cf_event_date', 'cf_event_end_date' );

  foreach ( $meta_fields as $field ) {

    // Get meta value of custom field
    $new_meta_value = ( isset( $_POST[$field] ) ?
      sanitize_html_class( $_POST[$field] ) : '' );

    // Get meta value of custom key
    $meta_value = get_post_meta( $post_id, $field, true );

    // If new value - add it
    if ( $new_meta_value && '' == $meta_value ) {
      add_post_meta( $post_id, $field, $new_meta_value, true );
    } elseif ( $new_meta_value && $new_meta_value != $meta_value ) {
      // Update unmatched values
      update_post_meta( $post_id, $field, $new_meta_value );
    } elseif ( '' == $new_meta_value && $meta_value ) {
      // Delete if absence of data
      delete_post_meta( $post_id, $field, $meta_value );
    }
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
    $title = apply_filters( 'cf_widget_title', $instance['title'] );
    $list_items = apply_filters( 'cf_widget_items', $instance['list_items'] );

    $qry_args = array(
      'posts_per_page' => $list_items ? $list_items : -1,
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
              <a href="<?php the_permalink(); ?>"><?php echo get_the_post_thumbnail( get_the_ID(), array ( 40, 40 ) ); ?></a>
            <?php endif ?>
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> - <?php
              $event_date = get_post_meta( get_the_ID(), 'cf_event_date', TRUE );
              $event_end_date = get_post_meta( get_the_ID(), 'cf_event_end_date', TRUE );
              if ( !$event_end_date OR $event_date >= $event_end_date ) {
                echo date( "F j", strtotime( $event_date ) );
              } else {
                if ( date( "F", strtotime( $event_date ) ) == date("F", strtotime( $event_end_date ) ) ) {
                  echo date( "F j", strtotime( $event_date )) . ' - ' . date( "j", strtotime( $event_end_date ) );
                } else {
                  echo date( "F j", strtotime( $event_date )) . ' - ' . date( "F j", strtotime( $event_end_date ) );
                }
              }
            ?>
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
    $instance['list_items'] = absint( $new_instance['list_items'] );
    return $instance;
  }

  function form( $instance ) {
    $title = esc_attr( $instance['title'] );
    $list_items = esc_attr( $instance['list_items'] );
    ?>
    <p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title: ', 'cf_domain' ); ?>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
      </label>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id( 'list_items' ); ?>"><?php _e( 'Number of Events to Show: ', 'cf_domain' ); ?>
        <input class="widefat" id="<?php echo $this->get_field_id( 'list_items' ); ?>" name="<?php echo $this->get_field_name('list_items'); ?>" type="number" value="<?php echo $list_items; ?>" />
      </label>
    </p>
    <?php
  }
}

add_action( 'widgets_init', function() {
  register_widget( 'Event_Widget' );
});
