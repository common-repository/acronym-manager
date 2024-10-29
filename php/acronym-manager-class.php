<?php

class Acronym_Manager {

    /* string get_textdomain ()
     *
     * returns the text domain to be used for internationalisation
     */

    function get_textdomain() {
	return "Acronym Manager";
    }

    /**
     * Adds the Management page to WordPress
     *
     * @return void
     * */
    function add_pages() {
	$domain = Acronym_Manager::get_textdomain();

	// Add the Manage page
	add_management_page(__('Acronym Manager', $domain), __('Acronym Manager', $domain), "manage_options", __FILE__, array('Acronym_Manager', 'admin_page'));
    }

    /**
     * void management_handler ()
     *
     * handles actions for adding, editing, deleting, importing, and exporting acronyms
     *
     */
    function management_handler() {
	$domain = Acronym_Manager::get_textdomain();
	load_plugin_textdomain($domain, dirname( plugin_basename( __FILE__ ) ) );

	$title = __('Acronym Manager', $domain);
	$parent_file = 'tools.php?page=' . Acronym_Manager::get_parent_url();

	if ( substr($parent_file, 'acronym-manager') ) {
	    $acronyms = get_option('acronym_acronyms');

	    wp_reset_vars(array('action', 'acronym'));

	    if      (isset($_GET['delete-acronyms']))      $action = 'delete-acronyms';
	    else if (isset($_GET['delete_all']))           $action = 'delete_all';
	    else if (isset($_GET['search']))               $action = 'search';
	    else if (isset($_POST['import_acronyms']))     $action = 'import_acronyms';
	    else if (isset($_POST['import_acronym_file'])) $action = 'import_acronym_file';
	    else if (isset($_GET['export_acronyms']))      $action = 'export_acronyms';
	    else                                           $action = $_GET['action'];

	    switch ($action) {
		case 'add-acronym':
		    check_admin_referer('add_acronym');
		    
		    $acronym = $_GET['acronym'];
		    $fulltext = $_GET['fulltext'];
		    if (Acronym_Manager::update($acronym, $fulltext)) $message = 1;
		    else $message = 4;
		    wp_redirect("$parent_file&message=$message");
		    break;

		case 'edit-acronym':
		    $acronym = $_GET['acronym'];
		    $fulltext = $_GET['fulltext'];
                    
		    check_admin_referer('edit_acronym');
		    if (Acronym_Manager::update($acronym, $fulltext)) $message = 3;
		    else $message = 5;
		    wp_redirect("$parent_file&message=$message");
		    break;

		case 'delete-acronym':
		    check_admin_referer('delete_acronym');

		    if (!current_user_can('manage_categories')) $message = 22;
                    else {
		        $acronym = $_GET['acronym'];
    		        Acronym_Manager::delete($acronym);
		        $message = 2;
                    }
		    wp_redirect("$parent_file&message=$message");
		    break;

		case 'delete-acronyms':
		    check_admin_referer('delete-acronyms');

		    if (!current_user_can('manage_categories')) $message = 22;
                    else {
 		        $acronyms = $_GET['delete_acronyms'];
		        foreach ((array) $acronyms as $acronym) Acronym_Manager::delete($acronym);
		        if (1 < ( count($acronyms) )) $message = 6;
		        else $message = 2;
                    }
		    wp_redirect("$parent_file&message=$message");
		    break;

		case 'delete_all':
		    check_admin_referer('delete-acronyms');

		    if (!current_user_can('manage_categories')) $message = 22;
                    else {
                        Acronym_Manager::delete_all();
                        $message = 9;
                    }
		    wp_redirect("$parent_file&message=$message");
		    break;

                case 'import_acronyms':
                    $acronym_count = Acronym_Manager::import_acronyms($_POST['acronym_import']);
                    $message = 7;
		    wp_redirect("$parent_file&message=$message&acronym_count=$acronym_count");
                    break;

                case 'import_acronym_file':
                        if ($_FILES["acronym_file_name"]["error"] > 0) {
                            $message = 20;
                        } else {
                            $acronym_count = Acronym_Manager::import_acronyms( file_get_contents($_FILES['acronym_file_name']['tmp_name']) );
                            $message = 21;
                            wp_redirect("$parent_file&message=$message&acronym_count=$acronym_count");
                            break;
                        }
		    wp_redirect("$parent_file&message=$message");
                    break;

                case 'export_acronyms':
                    $message = 8;
		    wp_redirect(Acronym_Manager::export_acronyms());
		    break;

		case 'search':
		    $s = '&s=' . urlencode($_GET['search']);
		    $p = isset($_GET['p']) ? '&p=' . $_GET['p'] : '';
		    $n = isset($_GET['n']) && 15 != $_GET['n'] ? '&n=' . $_GET['n'] : '';
		    wp_redirect("$parent_file$s$p$n");
		    break;
	    }
	}
    }


function admin_tabs($current) { 

    $tabs = array( 'manage' => __('Manage Collection'), 'import' => __('Import'), 'export' => __('Export') ); 
    $links = array(); 

    foreach( $tabs as $tab => $name ) { 
        if ( $tab == $current ) {
            $links[] = "<a class='nav-tab nav-tab-active' href='?page=" . Acronym_Manager::get_parent_url() . "&tab=$tab'>$name</a>"; 
        } else { 
            $links[] = "<a class='nav-tab' href='?page=" . Acronym_Manager::get_parent_url() . "&tab=$tab'>$name</a>"; 
        }
    }

    echo '<h2>'; 
    foreach ( $links as $link ) echo $link; 
    echo '</h2>'; 

}



// Function to generate options page

function admin_page() {

	global $pagenow;

   if ( isset ( $_GET['tab'] ) ) $current_tab = $_GET['tab'];
   else $current_tab = 'manage';
   
   //display the tab menu, setting the active tab to $current_tab
   Acronym_Manager::admin_tabs($current_tab);
	
	//display any messages reporting success/failure of functions
   if ( isset ($_GET['message']) ) Acronym_Manager::check_messages($_GET['message']);

   ?>
   <div class="wrap">
	    <div id="icon-edit" class="icon32"><br/></div>
	    <h2>
	 <?php

    if ( isset ( $_GET['tab'] ) ) {
        $tab = $_GET['tab']; 
    } else { 
        $tab = 'manage'; 
    }

    //set content of page based on which tab is active
    switch ( $tab ) {
        case 'manage' : 
            Acronym_Manager::manage_acronyms(); 
            break; 
        case 'import' :
            Acronym_Manager::manage_imports(); 
            break; 
        case 'export' :
            Acronym_Manager::manage_exports(); 
            break;
    }
    ?> <div class="wrap"> <?php

}

function manage_imports() {
     _e('Import', $domain); ?></h2><br class="clear" />
     <br />
	    <h3>Import Acronym File</h3>
            <br />
            <form name="import_acronym_file" id="import_acronym_file" method="post" action="" enctype="multipart/form-data">
	        <input type="hidden" name="action" value="import_acronym_file" />
	        <input type="hidden" name="page" value='<?php echo Acronym_Manager::get_parent_url() ?>'/>
	        <div class="form-field form-required">
                    <input type="hidden" name="MAX_FILE_SIZE" value="100000" />
                    <label for="acronym_file_name"><?php _e('Select file containing ACRONYM==definition;; pairs.  Extension .amf or .txt required.') ?></label>
                    <input type="file" id="acronym_file_name" name="acronym_file_name" size="20" onchange="check_file()">
	        </div>
	        <p class="submit">
	            <input type="submit" name="import_acronym_file" value="<?php _e('Import file') ?>" />
	        </p>
	    </form>
            <br />
	    <h3><?php _e('Bulk Add Acronym') ?></h3>
            <br />
            <form name="import_acronyms" id="import_acronyms" method="post" action="">
	        <input type="hidden" name="action" value="import_acronyms" />
	        <input type="hidden" name="page" value='<?php echo Acronym_Manager::get_parent_url() ?>'/>
	        <div class="form-field form-required">
                    <label for="acronym_import"><?php _e('Enter acronyms here (ACRONYM==definition;;):') ?></label>
		    <textarea name="acronym_import" id="acronym_import" type="textbox" rows="10" cols="20"></textarea>
	        </div>
                     <p class="submit">
	                <input type="submit" name="import_acronyms" value="<?php _e('Import Acronyms') ?>" />
	            </p>
            </form>

<?php 
}

function manage_exports() {
     _e('Export', $domain);
     $acronym_export = Acronym_Manager::export_acronyms();
     ?>
     </h2><br class="clear" />
     <form name="export_acronym_file" id="export_acronym_file" method="post" action="<?php echo WP_PLUGIN_URL . '/acronym-manager/php/acronym-export.php' ?>" class="">
	  <input type="hidden" name="page" value="<?php echo Acronym_Manager::get_parent_url() ?>"/>
	  <input type="hidden" name="action" value="export_acronyms" />
	  <input type="hidden" name="acronym-export" value="<?php echo $acronym_export; ?>" />
          <input type="submit" name="export_acronyms" value="<?php _e('Download library as file') ?>" />
     </form>
     <br />
     <br />
     <a href class="copy" title="<?php echo $acronym_export; ?>"><?php _e('Copy to Clipboard (only works in some browsers)') ?></a>
     <br />
     <div class="form-field form-required">
     <textarea name="acronym_export" id="acronym_export" type="textbox" rows="20" cols="50"><?php echo Acronym_Manager::export_acronyms(); ?></textarea>
     </div>
<?php 
}


