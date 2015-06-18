<?php
/*
Plugin Name: Tag Linker
Plugin URI: http://ssimsek.net
Description: This plugin adding links to post or page content that match tags in use.
Author: slmsmsk
Author URI: http://ssimsek.net
Version: 1.0
Text Domain: tag-linker
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


if(!defined('ABSPATH')) : exit; endif; // prevent the direct access


class taglinker{
    // Static Variables
    private static $tag_style = '';
    private static $tag_show_in_posts = 1;
    private static $tag_show_in_pages = 0;
	private static $tag_class = 'taglinker';
    
    public static $taglinker_tagid;
    public static $taglinker_tagname;
    public static $i = 0;

    // Constant Variables
    const tagstyle_on = 'taglinker_tagstyle'; // Tag style option name
    const tagposts_on = 'taglinker_showinposts'; // Tag Show in Posts option name
    const tagpages_on = 'taglinker_showinpages'; // Tag Show in Pages option name
	const tagclass_on = 'taglinker_tagclass';

    public static function activation(){
        if(!get_option(self::tagstyle_on)):
            add_option(self::tagstyle_on,self::$tag_style);
        else:
            self::$tag_style = get_option(self::tagstyle_on);
        endif;
        if(!get_option(self::tagposts_on)):
            add_option(self::tagposts_on,self::$tag_show_in_posts);
        else:
            self::$tag_show_in_posts = get_option(self::tagposts_on);
        endif;
        if(!get_option(self::tagpages_on)):
            add_option(self::tagpages_on,self::$tag_show_in_pages);
        else:
            self::$tag_show_in_pages = get_option(self::tagpages_on);
        endif;
		if(!get_option(self::tagclass_on)):
			add_option(self::tagclass_on,self::$tag_class);
		elsse:
			self::$tag_class = get_option(self::tagclass_on);
		endif;
        
    }
    public static function deactivation(){
        delete_option(self::tagpages_on);
        delete_option(self::tagposts_on);
        delete_option(self::tagstyle_on);
		delete_option(self::tagclass_on);
    }
    
    public static function admin_menu(){
        add_submenu_page('options-general.php', __('Tag Linker','tag-linker'), __('Tag Linker Settings','tag-linker'), 'manage_options', 'tag-linker-settings', array('taglinker','settings'));
    }
    
    public static function settings(){
?>
<h2 class="title"><?php _e('Tag Linker Settings','tag-linker') ?></h2>
<?php
    if($_POST):
            update_option(self::tagstyle_on,$_POST['tag_style']);
            if(isset($_POST['taglinker_posts'])): $show_posts = 1; else: $show_posts = 0; endif;
            update_option(self::tagposts_on,$show_posts);
            if(isset($_POST['taglinker_pages'])): $show_pages = 1; else: $show_pages = 0; endif;
            update_option(self::tagpages_on,$show_pages);
			if(isset($_POST['taglinker_class'])): $class = $_POST['taglinker_class']; else: $class = 'taglinker'; endif;
			update_option(self::tagclass_on,$class);
            echo '<div id="message" class="updated notice is-dismissible"><p>'.__('Settings saved.','tag-linker').'</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__('Hide this message.','tag-linker').'</span></button></div>';
    endif;
?>
<form action="" method="post">
    <table class="form-table">
	<tbody>
            <tr>
                <th><label for="tag_style"><?php _e('Tag Style','tag-linker') ?></label></th>
                <td>
                    <textarea name="tag_style" id="tag_style" cols="60" rows="10"><?php echo get_option(self::tagstyle_on) ?></textarea>
                    <p class="description" id="tagstyle-description"><?php _e('Write the custom css codes. Example: color:red; font-weight:bold;','tag-linker') ?></p>
                </td>
            </tr>
			<tr>
                <th><label for="tag_class"><?php _e('Tag Class','tag-linker') ?></label></th>
                <td>
					<input name="taglinker_class" id="tag_class" type="text" value="<?php echo get_option(self::tagclass_on) ?>" class="regular-text code">
					<p class="description" id="tagclass-description"><?php _e('Add a custom class name to link. Default: taglinker','tag-linker') ?></p>
				</td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Plugin are working on the following page : ','tag-linker') ?></th>
                    <td>
                    <fieldset>
                        <legend class="screen-reader-text">
                            <span><?php _e('Plugin are working on the following page : ','tag-linker') ?></span>
                        </legend>
                        <label for="taglinker_posts">
                            <input type="checkbox" name="taglinker_posts" id="taglinker_posts" value="<?php echo self::$tag_show_in_posts ?>" <?php echo get_option(self::tagposts_on) ? 'checked="checked"':null ?>/><?php _e('Posts','tag-linker') ?>
                        </label> <br />
                        <label for="taglinker_posts">
                            <input type="checkbox" name="taglinker_pages" id="taglinker_pages" value="<?php echo self::$tag_show_in_pages ?>" <?php echo get_option(self::tagpages_on) ? 'checked="checked"':null ?>/><?php _e('Pages','tag-linker') ?>
                        </label>
                    </fieldset>
                    </td>
                </tr>
        </tbody>
    </table>
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes','tag-linker') ?>"/></p>
</form>
<?php
    }
    
    public static function tag_to_link($content){
        $tags = get_tags();
            foreach ($tags as $tag):
                if((get_option(self::tagposts_on) and is_single()) or (get_option(self::tagpages_on) and is_page())):
                    $content = preg_replace('/ '.$tag->name.' /', ' <a class="'.get_option(self::tagclass_on).'" style="'.get_option(self::tagstyle_on).'" rel="tag" href="'.get_tag_link($tag->term_id).'">'.$tag->name.'</a> ', $content);
                endif;
            endforeach;
        return $content;
    }
    
    public static function pr_callback(){
        self::$i ++;
        return ' <a style="'.get_option(self::tagstyle_on).'" rel="tag" href="'.get_tag_link(self::$taglinker_tagid).'">'.self::$taglinker_tagname.'</a> ';
    }
    
    public static function load_textdomain(){
        load_plugin_textdomain( 'tag-linker', false, dirname(plugin_basename(__FILE__)));
    }
    
    static function links_to_plugins_page($links){
        $settings_link = '<a href="options-general.php?page=tag-linker-settings">'.__('Settings','tag-linker').'</a>';
            array_push( $links, $settings_link );
            return $links;
    }
    
}

$plugin = plugin_basename(__FILE__);
add_filter( "plugin_action_links_$plugin", array('taglinker','links_to_plugins_page') );
register_activation_hook(__FILE__,array('taglinker','activation'));
register_deactivation_hook(__FILE__,array('taglinker','deactivation'));
add_action('admin_menu',array('taglinker','admin_menu'));
add_filter('the_content',array('taglinker','tag_to_link'));
add_action( 'plugins_loaded', array('taglinker','load_textdomain'));
