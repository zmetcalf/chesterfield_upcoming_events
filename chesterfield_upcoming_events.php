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
      register_meta_box_cb => 'add_event_meta',
    )
  );
}

function add_event_meta($post) {
  // TODO
}