    /**
     * Manage Acronyms page
     *
     * @return void
     * */
    function manage_acronyms() {
	$domain = Acronym_Manager::get_textdomain();
	
	// Retrieve and set pagination information
	$s = isset($_GET['s']) ? urldecode($_GET['s']) : ''; // Number of acronyms per page
	$p = isset($_GET['p']) && is_numeric($_GET['p']) && 0 < $_GET['p'] ? $_GET['p'] : 1; // Which page to display
	$n = 15; // Default number of acronyms to show per page
	if (0 == $n)
	    $t = 1;
	else
	    $t = ceil(Acronym_Manager::count_acronyms($s) / $n); // Total number of pages, rounded up to nearest integer
   _e('Collection Management', $domain); ?>
	    </h2><br class="clear" />
<!--SEARCH--><form class="search-form" method="get" action="">
		<p class="search-box" id="post-search">
		    <input type="hidden" name="page" value="<?php echo Acronym_Manager::get_parent_url() ?>"/>
		    <input type="text" id="post-search-input" name="search" value="<?php echo attribute_escape(stripslashes($_GET['s'])); ?>" />
		    <input type="submit" value="<?php _e('Search acronyms', $domain); ?>" class="button" />
            </form>
	        </p>
	    <br class="clear" />
	    <div id="col-container">
		<div id="col-right">
		    <div class="col-wrap">
			<form id="posts-filter" action="" method="get">
			    <input type="hidden" name="page" value="<?php echo Acronym_Manager::get_parent_url() ?>"/>
	<?php
	Acronym_Manager::show_pagination_bar($s, $n, $p, $t, true);
	Acronym_Manager::show_acronym_list($s, $n, $p);
	Acronym_Manager::show_pagination_bar($s, $n, $p, $t);
	?>
			</form>
		    </div>
		</div>
		<div id="col-left">
		    <div class="col-wrap">
			<?php
			if ('edit' == $_GET['action'] && !empty($_GET['acronym'])) {
			    Acronym_Manager::add_acronym_form(urldecode($_GET['acronym']), urldecode($_GET['fulltext']));
			} else {
			    Acronym_Manager::add_acronym_form();
			}
			?>
		    </div>
		</div>
	</div>
	<?php
    }

