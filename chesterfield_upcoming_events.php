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
}

function cf_event_add_meta_boxes() {
  $screens = array( 'post',  'page' );
  $screens = apply_filters( 'posts_with_events', $screen );

  add_meta_box(
    'cf_event',
    esc_html__( 'Event' ),
    'cf_event_meta_box',
    $screen,
    'side',
    'default'
  );
}
