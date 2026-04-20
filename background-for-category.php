<?php
/*
 * Plugin Name: Служебный: Установить фоны для рубрики
 * Description: <b>Внимание!</b> Требует установки плагина или функции get_top_term(). Плагин устанавливает фоны для рубрик. Изображения должны быть в папке /images/backgrounds/. Название изображения устанавливаются жёстко по шаблону. Например, для рубрики сo slag равным wot, название файла фона background-wot.jpg. 
 * Plugin URI:  https://
 * Author URI:  https://t.me/big_jacky
 * Author:      @big_jacky
 * Version:     1.0
 */

/**
 * Создаем страницу настроек плагина
 * https://wp-kama.ru/id_3773/api-optsiy-nastroek.html#2.-stranitsa-nastroek-plagina-optsii-v-massive
 */
add_action('admin_menu', 'add_plugin_page_background_for_category');

function add_plugin_page_background_for_category(){
	add_options_page( 'Настройки Фона категории', 'Фон категории', 'manage_options', basename(dirname(__FILE__)), 'background_for_category_options_page_output' );
	
	}

/*
****************************************************************
	Plugin settings links
****************************************************************
*/
 add_filter( 'plugin_action_links', 'background_for_category_plugin_action_links', 10, 2 );
function background_for_category_plugin_action_links( $links, $plugin_file ){
	if( false === strpos( $plugin_file, basename(__FILE__) ) )
		return $links;

	$links[] = '<a href="' .
		admin_url( 'options-general.php?page='. basename(dirname(__FILE__))) .
		'">' . __('Settings') . '</a>';
	$links[] = '<a href="https://t.me/big_jacky">' . __('Author') . '</a>';
	return $links;
}




add_action ('wp_head', 'background_for_category',5);
function background_for_category($post_id) {
  
  $site_url = get_site_url(); //урл сайта
  //$default_bg_clr = '#000000'; //дефолтный фон
  
	$default_bg_clr = get_option('background_for_category_option');
	$default_bg_clr = $default_bg_clr ? $default_bg_clr['input'] : '#000000'; //дефолтный фон
  
  if (is_home()) {
echo '<style>body {background:' .  $default_bg_clr . ' url(' . $site_url . '/images/backgrounds/background-home.jpg) top center no-repeat !important;}</style>';
  }
  else
  {
		$top_term = get_top_term( 'category', $post_id ); //получаем информацию по категории верхнего уровня (по пользовательской функции get_top_term()
		$slug = $top_term->slug; // пишем в переменную название slug категории верхнего уровня 
 		
	if ( !empty( $slug ) ) { echo '<style>body {background: ' .  $default_bg_clr . ' url(' . $site_url . '/images/backgrounds/background-' . $slug . '.jpg) top center no-repeat !important;}</style>';}
	else { echo '<style>body {background: ' .  $default_bg_clr . ' !important;}</style>'; }
  }
 }



function background_for_category_options_page_output(){
	?>
<style>textarea {    
    border: 1px solid #b1aeae;
	box-shadow: 1px 1px 12px -2px;
}</style>
	<div class="wrap">
		<h2><?php echo get_admin_page_title() ?></h2>
		
		<div style="font-size: 12pt;">
	<b>Внимание!</b> Требует установки плагина или функции <b>get_top_term()</b>.<br>
	Плагин устанавливает фоны для рубрик.<br>
	Изображения должны быть в папке /images/backgrounds/.<br>
	Название изображения устанавливаются жёстко по шаблону. Например, для рубрики верхнего уровня сo slag равным <b>wot</b>, название файла фона background-<b>wot</b>.jpg.</div> 

		<form action="options.php" method="POST">
			<?php
				settings_fields( 'option_group' );     // скрытые защитные поля
				do_settings_sections( 'background_for_category_page' ); // секции с настройками (опциями). У нас она всего одна 'section_id'
				submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Регистрируем настройки.
 * Настройки будут храниться в массиве, а не одна настройка = одна опция.
 */
add_action('admin_init', 'background_for_category_plugin_settings');
function background_for_category_plugin_settings(){
	// параметры: $option_group, $background_for_category_option, $background_for_categorysanitize_callback
	register_setting( 'option_group', 'background_for_category_option', 'background_for_categorysanitize_callback' );

	// параметры: $id, $title, $callback, $page
	add_settings_section( 'section_id', 'Основные настройки', '', 'background_for_category_page' ); 

	// параметры: $id, $title, $callback, $page, $section, $args
	add_settings_field('background_for_category_field1', 'Дефолтный цвет фона', 'fill_background_for_category_field1', 'background_for_category_page', 'section_id' );
	add_settings_field('background_for_category_field2', 'Запасная опция', 'fill_background_for_category_field2', 'background_for_category_page', 'section_id' );

}

## Заполняем опцию 1
function fill_background_for_category_field1(){
	$val = get_option('background_for_category_option');
	$val = $val ? $val['input'] : null;
	?>
	<input type="text" name="background_for_category_option[input]" value="<?php echo esc_attr( $val ) ?>" /> <div style="display: inline-block;margin-left: 10px;background-color: <?php echo esc_attr( $val ) ?>;height: 20px;width: 20px;"></div>

	<div> Формат: #232323</div>
	<?php
}

## Заполняем опцию 2
function fill_background_for_category_field2(){
	$val = get_option('background_for_category_option');
	$val = $val ? $val['checkbox'] : null;
	?>
	<label><input type="checkbox" name="background_for_category_option[checkbox]" value="1" <?php checked( 1, $val ) ?> /> отметить</label>
	
	<?php
}



## Очистка данных
function background_for_categorysanitize_callback( $options ){ 
	// очищаем
	foreach( $options as $name => & $val ){
		if( $name == 'input' )
			//$val = strip_tags( $val );
		$val = htmlspecialchars($val, ENT_QUOTES);

		if( $name == 'checkbox' )
			$val = intval( $val );			
		
	}

	//die(print_r( $options )); // Array ( [input] => aaaa [checkbox] => 1 )
	return $options;
}


 
 ?>
