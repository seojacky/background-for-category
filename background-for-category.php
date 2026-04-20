<?php
/*
* Plugin Name: WP Booster: Background for Category
* Description: Sets background images for top-level categories. Images are chosen from the WordPress media library. Requires get_top_term() function.
* Version: 1.3
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

define( 'BFC_VERSION', '1.3' );
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
		admin_url( 'admin.php?page=' . BFC_SLUG ) .
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
 * Admin notice if get_top_term() is missing
 */
add_action( 'admin_notices', 'background_for_category_admin_notice' );

function background_for_category_admin_notice() {
	if ( function_exists( 'get_top_term' ) ) {
		return;
	}
	?>
	<div class="notice notice-warning">
		<p>
			<strong>Background for Category:</strong>
			<?php esc_html_e( 'Функция', BFC_SLUG ); ?> <code>get_top_term()</code> <?php esc_html_e( 'не найдена. Фоны для рубрик и постов работать не будут. Добавьте функцию в', BFC_SLUG ); ?> <code>functions.php</code> <?php esc_html_e( 'или установите соответствующий плагин.', BFC_SLUG ); ?>
		</p>
	</div>
	<?php
}


/**
 * Create top-level WP Booster menu if not yet registered by another plugin
 */
add_action( 'admin_menu', 'background_for_category_create_admin_menu', 8 );

function background_for_category_create_admin_menu() {
	global $admin_page_hooks;
	if ( isset( $admin_page_hooks['wp-booster'] ) ) {
		return;
	}
	add_menu_page(
		esc_html__( 'WP Booster', BFC_SLUG ),
		esc_html_x( 'WP Booster', 'Menu item', BFC_SLUG ),
		'manage_options',
		'wp-booster',
		'background_for_category_options_page_output',
		'dashicons-backup',
		92.3
	);
}

/* Hide duplicate first submenu item (mirrors parent label) */
add_action( 'admin_head', function() {
	echo '<style>.toplevel_page_wp-booster li.wp-first-item { display: none; }</style>';
} );

/* Add submenu page under WP Booster */
add_action( 'admin_menu', function() {
	add_submenu_page(
		'wp-booster',
		'Настройки Фона категории',
		'Фон категории',
		'manage_options',
		BFC_SLUG,
		'background_for_category_options_page_output'
	);
}, 99 );


/**
 * Enqueue WordPress media uploader on plugin settings page only
 */
add_action( 'admin_enqueue_scripts', 'background_for_category_enqueue_admin_scripts' );

function background_for_category_enqueue_admin_scripts( $hook ) {
	if ( 'wp-booster_page_' . BFC_SLUG !== $hook ) {
		return;
	}
	wp_enqueue_media();
}


/**
 * Output background CSS in <head>
 * Uses autoloaded option — zero extra DB queries when option is set
 */
add_action( 'wp_head', 'background_for_category', 5 );