    function theme_html5_check() {
	$doctype = '<!DOCTYPE html>';
	$header = file_get_contents(get_template_directory() . '/header.php');
	if (strpos($header, $doctype) !== false) {
	    update_option('acronym_html5', 1);
	} else {
	    update_option('acronym_html5', 0);
	}
    }

    /* string acronym_replace ( string $text, array $acronyms )
     *
     * Replaces known acronyms in $text with appropriate <acronym> acronyms.
     * Note: acronym replacement is case-sensitive.
     */

    function acronym_replace($text) {
	$acronyms = get_option('acronym_acronyms');
	$html5 = get_option('acronym_html5');
	$text = " $text ";
	if ($html5 == 1) {
	    foreach ($acronyms as $acronym => $fulltext) {

		$text = preg_replace("|(?!<[^<>]*?)(?<![?.&])\b$acronym\b(?!:)(?![^<>]*?>)|msU", "<abbr title=\"$fulltext\">$acronym</abbr>", $text);
	    }
	} else {
	    foreach ($acronyms as $acronym => $fulltext) {
		$text = preg_replace("|(?!<[^<>]*?)(?<![?.&])\b$acronym\b(?!:)(?![^<>]*?>)|msU", "<acronym title=\"$fulltext\">$acronym</acronym>", $text);
	    }
	}

	$text = trim($text);
	return $text;
    }

