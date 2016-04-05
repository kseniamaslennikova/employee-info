<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://github.com/kseniamaslennikova/employee-info
 * @since      1.0.0
 *
 * @package    Employee info
 */

// если деинсталляция плагина вызвана не Вордпрессом, выходим
if(!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN'))
    exit();

//очищаем и удаляем все таблицы
function kmempinfo_drop_database_tables(){

    global $wpdb;
    //таблица, которую будем удалять
    $table_name = $wpdb->prefix . 'kmempinfologs';

    //проверяем, существуют ли таблица в базе
    //если да, удаляем ее            
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);

}