function background_for_category( $post_id ) {
	$option         = get_option( 'background_for_category_option', array() );
	$default_bg_clr = isset( $option['input'] ) ? $option['input'] : '';

	$images        = get_option( 'background_for_category_images', array() );
	$attachment_id = 0;

	if ( is_home() || is_front_page() ) {
		$attachment_id = isset( $images['home'] ) ? intval( $images['home'] ) : 0;
	} elseif ( function_exists( 'get_top_term' ) ) {
		$top_term = get_top_term( 'category', $post_id );
		if ( ! empty( $top_term ) ) {
			$attachment_id = isset( $images[ $top_term->term_id ] ) ? intval( $images[ $top_term->term_id ] ) : 0;
		}
	}

	$color_css = $default_bg_clr ? esc_attr( $default_bg_clr ) . ' ' : '';

	if ( $attachment_id ) {
		$url = wp_get_attachment_url( $attachment_id );
		if ( $url ) {
			echo '<style>body {background:' . $color_css . 'url(' . esc_url( $url ) . ') top center no-repeat !important;}</style>';
			return;
		}
	}

	if ( $color_css ) {
		echo '<style>body {background:' . $color_css . '!important;}</style>';
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
			Плагин устанавливает фоны для рубрик верхнего уровня.<br>
			Выберите изображения из медиабиблиотеки для каждой рубрики и главной страницы.
		</div>
		<div style="font-size: 11pt; margin-top: 8px; color: #646970;">
			<b><?php esc_html_e( 'Ограничения:', BFC_SLUG ); ?></b>
			<?php esc_html_e( 'страницы (page) не поддерживаются; при нескольких рубриках у поста основная рубрика (Yoast/RankMath) игнорируется — применяется рубрика с наименьшим ID.', BFC_SLUG ); ?>
			<a href="https://github.com/seojacky/background-for-category#readme" target="_blank"><?php esc_html_e( 'Подробнее в README', BFC_SLUG ); ?></a>
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
	register_setting( 'option_group', 'background_for_category_images', 'background_for_category_images_sanitize' );

	add_settings_section( 'section_id', 'Основные настройки', '', 'background_for_category_page' );
	add_settings_section( 'section_images', 'Фоновые изображения', '', 'background_for_category_page' );

	add_settings_field( 'background_for_category_field1', 'Дефолтный цвет фона', 'fill_background_for_category_field1', 'background_for_category_page', 'section_id' );
	add_settings_field( 'background_for_category_field2', 'Запасная опция', 'fill_background_for_category_field2', 'background_for_category_page', 'section_id' );
	add_settings_field( 'background_for_category_images_field', 'Изображения', 'background_for_category_images_field_render', 'background_for_category_page', 'section_images' );
}

function fill_background_for_category_field1() {
	$option = get_option( 'background_for_category_option', array() );
	$val    = isset( $option['input'] ) ? $option['input'] : '';
	$preview_style = $val ? ' background-color:' . esc_attr( $val ) . ';' : '';
	?>
	<input type="text" name="background_for_category_option[input]" value="<?php echo esc_attr( $val ); ?>" />
	<div style="display:inline-block;margin-left:10px;height:20px;width:20px;border:1px solid #ccc;<?php echo $preview_style; ?>"></div>
	<div>Формат: #232323. Оставьте пустым, чтобы не задавать цвет.</div>
	<?php
}

function fill_background_for_category_field2() {
	$option = get_option( 'background_for_category_option', array() );
	$val    = isset( $option['checkbox'] ) ? $option['checkbox'] : null;
	?>
	<label><input type="checkbox" name="background_for_category_option[checkbox]" value="1" <?php checked( 1, $val ); ?> /> отметить</label>
	<?php
}

function background_for_category_images_field_render() {
	$images = get_option( 'background_for_category_images', array() );

	$rows   = array();
	$rows[] = array(
		'key'   => 'home',
		'label' => 'Главная страница',
		'id'    => isset( $images['home'] ) ? intval( $images['home'] ) : 0,
	);

	$categories = get_categories( array(
		'parent'     => 0,
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	) );

	foreach ( $categories as $cat ) {
		$rows[] = array(
			'key'   => $cat->term_id,
			'label' => $cat->name,
			'id'    => isset( $images[ $cat->term_id ] ) ? intval( $images[ $cat->term_id ] ) : 0,
		);
	}

	echo '<table class="widefat striped" style="max-width:700px;">';
	echo '<thead><tr><th>Страница / Рубрика</th><th>Превью</th><th>Действия</th></tr></thead>';
	echo '<tbody>';

	foreach ( $rows as $row ) {
		$key         = esc_attr( $row['key'] );
		$att_id      = $row['id'];
		$preview_url = $att_id ? wp_get_attachment_image_url( $att_id, 'thumbnail' ) : '';
		$has_image   = ! empty( $preview_url );

		echo '<tr>';
		echo '<td>' . esc_html( $row['label'] ) . '</td>';
		echo '<td>';
		echo '<img src="' . esc_url( $preview_url ) . '" style="max-height:60px;max-width:120px;' . ( $has_image ? '' : 'display:none;' ) . '" class="bfc-preview-' . $key . '" />';
		echo '</td>';
		echo '<td>';
		echo '<input type="hidden" name="background_for_category_images[' . $key . ']" value="' . esc_attr( $att_id ) . '" class="bfc-input-' . $key . '" />';
		echo '<button type="button" class="button bfc-select" data-key="' . $key . '">Выбрать</button> ';
		echo '<button type="button" class="button bfc-remove" data-key="' . $key . '"' . ( $has_image ? '' : ' style="display:none;"' ) . '>Удалить</button>';
		echo '</td>';
		echo '</tr>';
	}

	echo '</tbody></table>';
	?>
	<script>
	(function($) {
		$(document).on('click', '.bfc-select', function(e) {
			e.preventDefault();
			var key   = $(this).data('key');
			var frame = wp.media({
				title    : 'Выберите фоновое изображение',
				button   : { text: 'Использовать' },
				multiple : false,
				library  : { type: 'image' }
			});
			frame.on('select', function() {
				var attachment = frame.state().get('selection').first().toJSON();
				var thumb      = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
				$('.bfc-input-'   + key).val(attachment.id);
				$('.bfc-preview-' + key).attr('src', thumb).show();
				$('[data-key="'   + key + '"].bfc-remove').show();
			});
			frame.open();
		});

		$(document).on('click', '.bfc-remove', function(e) {
			e.preventDefault();
			var key = $(this).data('key');
			$('.bfc-input-'   + key).val('');
			$('.bfc-preview-' + key).attr('src', '').hide();
			$(this).hide();
		});
	})(jQuery);
	</script>
	<?php
}

function background_for_category_images_sanitize( $input ) {
	if ( ! is_array( $input ) ) {
		return array();
	}
	$clean = array();
	foreach ( $input as $key => $val ) {
		$att_id = intval( $val );
		if ( $att_id <= 0 ) {
			continue;
		}
		if ( 'home' === $key ) {
			$clean['home'] = $att_id;
		} else {
			$clean[ intval( $key ) ] = $att_id;
		}
	}
	return $clean;
}

function background_for_categorysanitize_callback( $options ) {
	foreach ( $options as $name => &$val ) {
		if ( 'input' === $name ) {
			$val = htmlspecialchars( $val, ENT_QUOTES );
		}

		if ( 'checkbox' === $name ) {
			$val = intval( $val );
		}
	}

	return $options;
}
