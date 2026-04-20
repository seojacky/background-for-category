<?php
/*
* Plugin Name: WP Booster: Background for Category
* Description: Sets background images for top-level categories. Images must be placed in /images/backgrounds/ and named by slug, e.g. background-wot.jpg. Requires get_top_term() function.
* Version: 1.0
* Author: seojacky
* Author URI: https://t.me/big_jacky
* GitHub Plugin URI: https://github.com/seojacky/background-for-category
* Plugin URI: https://github.com/seojacky/background-for-category
* Text Domain: background-for-category
* Domain Path: /languages
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) { return; }

define( 'BFC_VERSION', '1.0' );
define( 'BFC_FILE', __FILE__ );
define( 'BFC_DIR', __DIR__ );
define( 'BFC_FOLDER', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'BFC_SLUG', 'background-for-category' );

add_action( 'plugins_loaded', function() {
	load_plugin_textdomain( BFC_SLUG, false, dirname( plugin_basename( BFC_FILE ) ) . '/languages/' );
} );

/* Plugin settings links */
add_filter( 'plugin_action_links_' . plugin_basename( BFC_FILE ), function( $links ) {
	$links[] = '<a href="' .
		admin_url( 'options-general.php?page=' . BFC_SLUG ) .
		'">' . __( 'Settings' ) . '</a>';
	$links[] = '<a href="https://t.me/big_jacky">' . __( 'Author' ) . '</a>';
	return $links;
} );

/* Plugin extra links */
add_filter( 'plugin_row_meta', function( $links, $file ) {
	if ( plugin_basename( BFC_FILE ) !== $file ) {
		return $links;
	}

	$meta_links = array(
		'<a href="https://github.com/seojacky/background-for-category#readme" target="_blank">' . __( 'FAQ', BFC_SLUG ) . '</a>',
		__( 'Rate us:', BFC_SLUG ) . " <span class='rating-stars'><a href='https://github.com/seojacky/background-for-category' target='_blank' data-rating='5' title='" . __( 'Fantastic!', BFC_SLUG ) . "'><span class='dashicons dashicons-star-filled' style='color:#ffb900 !important;'></span></a><span>",
	);

	return array_merge( $links, $meta_links );
}, 10, 2 );


/**
 * Add plugin settings page
 */
add_action( 'admin_menu', 'add_plugin_page_background_for_category' );

function add_plugin_page_background_for_category() {
	add_options_page(
		'Настройки Фона категории',
		'Фон категории',
		'manage_options',
		BFC_SLUG,
		'background_for_category_options_page_output'
	);
}


add_action( 'wp_head', 'background_for_category', 5 );
function background_for_category( $post_id ) {

	$site_url = get_site_url();

	$default_bg_clr = get_option( 'background_for_category_option' );
	$default_bg_clr = $default_bg_clr ? $default_bg_clr['input'] : '#000000';

	if ( is_home() ) {
		echo '<style>body {background:' . $default_bg_clr . ' url(' . $site_url . '/images/backgrounds/background-home.jpg) top center no-repeat !important;}</style>';
	} else {
		$top_term = get_top_term( 'category', $post_id );
		$slug     = $top_term->slug;

		if ( ! empty( $slug ) ) {
			echo '<style>body {background: ' . $default_bg_clr . ' url(' . $site_url . '/images/backgrounds/background-' . $slug . '.jpg) top center no-repeat !important;}</style>';
		} else {
			echo '<style>body {background: ' . $default_bg_clr . ' !important;}</style>';
		}
	}
}


function background_for_category_options_page_output() {
	?>
	<style>textarea {
		border: 1px solid #b1aeae;
		box-shadow: 1px 1px 12px -2px;
	}</style>
	<div class="wrap">
		<h2><?php echo get_admin_page_title(); ?></h2>

		<div style="font-size: 12pt;">
			<b>Внимание!</b> Требует установки плагина или функции <b>get_top_term()</b>.<br>
			Плагин устанавливает фоны для рубрик.<br>
			Изображения должны быть в папке /images/backgrounds/.<br>
			Название изображения устанавливаются жёстко по шаблону. Например, для рубрики верхнего уровня сo slag равным <b>wot</b>, название файла фона background-<b>wot</b>.jpg.
		</div>

		<form action="options.php" method="POST">
			<?php
				settings_fields( 'option_group' );
				do_settings_sections( 'background_for_category_page' );
				submit_button();
			?>
		</form>
	</div>
	<?php
}


/**
 * Register plugin settings
 */
add_action( 'admin_init', 'background_for_category_plugin_settings' );
function background_for_category_plugin_settings() {
	register_setting( 'option_group', 'background_for_category_option', 'background_for_categorysanitize_callback' );

	add_settings_section( 'section_id', 'Основные настройки', '', 'background_for_category_page' );

	add_settings_field( 'background_for_category_field1', 'Дефолтный цвет фона', 'fill_background_for_category_field1', 'background_for_category_page', 'section_id' );
	add_settings_field( 'background_for_category_field2', 'Запасная опция', 'fill_background_for_category_field2', 'background_for_category_page', 'section_id' );
}

function fill_background_for_category_field1() {
	$val = get_option( 'background_for_category_option' );
	$val = $val ? $val['input'] : null;
	?>
	<input type="text" name="background_for_category_option[input]" value="<?php echo esc_attr( $val ); ?>" />
	<div style="display: inline-block; margin-left: 10px; background-color: <?php echo esc_attr( $val ); ?>; height: 20px; width: 20px;"></div>
	<div>Формат: #232323</div>
	<?php
}

function fill_background_for_category_field2() {
	$val = get_option( 'background_for_category_option' );
	$val = $val ? $val['checkbox'] : null;
	?>
	<label><input type="checkbox" name="background_for_category_option[checkbox]" value="1" <?php checked( 1, $val ); ?> /> отметить</label>
	<?php
}

function background_for_categorysanitize_callback( $options ) {
	foreach ( $options as $name => &$val ) {
		if ( $name == 'input' ) {
			$val = htmlspecialchars( $val, ENT_QUOTES );
		}

		if ( $name == 'checkbox' ) {
			$val = intval( $val );
		}
	}

	return $options;
}
