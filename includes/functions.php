<?php

/**
 * The file that defines the plugin functions
 *
 * Functions for adding action and filter hooks.
 * Extending plugin functionality.
 *
 * @link       https://github.com/kseniamaslennikova/employee-info
 * @since      1.0.0
 *
 * @package    Employee info
 * @subpackage Employee info/includes
 */

/* Employee info plugin activation functions*/
function kmempinfo_setup_post_types() {

    //регистрируем тип постов Сотрудники
    $kmempinfo_employee_labels = array(
        'name' => _x( 'Сотрудники', 'kmempinfo_employee' ),
        'singular_name' => _x( 'Сотрудника', 'kmempinfo_employee' ),
        'add_new' => _x( 'Добавить сотрудника', 'kmempinfo_employee' ),
        'add_new_item' => _x( 'Новый сотрудник', 'kmempinfo_employee' ),
        'edit_item' => _x( 'Изменить сотрудника', 'kmempinfo_employee' ),
        'new_item' => _x( 'Новый сотрудник', 'kmempinfo_employee' ),
        'view_item' => _x( 'Просмотреть сотрудника', 'kmempinfo_employee' ),
        'search_items' => _x( 'Поиск сотрудников', 'kmempinfo_employee' ),
        'not_found' => _x( 'Сотрудников не найдено', 'kmempinfo_employee' ),
        'not_found_in_trash' => _x( 'Не найдено Сотрудников в корзине', 'kmempinfo_employee' ),
        'parent_item_colon' => '',
        'menu_name' => _x( 'Сотрудники', 'kmempinfo_employee' ),
    );

    $args = array(
        'labels' => $kmempinfo_employee_labels,
        'taxonomies' => array('position','employee-sex','employee-status'),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'query_var' => true,
        'capability_type' => 'post',
        'hierarchical' => true,
        'menu_position' => 6,
        'menu_icon'=> 'dashicons-smiley',
        'supports' => array('title','author','thumbnail'),
        'has_archive' => true,
        'rewrite' => array('slug' => 'команда'),
        'register_meta_box_cb' => 'kmempinfo_employee_meta_boxes'
    );
    register_post_type('kmempinfo_employee',$args);

}
add_action( 'init', 'kmempinfo_setup_post_types' );

//регистрируем таксономии для типа постов Сотрудники
function kmempinfo_setup_taxonomies(){

    // Должности сотрудников
    $position_labels = array(
        'name' => _x( 'Должности', 'taxonomy general name' ),
        'singular_name' => _x( 'Должность', 'taxonomy singular name' ),
        'search_items' =>  __( 'Искать в должностях' ),
        'all_items' => __( 'Все должности' ),
        'most_used_items' => null,
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => __( 'Изменить должность' ),
        'update_item' => __( 'Обновить должность' ),
        'add_new_item' => __( 'Добавить должность' ),
        'new_item_name' => __( 'Новая должность' ),
        'menu_name' => __( 'Должности' ),
    );
    register_taxonomy('position',array('kmempinfo_employee'),array(
        'hierarchical' => true,
        'labels' => $position_labels,
        'show_ui' => true,
        'query_var' => true,
        'show_admin_column' => true,
        'meta_box_cb' => 'kmempinfo_employee_position_meta_box',
        'rewrite' => array('slug' => 'должности' )
    ));

    // Пол сотрудников
    $sex_labels = array(
        'name' => _x( 'Пол сотрудника', 'taxonomy general name' ),
        'singular_name' => _x( 'Пол сотрудника', 'taxonomy singular name' ),
        'search_items' =>  __( 'Искать в Пол сотрудника' ),
        'all_items' => __( 'Все варианты' ),
        'most_used_items' => null,
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => __( 'Изменить пол' ),
        'update_item' => __( 'Обновить пол' ),
        'add_new_item' => __( 'Добавить пол' ),
        'new_item_name' => __( 'Новый пол' ),
        'menu_name' => __( 'Пол сотрудников' ),
    );
    register_taxonomy('employee-sex',array('kmempinfo_employee'),array(
        'hierarchical' => false,
        'labels' => $sex_labels,
        'show_ui' => true,
        'query_var' => true,
        'show_admin_column' => true,
        'meta_box_cb' => 'kmempinfo_employee_sex_meta_box',
        'rewrite' => array('slug' => 'пол-сотрудника' )
    ));

}
add_action( 'init', 'kmempinfo_setup_taxonomies');

//создаем в базе Вордпресса таблицу для ведения логов
function kmempinfo_setup_database_tables(){

    global $wpdb;
    //таблица, которую будем создавать
    $table_name = $wpdb->prefix . 'kmempinfologs';

    //таблица с сотрудниками, к которой будем привязывать новую таблицу по ключу
    $employees_table= $wpdb->prefix . 'posts';

    //проверяем, существуют ли таблица в базе
    //если нет, создаем ее
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        //такой таблицы не существует, создаем ее
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            employee_id bigint(20) unsigned NOT NULL,
            log_date date NOT NULL,
            webpage_url varchar(255) NOT NULL,
            PRIMARY KEY (id),
            KEY employee_id (employee_id)
        )ENGINE = INNODB $charset_collate; AUTO_INCREMENT=1";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        //добавим FOREIGN KEY
        $sql = "ALTER TABLE $table_name ADD FOREIGN KEY (employee_id) REFERENCES $employees_table(ID) ON DELETE CASCADE;";
        $wpdb->query($sql);

    }
    else{
        //таблица существует
    }

}

//Меняем отображение списка Сотрудников на главной странице Админки
add_filter( 'manage_kmempinfo_employee_posts_columns', 'kmempinfo_employee_edit_columns' );
function kmempinfo_employee_edit_columns( $columns ) {

    return array(
        'cb' => '<input type="checkbox" />',
        'thumbnail'=>'Фото',
        'title' => 'Имя сотрудника',
        'employee-sex' =>'Пол',
        'position' =>'Должность',
        'employee-status' => 'Статус',
        'author' => 'Автор',
        'date' => 'Дата'
    );
}

//устанавливаем новые размеры превьюшек для типа постов Сотрудники
function kmempinfo_setup_image_sizes() {
    //добавляем поддержку превьюшек для типа постов Сотрудники
    if ( function_exists( 'add_theme_support' ) ) {
        add_theme_support( 'post-thumbnails', array( 'kmempinfo_employee' ) );
    }

    //добавляем определенные размеры превьюшек для типа постов Сотрудники
    if ( function_exists( 'add_image_size' ) ) {
        add_image_size('kmempinfo_employee-thumbnail',220, 220, true );
        add_image_size('kmempinfo_employee-image', 300, 533, false );
    }
}
add_action( 'after_setup_theme', 'kmempinfo_setup_image_sizes' );


function kmempinfo_setup_plugin_settings() {

    kmempinfo_setup_image_sizes();

}

function kmempinfo_unset_image_sizes($sizes) {
    //удаляем определенные размеры превьюшек для типа постов Сотрудники
    unset( $sizes['kmempinfo_employee-thumbnail']);
    unset( $sizes['kmempinfo_employee-image']);

    return $sizes;
}

function kmempinfo_unset_plugin_settings(){

    add_filter('intermediate_image_sizes_advanced', 'kmempinfo_unset_image_sizes');
}