    /* string show_acronym_list ( string $s, int $n, int $p )
     *
     * Displays the list of acronyms, filtered by search term $s and showing page # $p based on $n per page
     */

    function show_acronym_list($s, $n, $p = '1') {
	$domain = Acronym_Manager::get_textdomain();

	// Sort the acronyms appropriately
	$acronyms = get_option('acronym_acronyms');
	uksort($acronyms, "strnatcasecmp");
	?>
	<table class="widefat">
	    <thead>
		<tr>
		    <th scope="col" class="check-column"><span class="acronym-tooltip" title="<?php _e('Select all acronyms', $domain) ?>"><input type="checkbox" onclick="checkAll(document.getElementById('posts-filter'));" /></span></th>
		    <th scope="col"><?php _e('Acronym', $domain) ?></th>
		    <th scope="col"><?php _e('Full', $domain) ?></th>
		</tr>
	    </thead>
	    <tfoot>
		<tr>
		    <th scope="col" class="check-column"><span class="acronym-tooltip" title="<?php _e('Select all acronyms', $domain) ?>"><input type="checkbox" onclick="checkAll(document.getElementById('posts-filter'));" /></span></th>
		    <th scope="col"><?php _e('Acronym', $domain) ?></th>
		    <th scope="col"><?php _e('Full', $domain) ?></th>
		</tr>
	    </tfoot>
	    <tbody id="the-list" class="list:acronym">
		<?php
		$index_start = $n * ( $p - 1 );
		$index_end = $n * $p;
		$index = 0;
		foreach ($acronyms as $acronym => $fulltext) {
		    if (( '' == $s ) || ( ( false !== strpos(strtolower($acronym), strtolower($s)) ) || ( false !== strpos(strtolower($fulltext), strtolower($s)) ) )) {
			if (0 == $n || (++$index >= $index_start && $index <= $index_end )) {
			    ?>
		    	<tr class="iedit<?php if (0 == $i++ % 2)
				echo ' alternate' ?>">
		    	    <th scope="row" class="check-column">
		    		<input type="checkbox" name="delete_acronyms[]" value="<?php echo $acronym ?>" id="select-<?php echo $acronym ?>"/>
		    	    </th>
		    	    <td class="name column-name">
		    		<label for="select-<?php echo $acronym ?>" style="display:block">
		    		    <strong>
		    			<span class="acronym-tooltip" title="<?php printf(__("Edit &quot;%s&quot;", $domain), $acronym) ?>">
		    <?php echo $acronym ?>
		    			</span>
		    		    </strong>
		    		</label>
		    		<div class="row-actions">
		    		    <span class="edit">
		    			<a href="tools.php?page=<?php echo Acronym_Manager::get_parent_url() ?>&amp;action=edit&amp;acronym=<?php echo urlencode($acronym) ?>&amp;fulltext=<?php echo urlencode($fulltext) ?>">
		    <?php _e('Edit'); ?>
		    			</a>
		    			|
		    		    </span>
		    		    <span class="delete">
					    <?php
					    $link = 'tools.php?page=' . Acronym_Manager::get_parent_url() . '&amp;action=delete-acronym&amp;acronym=' . urlencode($acronym);
					    $link = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($link, 'delete_acronym') : $link;
					    ?>
		    			<a class="submitdelete" href="<?php echo $link ?>" onclick="if ( confirm('<?php _e("You are about to delete this acronym \'$acronym\'.\\n \'Cancel\' to stop, \'OK\' to delete.", $domain) ?>') ) { return true;}return false;">
		    <?php _e('Delete'); ?>
		    			</a>
		    		    </span>
		    		</div>
		    	    </td>
		    	    <td><label for="select-<?php echo $acronym ?>" style="display:block"><?php echo $fulltext ?></label></td>
		    	</tr>
			    <?php
			}
		    }
		}
		?>
	    </tbody>
	</table>
	<?php
    }

    function show_pagination_bar($s, $n, $p, $t, $filter = false) {

	$domain = Acronym_Manager::get_textdomain();
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
	<div class="tablenav">
	    <div class="alignleft actions">
		<input type="submit" value="<?php _e('Delete') ?>" name="delete-acronyms" class="button-secondary delete" onclick="if ( confirm('<?php _e("You are about to delete the selected acronyms.\\n \'Cancel\' to stop, \'OK\' to delete.", $domain) ?>') ) { return true;}return false;"/>
		<input type="submit" value="<?php _e('Delete All') ?>" name="delete_all" class="button-secondary delete" onclick="if ( confirm('<?php _e("You are about to delete ALL acronyms in the collection.\\n \'Cancel\' to stop, \'OK\' to delete.", $domain) ?>') ) { return true;}return false;"/>
	<?php if ($filter) {
	    wp_nonce_field('delete-acronyms');
	} ?>
	    </div>
	    <div class="tablenav-pages">
		<?php
		// Display pagination links
		$page_links = paginate_links(array(
		    'base' => add_query_arg('p', '%#%'),
		    'format' => '',
		    'total' => $t,
		    'current' => $p,
		    'add_args' => $n
			));
		if (0 < $n && $page_links) {
		    echo '<div class="tablenav-pages">';
		    $range = $n * ( $p - 1 ) + 1 . '-' . $n * $p;
		    $total = Acronym_Manager::count_acronyms($s);
		    echo '<span class="displaying-num">';
		    _e("Displaying $range of $total");
		    echo '</span>';

		    echo "$page_links</div>";
		}
		?>
	    </div>
	</div>
	<br class="clear" />
	<?php
    }

    /* void add_acronym_form ( string $acronym, string $fulltext )
     *
     * Display the form for adding or editing acronyms
     */

    function add_acronym_form($acronym = '', $fulltext = '') {
	$domain = Acronym_Manager::get_textdomain();

	if (!empty($acronym)) {
	    $heading = __('Edit Acronym', $domain);
	    $submit_text = __('Edit Acronym', $domain);
	    $form = '<form name="editacronym" id="editacronym" method="get" action="" class="validate">';
	    $action = 'edit-acronym';
	    $nonce_action = 'edit_acronym';
	} else {
	    $heading = __('Add Acronym', $domain);
	    $submit_text = __('Add Acronym', $domain);
	    $form = '<form name="addacronym" id="addacronym" method="get" action="" class="add:the-list: validate">';
	    $action = 'add-acronym';
	    $nonce_action = 'add_acronym';
	}
	?>
	<div class="form-wrap">
	    <h3><?php echo $heading ?></h3>
	    <div id="ajax-response"></div>
	    <?php echo $form ?>
	    <input type="hidden" name="page" value="<?php echo Acronym_Manager::get_parent_url() ?>"/>
	    <input type="hidden" name="action" value="<?php echo $action ?>" />
	<?php wp_original_referer_field(true, 'previous');
	wp_nonce_field($nonce_action); ?>
	    <div class="form-field form-required">
		<label for="acronym"><?php _e('Acronym', $domain) ?></label>
		<input name="acronym" id="acronym" type="text" onBlur="checkAcronym(this.value)" value="<?php echo attribute_escape($acronym); ?>" size="20" <?php if ('edit-acronym' == $action)
	    echo 'readonly="readonly"'; ?>/>
	    </div>
	    <div class="form-field form-required">
		<label for="fulltext"><?php _e('Definition', $domain) ?></label>
		<input name="fulltext" id="fulltext" type="text" value="<?php echo attribute_escape($fulltext); ?>" size="80" />
	    </div>
	    <p class="submit">
	<?php if ('edit-acronym' == $action)
	    echo '<a accesskey="c" title="' . __('Cancel') . '" class="cancel button-secondary alignright" href="tools.php?page=' . Acronym_Manager::get_parent_url() . '">' . __('Cancel') . '</a>' ?>
		<input type="submit" class="button<?php if ('edit-acronym' == $action)
	    echo '-primary'; else
	    echo '-secondary'; ?> alignleft" name="submit" value="<?php echo $submit_text ?>" />
	    </p>
	</form>
	</div>
<?php
}

    /* boolean update ( string $acronym, string $fulltext )
     *
     * Add a new acronym to the list, or edit an existing one
     */

    function update($acronym, $fulltext) {
	if (empty($acronym) || empty($fulltext)) return false;
        else {
	    $acronyms = get_option('acronym_acronyms');
	    $acronyms[$acronym] = $fulltext;
	    if ( update_option('acronym_acronyms', $acronyms) ) return true;
	    else return false;
	}
    }

    /* boolean delete ( string $acronym )
     *
     * Delete an existing acronym
     */

    function delete($acronym) {
	$acronyms = get_option('acronym_acronyms');
	if (array_key_exists($acronym, $acronyms)) {
	    unset($acronyms[$acronym]);
	    update_option('acronym_acronyms', $acronyms);
	    return true;
	}
	else
	    return false;
    }

    /* boolean delete_all ()
     *
     * Delete all existing acronym
     */

    function delete_all() {
		$acronyms = get_option('acronym_acronyms');
	        foreach ($acronyms as $acronym => $fulltext){
		    unset($acronyms[$acronym]);
	        }
		update_option('acronym_acronyms', $acronyms);
	    }
	
	    /* int count_acronyms ( string $s )
	     *
	     * Get the number of acronyms based on the search term if provided
	     */
	
	    function count_acronyms($s = '') {
		$acronyms = get_option('acronym_acronyms');
		if (empty($s))
		    return count($acronyms);
		else {
		    $index = 0;
		    foreach ($acronyms as $acronym => $fulltext) {
			if (false !== strpos(strtolower($acronym), strtolower($s)) || ( false !== strpos(strtolower($fulltext), strtolower($s)) )) {
			    $index++;
			}
		    }
		    return $index;
		}
    }

    /* string get_parent_url ()
     *
     */

    function get_parent_url() {
	     return $_GET['page'];
    }


	/* export_acronym ()
 	* 
 	* create a temporary file, load with acronym output, and return URL to file
 	*/
 	
    function export_acronyms() {
        $export_file_contents = "";
        $domain = Acronym_Manager::get_textdomain();

        // Sort the acronyms appropriately
        $acronyms = get_option('acronym_acronyms');
        uksort($acronyms, "strnatcasecmp");

        foreach ($acronyms as $acronym => $fulltext) {
            $export_file_contents .= $acronym . "==" . $fulltext . ";;";
        }

        return $export_file_contents;
    }


	/* import_acronym ()
 	* 
 	* parse acronym file of format ARC==definition;;\n
 	*/
 	
    function import_acronyms($acronym_import_string) {
        $a_acronyms = explode( ';;', $acronym_import_string);
        $acronym_count = 0;
        
        foreach ($a_acronyms as $key => $current_acronym) {
            list($acronym, $fulltext) = explode( '==', $current_acronym);
            $acronym = Acronym_Manager::str_trim($acronym);
            $acronym_added = Acronym_Manager::update($acronym, $fulltext);
            if ($acronym_added) $acronym_count++;
        }
        return $acronym_count;
    }

    /*  str_trim ($to_trim)
     *
     *  functions like PERL trim, removes white space around string
     */
    function str_trim($to_trim) {
        $to_trim = str_replace(' ', '', $to_trim);
        $to_trim = str_replace('\n', '', $to_trim);
        return $to_trim;
    }
    
    
	/* print_glossary_list ()
	 * 
	 * create a table, load with acronym output, and return html code
	 */
	 
	function print_glossary_list() {
		$domain = Acronym_Manager::get_textdomain();
	
		// Sort the acronyms appropriately
		$acronyms = get_option('acronym_acronyms');
		uksort($acronyms, "strnatcasecmp");
	
	        ?> <table border="0"> <?php
	
		foreach ($acronyms as $acronym => $fulltext) {
			?> <tr><td><strong> <?php
			echo $acronym;
			?> </strong></td><td> <?php
			echo $fulltext;
			?> </td></tr> <?php
		}
	
		?> </table> <?php
	}
	
    function check_messages($message) {
        // Set any error/notice messages based on the 'message' GET value
        $messages[1] = __('Acronym added.', $domain);
        $messages[2] = __('Acronym deleted.', $domain);
        $messages[3] = __('Acronym updated.', $domain);
        $messages[4] = __('Acronym not added.', $domain);
        $messages[5] = __('Acronym not updated.', $domain);
        $messages[6] = __('Acronyms deleted.', $domain);
        $messages[7] = __( $_GET['acronym_count'] . ' acronyms successfully imported.', $domain);
        $messages[8] = __('Acronyms successfully exported.', $domain);
        $messages[9] = __('All acronyms deleted from collection.', $domain);
        $messages[20] = __('Import failed: could not access file.', $domain);
        $messages[21] = __('File successfully imported, ' . $_GET['acronym_count'] . ' acronyms added.', $domain);
        $messages[22] = __('You do not have the user privileges to perform this action.');
	 
        if (isset($message)) {
            ?> <div id="message" class="updated fade"><p><?php echo $messages[$message] ?></p></div> <?php
        }
    }
}

/* END CLASS acronyms */

?>