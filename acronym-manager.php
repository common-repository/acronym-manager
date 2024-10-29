<?php
/*
  Plugin Name: Acronym Manager
  Description: Adds tooltip defining acronyms on your Wordpress website that are in an acronym library. The acronym collection is managed through the Admin interface under Tools.  A widget is also included that allows users to add acronyms to the library if they have contributing privileges.  Library can be imported/exported for transfer between sites.  Use the shortcode [glossary] to include a list of the acronym collection on a page or post within your website.
  Version: 0.1
  Author: Daniel Finnigan
  Author URI: http://www.fammedref.org

  Copyright 2012  Daniel Finnigan

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * Holds the absolute location of plugin
 */
if ( !defined( 'AM_ABSPATH' ) ) define( 'AM_ABSPATH', dirname( __FILE__ ) );
if ( !defined( 'AM_URLPATH' ) ) define( 'AM_URLPATH', WP_PLUGIN_URL."/acronym-manager" );

include AM_ABSPATH . '/php/acronym-manager-class.php';

$acronym_manager = new Acronym_Manager(); 

add_action('switch_theme', array('Acronym_Manager', 'theme_html5_check'));
add_action('admin_menu', array('Acronym_Manager', 'add_pages'));
add_action('admin_init', array('Acronym_Manager', 'management_handler'));
add_action('admin_init', 'acronym_manager_load');
add_action('widgets_init', 'am_init_widget' );
add_action('init', 'acronym_manager_load');

register_activation_hook(__FILE__, 'acronym_manager_install');
register_uninstall_hook( __FILE__, 'acronym_manager_uninstall');

function acronym_manager_load() {
	wp_register_style("am-style", AM_URLPATH."/css/am-style.css");
	wp_enqueue_style("am-style",  AM_URLPATH."/css/am-style.css");
	wp_register_script("am-script", AM_URLPATH."/js/am-scripts.js");
	wp_enqueue_script("am-script",  AM_URLPATH."/js/am-scripts.js");
 
        //For i18n
//        load_plugin_textdomain( 'acronym-manager', false, AM_ABSPATH );
//        wp_localize_script( 'am-script', 'objectL10n', array(
//	    'file-type-error' => __('File type not allowed,\nAllowed file types: *.amf,*.txt')
//            ) );
}


if (1 == get_option('acronym_content'))  add_filter('the_content', array('Acronym_Manager', 'acronym_replace'));
if (1 == get_option('acronym_comments')) add_filter('comment_text', array('Acronym_Manager', 'acronym_replace'));


//Initiate the acronym widget that allows the adding of acronyms from the sidebar
function am_init_widget() {
    require_once('php/acronym-manager-widget.php'); // Include widget file
    register_widget('Acronym_Manager_Widget'); // Register the widget
}

//add to a page in the site where you would like the complete collection of acronyms listed in table format
add_shortcode( 'glossary', array('Acronym_Manager', 'print_glossary_list') );

/* void install ()
 * Creates tables and populates default acronyms.
 */

function acronym_manager_install() {
    // Populate defaults
    $acronyms = array('CNS' => 'central nervous system');
    add_option('acronym_acronyms', $acronyms);

    // Add default options -- Options not currently modifiable by user
    add_option('acronym_content', 1);
    add_option('acronym_comments', 1);
    Acronym_Manager::theme_html5_check();
}


/* void uninstall ()
 */

function acronym_manager_uninstall() {
    delete_option('acronym_acronyms');
    delete_option('acronym_content');
    delete_option('acronym_comments');
    delete_option('acronym_html5');
}	
?>