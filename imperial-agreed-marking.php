<?php
/*
Plugin Name: Imperial Agreed Marking
Description: Agreed Marking for two or more assessors
Version: 0.1
Author: Alex Furr
License: GPL
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Global defines
define( 'AGREED_MARKING_URL', plugins_url('imperial-agreed-marking' , dirname( __FILE__ )) );
define( 'AGREED_MARKING_PATH', plugin_dir_path(__FILE__) );


include_once( AGREED_MARKING_PATH . 'classes/class-cpt-setup.php' );
include_once( AGREED_MARKING_PATH . 'classes/class-draw.php' );
include_once( AGREED_MARKING_PATH . 'classes/class-init.php' );
include_once( AGREED_MARKING_PATH . 'classes/class-queries.php' );
include_once( AGREED_MARKING_PATH . 'classes/class-db.php' );
include_once( AGREED_MARKING_PATH . 'classes/class-actions.php' );
include_once( AGREED_MARKING_PATH . 'classes/class-utils.php' );
include_once( AGREED_MARKING_PATH . 'classes/class-export.php' );
include_once( AGREED_MARKING_PATH . 'classes/class-adminDraw.php' );

//include_once( AGREED_MARKING_PATH . 'classes/class-peer-feedback.php' );
//include_once( AGREED_MARKING_PATH . 'classes/class-database.php' );
//include_once( AGREED_MARKING_PATH . 'classes/class-utils.php' );

define( 'FINAL_MARK_DISCREPANCY_THRESHOLD', 7 );


?>
