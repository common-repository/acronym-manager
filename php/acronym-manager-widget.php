<?php
/*
Acronmyn_Manager_Widget

allows a registered user to add to the acronym collection from a sidebar
widget.  This is convenient in situations where a user is reviewing an 
article and notes that there is an undefined acronym to add to the
collection.

Created by Daniel Finningan
2012-03-23

*/

class Acronym_Manager_Widget extends WP_Widget {
	
    function Acronym_Manager_Widget() {
		
        /* add a description for widget in WordPress Widget Administration page */
        $widget_ops = array('description' => __('Allows a registered user to add to the Acronym Manager collection from a sidebar
widget.', 'Acronym Manager') );			
	parent::WP_Widget( 'Acronym_Manager_Widget', __('Acronym Manager Widget','Acronym_Manager'), $widget_ops);		
    }

	
    /* Widget Admin form */
    function form($instance) {
	
        /* Set up default values for widget settings. */
        $instance = wp_parse_args((array) $instance, array('title'=>'Add Acronym'));
		
        echo '
            <p>
            <label for="'. $this->get_field_id('title').'">'.__('Widget Title','Acronmy_Manager').'</label>
            <input type="text" id="'. $this->get_field_id('title').'" name="'. $this->get_field_name('title').'" value="'.attribute_escape($instance['title']).'" class="widefat" />
            </p>';
    }

		
    function update($new_instance, $old_instance) {
		
        /* If the user tries to type any HTML tags, like <h1>, <p>, or <strong> into the title field, this line will strip it out. */
        $instance['title'] = strip_tags($new_instance['title']); 
        return $instance;
		
    }


    /* What widget displays when called */	
    function widget( $args, $instance ) {
	
        echo $args['before_widget'];
		
        /* If the user has set a title for the widget, display it. */
        if ( $instance['title'] ) echo $args['before_title'] . $instance['title'] . $args['after_title'];

        if ( isset($_GET['acronym']) ) {
            $acronym = $_GET['acronym'];
            $fulltext = $_GET['fulltext'];

            get_currentuserinfo() ;
            global $user_level;

            if ( empty($acronym) || empty($fulltext) ) {
                $message = __('Acronym not added: incomplete.');
            } elseif ($user_level <= 0) {
                $message = __('You do not have permission to add an acronym.');
            } elseif ($user_level > 0) {
                $acronyms = get_option('acronym_acronyms');
	        $acronyms[$acronym] = $fulltext;
                update_option('acronym_acronyms', $acronyms);
                $message = __('Acronym successfully added.');
            }
        }

        ?>

        <!-- Checks if acronym is already in the db, and if so, preloads the context textbox with it-->
        <script type="text/javascript">
        var a_acronym = new Array();
	<?php
	$acronyms = get_option('acronym_acronyms');   #get php array:
	foreach($acronyms as $key => $value) {        #pass php array to js array:
		echo "a_acronym[\"$key\"] = \"$value\";\n";
	} ?>
	function checkAcronym(s_acronym) {
	    if (a_acronym[s_acronym] != undefined) {
	        document.getElementById('fulltext').value = a_acronym[s_acronym];
	    } else {
	        document.getElementById('fulltext').value = "";
	    }
	}
        </script>
        <form class="acronym-manager-widget" method="GET" action="">
        <font style="font-size:12px;color:#888888;font-style:italic;">
        <?php if ( isset($message) ) echo $message . '<br />'; ?>
        </font>
	<table><tr><td>
        <label for="acronym"><?php _e('Acronym') ?></label></td><td>
	<input name="acronym" id="acronym" type="text" onBlur="checkAcronym(this.value)" size="20" style="font-size: 14px; font-family: arial;"/></td></tr>
        <tr><td>
	<label for="fulltext"><?php _e('Definition') ?></label></td><td>
	<input name="fulltext" id="fulltext" type="text" size="20" style="font-size: 14px; font-family: arial;" />
        </td></tr></table>
        <input type="submit" name="submit" value="Add" />
        </form>

        <?php

        echo $args['after_widget'];

    }
	
}
?>
