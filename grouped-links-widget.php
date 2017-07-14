<?php
/*
Plugin Name: Grouped Links Widget
Description: Grouped Links Widget can use any number of widget instances to group links by their associated link categories. Tested for WP 3.0. Please use version 1.0.x for WP 2.7 and below.
Author: Stefan Meretz
Version: 2.0.1
Author URI: http://www.meretz.de
Plugin URI: http://groupedlinks.wordpress.com/

Copyright (C) 2010 Stefan Meretz

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

///////////////////////////////////////////////////////////////////

class grouped_links_widget extends WP_Widget{

	// declaration of the class
	function grouped_links_widget(){
		$widget_ops = array( 'classname' => 'grouped_links_widget', 'description' => __( "Group your Links!" ));
		$control_ops = array( 'width' => 250, 'height' => 300 );
		$this->WP_Widget( 'grouped_links', __( 'Grouped Links' ), $widget_ops, $control_ops );
	}

	// generates sidebar output
	function widget( $args, $instance ){
		extract( $args );
		if( isset( $instance['category_group'] )) {
			$categories = '';
			foreach( $instance['category_group'] as $cat_group ){
				if( !empty( $categories )) $categories .= ',';
				$categories .= $cat_group;
			};
			$cat_order = $instance['category_order'];
			$opt = get_option( 'grouped_links_options' );
			wp_list_bookmarks(	// see http://codex.wordpress.org/wp_list_bookmarks
				array(
					'category' => $categories,
					'category_order' => $cat_order,	# order between the groups, or:
#					'order' => $cat_order,		# link order within each group
					'category_before' => $opt[category_before],
					'category_after' => $opt[category_after],
					'title_before' => $opt[title_before],
					'title_after' => $opt[title_after]
				)
			);
		}
	}

	// saves widgets settings.
	function update( $new_instance, $old_instance ){
		$instance = $old_instance;
		$instance['category_group'] = $new_instance['category_group'];
		$instance['category_order'] = $new_instance['category_order'];
		return $instance;
	}

	// creates widget edit form
	function form( $instance ){
		global $wpdb;
		$instance = wp_parse_args( (array) $instance, array( 'category_group'=>array(), 'category_order'=>'ASC' ));
		if( isset( $instance['category_group'] )) foreach( $instance['category_group'] as $cat_group ){
			$cat_group_selected[$cat_group] = 1;
		};
		$cat_order_val = $instance['category_order'];
		$cat_group_name = $this->get_field_name( 'category_group' );
		$cat_group_id = $this->get_field_id( 'category_group' );
		$cat_order_name = $this->get_field_name( 'category_order' );
		$cat_order_id = $this->get_field_id( 'category_order' );
		$qry = "SELECT t.term_id id, t.name cat FROM $wpdb->terms t, $wpdb->term_taxonomy x WHERE t.term_id = x.term_id and x.taxonomy = 'link_category' and x.count > 0 order by cat $cat_order_val";
		$results = $wpdb->get_results( $qry );
		echo "\t\t<p>\n\t\t";
		_e( 'Link Categories' );
		echo ":\n\t\t</p>\n\t\t<p>\n";
		if( $results ) {
			$i = 1;
			foreach( $results as $result ){
				$cat_gid = $result->id;
				$cat_group_val = $result->cat;
				echo "\t\t<label for='$cat_group_id-$i'>\n";
				echo "\t\t<input class='checkbox' type='checkbox' ";
				echo "id='$cat_group_id-$i' name='$cat_group_name".'['.$i.']'."' value='$cat_gid'";
				echo $cat_group_selected[$cat_gid] ? " checked='checked'" : '';
				echo " /> $cat_group_val<br />\n\t\t</label>\n";
				$i++;
			}
		}
		echo "\t\t</p>\n\t\t<p>\n";
		echo "\t\t<label for='$cat_order_name'>Sort order:\n";
		echo "\t\t<select id='$cat_order_id' name='$cat_order_name'>\n";
		echo "\t\t\t<option value='ASC'".( $cat_order_val == 'ASC' ? " selected='selected'" : '' ).">ascending</option>\n";
		echo "\t\t\t<option value='DESC'".( $cat_order_val == 'DESC' ? " selected='selected'" : '' ).">descending</option>\n";
		echo "\t\t</select>\n\t\t</label>\n";
		echo "\t\t</p>\n";
	}

}// END class

// options page to set number of plugin instances
function grouped_links_widget_options() {
	$opt = get_option( 'grouped_links_options' );
	echo '<div id="icon-options-general" class="icon32"><br /></div>'."\n";
	echo '<div class="wrap">'."\n";
	echo "\t<h2>".__( 'Grouped Links &ndash; Options' )."</h2>\n";
	echo "\t".'<form method="post" action="options.php">'."\n\t\t";
	settings_fields( 'grouped_links_options_group' );
	echo "\n\t\t<h3>".__( 'HTML code for each category' )."</h3>\n";
	echo "\t\t".'<table class="form-table">'."\n";
	echo "\t\t\t".'<tr valign="top">'."\n";
	echo "\t\t\t".'<th scope="row">'."\n\t\t\t\t".__( 'Opening tag: ' )."\n\t\t\t</th>\n";
	echo "\t\t\t<td>\n";
	echo "\t\t\t\t".'<input style="width: 200px;" name="grouped_links_options[category_before]" type="text" value="';
	echo htmlentities( $opt[category_before] ).'" />'."\n\t\t\t</td>\n";
	echo "\t\t\t</tr>\n";
	echo "\t\t\t".'<tr valign="top">'."\n";
	echo "\t\t\t".'<th scope="row">'."\n\t\t\t\t".__( 'Closing tag: ' )."\n\t\t\t</th>\n";
	echo "\t\t\t<td>\n";
	echo "\t\t\t\t".'<input style="width: 200px;" name="grouped_links_options[category_after]" type="text" value="';
	echo htmlentities( $opt[category_after] ).'" />'."\n\t\t\t</td>\n";
	echo "\t\t\t</tr>\n";
	echo "\t\t</table>\n";
	echo "\n\t\t<h3>".__( 'HTML code for each title' )."</h3>\n";
	echo "\t\t".'<table class="form-table">'."\n";
	echo "\t\t\t".'<tr valign="top">'."\n";
	echo "\t\t\t".'<th scope="row">'."\n\t\t\t\t".__( 'Opening tag: ' )."\n\t\t\t</th>\n";
	echo "\t\t\t<td>\n";
	echo "\t\t\t\t".'<input style="width: 200px;" name="grouped_links_options[title_before]" type="text" value="';
	echo htmlentities( $opt[title_before] ).'" />'."\n\t\t\t</td>\n";
	echo "\t\t\t</tr>\n";
	echo "\t\t\t".'<tr valign="top">'."\n";
	echo "\t\t\t".'<th scope="row">'."\n\t\t\t\t".__( 'Closing tag: ' )."\n\t\t\t</th>\n";
	echo "\t\t\t<td>\n";
	echo "\t\t\t\t".'<input style="width: 200px;" name="grouped_links_options[title_after]" type="text" value="';
	echo htmlentities( $opt[title_after] ).'" />'."\n\t\t\t</td>\n";
	echo "\t\t\t</tr>\n";
	echo "\t\t</table>\n";
	echo "\t\t".'<p class="submit">'."\n";
	echo "\t\t\t".'<input type="submit" class="button-primary" value="'.__( 'Save Changes' ).'" />'."\n";
	echo "\t\t</p>\n\t</form>\n</div>\n";
}

// register grouped links widget
function grouped_links_widget_init() {
	register_widget( 'grouped_links_widget' );
}

// register options page
function grouped_links_widget_options_register(){
	add_options_page( 'Grouped Links Options', 'Grouped Links', 8, __FILE__, 'grouped_links_widget_options' );
}

// register whitelist options
function register_grouped_links_options_group(){
	if( !get_option( 'grouped_links_options' )){
		$opt[category_before] = '<li>';
		$opt[category_after] = '</li>';
		$opt[title_before] = '<h3>';
		$opt[title_after] = '</h3>';
		add_option( 'grouped_links_options', $opt );
	}
	register_setting( 'grouped_links_options_group', 'grouped_links_options' );
}

// hook in widgets init
add_action( 'widgets_init', 'grouped_links_widget_init' );

// hook in options register functions
if ( is_admin() ){
	add_action( 'admin_menu', 'grouped_links_widget_options_register');
	add_action( 'admin_init', 'register_grouped_links_options_group' );
}

?>
