<?php
/*
Plugin Name:  Simple Promo Banner
Description:  An incredibly simple and clean banner that works on all wordpress sites
Version:      1.1.0
Author:       Brandyn Lordi
Author URI:   https://digidev.io
License:      MIT
License URI:  https://opensource.org/licenses/MIT
*/

defined( 'ABSPATH' ) or die( 'DENIED!' );

require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/BrandynL/wordpress-simple-promo-banner-plugin/',
	__FILE__,
	'simple-promo-banner.php'
);

add_action('admin_menu','simple_promo_banner_menu');
function simple_promo_banner_menu(){
    add_options_page(
        'Simple Promo Banner',
        'Simple Promo Banner',
        'manage_options',
        'simple-promo-banner.php',
        'simple_promo_banner_options_page'
    );
}

function simple_promo_banner_options_page(){
    global $promo_banner_options;
    $available_colors = ["AliceBlue","AntiqueWhite","Aqua","Aquamarine","Azure","Beige","Bisque","Black","BlanchedAlmond","Blue","BlueViolet","Brown","BurlyWood","CadetBlue","Chartreuse","Chocolate","Coral","CornflowerBlue","Cornsilk","Crimson","Cyan","DarkBlue","DarkCyan","DarkGoldenRod","DarkGray","DarkGrey","DarkGreen","DarkKhaki","DarkMagenta","DarkOliveGreen","Darkorange","DarkOrchid","DarkRed","DarkSalmon","DarkSeaGreen","DarkSlateBlue","DarkSlateGray","DarkSlateGrey","DarkTurquoise","DarkViolet","DeepPink","DeepSkyBlue","DimGray","DimGrey","DodgerBlue","FireBrick","FloralWhite","ForestGreen","Fuchsia","Gainsboro","GhostWhite","Gold","GoldenRod","Gray","Grey","Green","GreenYellow","HoneyDew","HotPink","IndianRed","Indigo","Ivory","Khaki","Lavender","LavenderBlush","LawnGreen","LemonChiffon","LightBlue","LightCoral","LightCyan","LightGoldenRodYellow","LightGray","LightGrey","LightGreen","LightPink","LightSalmon","LightSeaGreen","LightSkyBlue","LightSlateGray","LightSlateGrey","LightSteelBlue","LightYellow","Lime","LimeGreen","Linen","Magenta","Maroon","MediumAquaMarine","MediumBlue","MediumOrchid","MediumPurple","MediumSeaGreen","MediumSlateBlue","MediumSpringGreen","MediumTurquoise","MediumVioletRed","MidnightBlue","MintCream","MistyRose","Moccasin","NavajoWhite","Navy","OldLace","Olive","OliveDrab","Orange","OrangeRed","Orchid","PaleGoldenRod","PaleGreen","PaleTurquoise","PaleVioletRed","PapayaWhip","PeachPuff","Peru","Pink","Plum","PowderBlue","Purple","Red","RosyBrown","RoyalBlue","SaddleBrown","Salmon","SandyBrown","SeaGreen","SeaShell","Sienna","Silver","SkyBlue","SlateBlue","SlateGray","SlateGrey","Snow","SpringGreen","SteelBlue","Tan","Teal","Thistle","Tomato","Turquoise","Violet","Wheat","White","WhiteSmoke","Yellow","YellowGreen"];
    // check banner text settings
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['promo-banner-title'])){
        $promo_banner_options['promo-banner-title'] = $_POST['promo-banner-title'];
        if(trim($_POST['promo-banner-text'], ' ') != '') {
            $promo_banner_options['promo-banner-text'] = trim($_POST['promo-banner-text'], ' ');
        }
        if(trim($_POST['promo-banner-link'], ' ') != '') {
            $promo_banner_options['promo-banner-link'] = trim($_POST['promo-banner-link'], ' ');
        }

        if ($_POST['start-date'] != ''){
            $promo_banner_options['start-date'] = $_POST['start-date'];
        }

        if ($_POST['end-date'] != ''){
            $promo_banner_options['end-date'] = $_POST['end-date'];
        }

        if ($_POST['hide-promo-banner'] && $_POST['hide-promo-banner'] == 'on'){
            $promo_banner_options['hide-promo-banner'] = true;
        } else {
            $promo_banner_options['hide-promo-banner'] = false;
        }

        /**
         * use regex to get the link exclusions
         */
        $exclusions = [];
        foreach ($_POST as $f=>$v){
            if (strpos($f, 'banner-exclusion-url') > -1){
                $exclusions[] = $v;
            }
        }
        if (count($exclusions) > 0){
            $promo_banner_options['excluded-urls'] = $exclusions;
        }

        $promo_banner_options['background-color'] = $_POST['background-color'];
        $promo_banner_options['text-color'] = $_POST['text-color'];

        $promo_banner_options['last-updated'] = $_POST['updated'];

        update_option('simple-promo-banner', $promo_banner_options);

        // show values in admin areas
        if($promo_banner_options == ''){
            $promo_banner_options = get_option('simple-promo-banner', true);
        }
    }

require ('templates/backend-wrapper.php');
}

// display the banner on the front end
add_action('get_header', 'display_simple_promo_banner');
function display_simple_promo_banner(){
    $promo_banner_options = get_option('simple-promo-banner', true);
    if ($promo_banner_options['hide-promo-banner'] != true) {
        if (trim($promo_banner_options['start-date']) != '' || $promo_banner_options['start-date'] == null){ // start date not set, continue to display...
            //check start date is in the past and end date is in the future
            if( strtotime(str_replace(['-', '/'], '', $promo_banner_options['start-date'])) <= strtotime(Date('Ymd'))) { // start is today or before today
                if (
                    strtotime(str_replace(['-', '/'], '', $promo_banner_options['end-date'])) > strtotime(Date('Ymd'))
                    || trim($promo_banner_options['end-date']) == ''
                    || $promo_banner_options['end-date'] == null
                ){ // end date is in the future or not set
                    $show_banner = true;
                    foreach($promo_banner_options['excluded-urls'] as $exclude_url){
                        if ($show_banner == true){
                            if ((strpos($_SERVER['REQUEST_URI'], $exclude_url) > -1) == true){
                                $show_banner = false;
                            }
                        }
                    }
                    if ($show_banner == true){
                        require('templates/frontend.php');
                    }
                }
            }
        }
    }
}
// plugin styles
add_action('wp_enqueue_scripts', 'simple_promo_banner_scripts');
function simple_promo_banner_scripts(){
    wp_enqueue_style('simple_banner_styles', plugins_url( '/css/simple-promo-banner-styles.css',  __FILE__  ));
    wp_enqueue_script('simple_banner_scripts', plugins_url( '/js/simple-promo-banner-scripts.js' , __FILE__  ), array('jquery', 'jquery-ui-effects-bounce'));
}
add_action( 'admin_enqueue_scripts', 'simple_promo_banner_admin_scripts' );
function simple_promo_banner_admin_scripts($hook) {
    if($hook != 'settings_page_simple-promo-banner') {
        return;
    }
    // wp_enqueue_style( 'simple_banner_admin_styles', plugins_url( 'simple-promo-banner/css/simple-promo-banner-admin-styles.css' ));
    wp_enqueue_style( 'simple_banner_admin_styles', plugins_url( '/css/simple-promo-banner-admin-styles.css', __FILE__ ));
    wp_enqueue_script( 'simple_banner_admin_scripts', plugins_url( '/js/simple-promo-banner-admin-scripts.js', __FILE__ ));
}