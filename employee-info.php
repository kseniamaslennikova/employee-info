<?php

/**
 * Employee info
 *
 * @package   Employee info
 * @author    Ksenia Maslennikova <info@php4u.ru>
 * @license   GPL-2.0+
 * @link      https://github.com/kseniamaslennikova/employee-info
 * @copyright 2016 Ksenia Maslennikova, php4u.ru
 *
 * @wordpress-plugin
 * Plugin Name:       Employee info
 * Plugin URI:        https://github.com/kseniamaslennikova/employee-info
 * Description:       Adding functionality for managing employees of a company.
 * Version:           1.0.0
 * Author:            Ksenia Maslennikova
 * Author URI:        https://github.com/kseniamaslennikova
 * Text Domain:       employee-info
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/kseniamaslennikova/employee-info
 * GitHub Branch:     master
 */

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) && ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Подключаем класс Логер, отвечающий за ведение логов
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-logger.php';

/**
 * Подключаем functions с набором функций, расширяющих функционал плагина
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';

function kmempinfo_install() {

    // регистрируем тип постов Сотрудники
    kmempinfo_setup_post_types();

    //регистрируем таксономии для типа постов Сотрудники
    kmempinfo_setup_taxonomies();

    //проверяем, существуют ли в базе данных нужные таблицы
    //если нет, создаем их
    kmempinfo_setup_database_tables();

    // Обновляем permalinks после регистрации типа постов Сотрудники
    flush_rewrite_rules();

    //устанавливаем дополнительные размеры превьюшек типа постов Сотрудники в админке
    kmempinfo_setup_plugin_settings();

}
/* End of Employee info plugin activation functions*/

/* Employee info plugin deactivation functions*/
function kmempinfo_deactivation() {

    //удаляем дополнительные размермы превьюшек типа постов Сотрудники в админке
    kmempinfo_unset_plugin_settings();

    // Clear the permalinks to remove our post type's rules
    flush_rewrite_rules();

}
/* End of Employee info plugin deactivation functions*/

register_activation_hook( __FILE__, 'kmempinfo_install' );
register_deactivation_hook( __FILE__, 'kmempinfo_deactivation' );