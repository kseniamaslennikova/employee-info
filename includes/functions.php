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

/*
 * Функции для работы с типом постов Сотрудники
 */
// Добавить вывод постов типа Сотрудники на главную страницу в общий список
add_action( 'pre_get_posts', 'add_kmempinfo_employee_to_query' );

function add_kmempinfo_employee_to_query( $query ) {
    if ( is_home() && $query->is_main_query() )
        $query->set( 'post_type', array( 'post', 'page', 'kmempinfo_employee' ) );
    return $query;
}

//подставляем нужные данные в столбцы списка Сотрудников
add_action( 'manage_kmempinfo_employee_posts_custom_column', 'kmempinfo_employee_columns', 10, 2 );
function kmempinfo_employee_columns( $column, $post_id ) {

    $kmempinfo_employee_status = get_post_meta( $post_id, 'kmempinfo_employee_status', true );

    switch ( $column ) {
        case 'thumbnail':
            if(has_post_thumbnail($post_id)){
                $thumbnail = get_the_post_thumbnail($post_id, 'kmempinfo_employee-thumbnail');
                echo $thumbnail;
            }else{
                $noimage220= plugin_dir_url( __FILE__ ) . '../images/noimage220.png';
                echo '<img width="220" height="220" src="'.$noimage220.'" alt="Нет изображения" title="Нет изображения"/>';
            }
            break;
        case 'employee-sex':
            echo get_the_term_list( $post_id, 'employee-sex', '', ', ','' );
            break;
        case 'position':
            echo get_the_term_list( $post_id, 'position', '', ', ','' );
            break;
        case 'employee-status':
            if (isset($kmempinfo_employee_status) && !empty($kmempinfo_employee_status)){
                echo 'доступен';
            }
            else {
                echo 'недоступен';
            }
            break;
    }
}

//меняем размер колонок в списке сотрудников
add_action('admin_head', 'my_admin_column_width');
function my_admin_column_width() {
    echo '<style type="text/css">
        .column-title { text-align: left; width:200px !important; overflow:hidden }		      
		.column-thumbnail { text-align: left; width:220px !important; overflow:hidden }					
		.column-employee-sex { text-align: left; width:100px !important; overflow:hidden }
		.column-position { text-align: left; color: #21B384;  width:120px !important; overflow:hidden }
		.column-employee-status { text-align: left; color: #21B384;  width:120px !important; overflow:hidden }
					
    </style>';
}

//меняем расположение стандартных метабоксов для типа постов Сотрудники
add_action( 'admin_head', 'change_kmempinfo_employee_metaboxes' );
function change_kmempinfo_employee_metaboxes() {

    remove_meta_box('tagsdiv-employee-sex', 'kmempinfo_employee', 'normal' );
    add_meta_box('tagsdiv-employee-sex', __('Пол сотрудника'),'kmempinfo_employee_sex_meta_box', 'kmempinfo_employee', 'side');

    remove_meta_box('positiondiv', 'kmempinfo_employee', 'side' );
    add_meta_box('positiondiv', __('Должность сотрудника'),'kmempinfo_employee_position_meta_box', 'kmempinfo_employee', 'normal','high');

    remove_meta_box( 'postimagediv', 'kmempinfo_employee', 'side' );
    add_meta_box('postimagediv', __('Фото сотрудника'), 'post_thumbnail_meta_box', 'kmempinfo_employee', 'normal', 'high');

    remove_meta_box( 'authordiv', 'kmempinfo_employee', 'normal' );
    add_meta_box('authordiv', __('Автор'), 'post_author_meta_box', 'kmempinfo_employee', 'normal', 'low');
}


add_filter('gettext', 'custom_rewrites', 10, 4);
function custom_rewrites($translation, $text, $domain) {

    global $post;

    $translations = &get_translations_for_domain($domain);
    $translation_array = array();

    switch ($post->post_type) {
        case 'kmempinfo_employee':
            $translation_array = array(
                'Enter title here' => 'Имя сотрудника',
                'Excerpt' => "Краткая информация о сотруднике"
            );
            $pos = strpos($text, 'Excerpts are optional hand-crafted summaries of your');
            if ($pos !== false) {
                return  'Добавьте краткую информацию о сотруднике.';
            }
            break;
    }

    if (array_key_exists($text, $translation_array)) {
        return $translations->translate($translation_array[$text]);
    }

    return $translation;
}

/* Добавляем метабоксы с дополнительными параметрами для профайлов сотрудников */
function kmempinfo_employee_meta_boxes() {

    add_meta_box( 'kmempinfo_employee_info', 'Информация о сотруднике', 'kmempinfo_employee_info_output', 'kmempinfo_employee', 'normal', 'core' );

}

/* Новый metabox для сотрудников - Основная информация */
function kmempinfo_employee_info_output($post) {

    $post_id = get_the_ID();

    wp_nonce_field( 'kmempinfoemployee_info', 'kmempinfoemployee_info' );

    $kmempinfo_employee_status = get_post_meta( $post_id, 'kmempinfo_employee_status', true );
    $kmempinfo_employee_phone = get_post_meta( $post_id, 'kmempinfo_employee_phone', true );
    $kmempinfo_employee_email = get_post_meta( $post_id, 'kmempinfo_employee_email', true );

    ?>

    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="kmempinfo_employee_status">Сотрудник доступен?</label>
            </th>
            <td>
                <input type="checkbox" name="kmempinfo_employee_status" value="1" <?php  checked( 1,  $kmempinfo_employee_status , true ) ?> />
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="kmempinfo_employee_phone">Телефон</label>
            </th>
            <td>
                <input type="text" name="kmempinfo_employee_phone" id="kmempinfo_employee_phone" value='<?php esc_attr_e($kmempinfo_employee_phone);?>'  style="width:60%;"/>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="kmempinfo_employee_email">Email</label>
            </th>
            <td>
                <input type="text" name="kmempinfo_employee_email" id="kmempinfo_employee_email" value='<?php esc_attr_e($kmempinfo_employee_email);?>'  style="width:60%;"/>
            </td>
        </tr>

    </table>

    <?php

}/* Завершение метабокса Основная информация о сотруднике */


/**
 * Новый meta box для таксономии Пол сотрудников
 */
function kmempinfo_employee_sex_meta_box( $post ) {
    $terms = get_terms( 'employee-sex', array( 'hide_empty' => false ) );
    $post  = get_post();
    $employee_sex = wp_get_object_terms( $post->ID, 'employee-sex', array( 'orderby' => 'term_id', 'order' => 'ASC' ) );
    $name  = '';
    if ( ! is_wp_error( $employee_sex ) ) {
        if ( isset( $employee_sex[0] ) && isset( $employee_sex[0]->name ) ) {
            $name = $employee_sex[0]->name;
        }
    }
    foreach ( $terms as $term ) {
        ?>
        <label title='<?php esc_attr_e( $term->name ); ?>'>
            <input type="radio" name="kmempinfo_employee_sex" value="<?php esc_attr_e( $term->name ); ?>" <?php checked( $term->name, $name ); ?>>
            <span><?php esc_html_e( $term->name ); ?></span>
        </label><br>
        <?php
    }
}


/**
 * Новый meta box для таксономии Должность сотрудника
 */
function kmempinfo_employee_position_meta_box( $post ) {
    $terms = get_terms( 'position', array( 'hide_empty' => false ) );
    $post  = get_post();
    $employee_position = wp_get_object_terms( $post->ID, 'position', array( 'orderby' => 'term_id', 'order' => 'ASC' ) );
    $name  = '';
    if ( ! is_wp_error( $employee_position ) ) {
        if ( isset( $employee_position[0] ) && isset( $employee_position[0]->name ) ) {
            $name = $employee_position[0]->name;
        }
    }
    ?>

    <select name="kmempinfo_employee_position" id="kmempinfo_employee_position" style="width:60%;">
        <?php
        foreach ($terms as $term) {
            ?>
            <option <?php selected($term->name, $name); ?>
                value='<?php esc_attr_e($term->name); ?>'><?php esc_attr_e($term->name); ?></option>
            <?php
        }
        ?>
    </select>
    <?php
}

/**
 * Сохраняем результаты метабокса.
 *
 * @param int $post_id The ID of the post that's being saved.
 */
function save_kmempinfo_employee( $post_id ) {

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! empty( $_POST['kmempinfoemployee_info'] ) && ! wp_verify_nonce( $_POST['kmempinfoemployee_info'], 'kmempinfoemployee_info' ) )
        return;

    //сохраняем пол сотрудника
    if ( ! isset( $_POST['kmempinfo_employee_sex'] ) ) {
        return;
    }
    $kmempinfo_employee_sex = sanitize_text_field( $_POST['kmempinfo_employee_sex'] );

    if ( empty( $kmempinfo_employee_sex ) ) {
        remove_action( 'save_post_kmempinfo_employee', 'save_kmempinfo_employee' );
        $postdata = array(
            'ID'          => $post_id,
            'post_status' => 'draft',
        );
        wp_update_post( $postdata );
    } else {
        $term = get_term_by( 'name', $kmempinfo_employee_sex, 'employee-sex' );
        if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
            wp_set_object_terms( $post_id, $term->term_id, 'employee-sex', false );
        }
    }

    //сохраняем должность сотрудника
    if ( ! isset( $_POST['kmempinfo_employee_position'] ) ) {
        return;
    }
    $kmempinfo_employee_position = sanitize_text_field( $_POST['kmempinfo_employee_position'] );

    if ( empty( $kmempinfo_employee_position ) ) {
        remove_action( 'save_post_kmempinfo_employee', 'save_kmempinfo_employee' );
        $postdata = array(
            'ID'          => $post_id,
            'post_status' => 'draft',
        );
        wp_update_post( $postdata );
    } else {
        $term = get_term_by( 'name', $kmempinfo_employee_position, 'position' );
        if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
            wp_set_object_terms( $post_id, $term->term_id, 'position', false );
        }
    }

    //сохраняем статус сотрудника
    $kmempinfo_employee_status = 1;
    if ( ! isset( $_POST['kmempinfo_employee_status'] ) ) {
        $kmempinfo_employee_status = 0;
    }
    update_post_meta( $post_id, 'kmempinfo_employee_status', $kmempinfo_employee_status );

    //сохраняем телефон сотрудника
    if (( isset( $_POST['kmempinfo_employee_phone'] ) ) && ( ! empty( $_POST['kmempinfo_employee_phone'] ) )) {
        $kmempinfo_employee_phone =  sanitize_text_field( $_POST['kmempinfo_employee_phone'] );
        update_post_meta( $post_id, 'kmempinfo_employee_phone', $kmempinfo_employee_phone );
    } else {
        delete_post_meta( $post_id, 'kmempinfo_employee_phone' );
    }

    //сохраняем email сотрудника
    if (( isset( $_POST['kmempinfo_employee_email'] ) ) && ( ! empty( $_POST['kmempinfo_employee_email'] ) )) {
        $kmempinfo_employee_email =  sanitize_text_field( $_POST['kmempinfo_employee_email'] );
        update_post_meta( $post_id, 'kmempinfo_employee_email', $kmempinfo_employee_email );
    } else {
        delete_post_meta( $post_id, 'kmempinfo_employee_email' );
    }

}
add_action( 'save_post_kmempinfo_employee', 'save_kmempinfo_employee' );

/**
 * Показываем сообщение об ошибке и необходимости заполнения ключевых параметров для публикации профайла
 *
 * @param WP_Post The current post object.
 */
function show_required_field_error_msg( $post ) {
    if ( 'kmempinfo_employee' === get_post_type( $post ) && 'auto-draft' !== get_post_status( $post ) ) {
        $kmempinfo_employee_sex = wp_get_object_terms( $post->ID, 'employee-sex', array( 'orderby' => 'term_id', 'order' => 'ASC' ) );
        if ( is_wp_error( $kmempinfo_employee_sex ) || empty( $kmempinfo_employee_sex ) ) {
            printf(
                '<div class="error below-h2"><p>%s</p></div>',
                esc_html__( 'Пол сотрудника является необходимым параметром для заполнения. Профайл не был опубликован.' )
            );
        }

        $kmempinfo_employee_position = wp_get_object_terms( $post->ID, 'position', array( 'orderby' => 'term_id', 'order' => 'ASC' ) );
        if ( is_wp_error( $kmempinfo_employee_position ) || empty( $kmempinfo_employee_position ) ) {
            printf(
                '<div class="error below-h2"><p>%s</p></div>',
                esc_html__( 'Должность сотрудника является необходимым параметром для заполнения. Профайл не был опубликован.' )
            );
        }
    }
}
add_action( 'edit_form_top', 'show_required_field_error_msg' );

/**
 * Реализация шорткода
 * [employee id="1"] - выводит профайл сотрудника с id=1
 * [employee id=""] - выводит 'Такого сотрудника не существует'
 * (а также при введении номера id сотрудника, которого нет в базе)
 * [employee] - выводит список из профайлов всех сотрудников
 */
function kmempinfo_employee_output( $atts ) {

    $output = '';

    $args = shortcode_atts(
        array(
            'id'=> ''
        ),
        $atts
    );

    $employee_id= $args['id'];
    //если был передан id
    if (!empty($employee_id)){

        $args = array(
            'post_type' => 'kmempinfo_employee',
            'p' => $employee_id
        );
        $employees = new WP_Query($args);
        //если существует сотрудник с таким id
        if($employees->have_posts()):
            while($employees->have_posts()): $employees->the_post();
                //получаем должность сотрудника
                $employee_position = wp_get_object_terms( $employee_id, 'position', array( 'orderby' => 'term_id', 'order' => 'ASC' ) );
                $employee_position_name  = '';
                if ( ! is_wp_error( $employee_position ) ) {
                    if ( isset( $employee_position[0] ) && isset( $employee_position[0]->name ) ) {
                        $employee_position_name = $employee_position[0]->name;
                    }
                }
                //получаем пол сотрудника
                $employee_sex = wp_get_object_terms( $employee_id, 'employee-sex', array( 'orderby' => 'term_id', 'order' => 'ASC' ) );
                $employee_sex_name  = '';
                if ( ! is_wp_error( $employee_sex ) ) {
                    if ( isset( $employee_sex[0] ) && isset( $employee_sex[0]->name ) ) {
                        $employee_sex_name = $employee_sex[0]->name;
                    }
                }
                //получаем статус, телефон, email сотрудника
                $kmempinfo_employee_status = get_post_meta( $employee_id, 'kmempinfo_employee_status', true );
                $kmempinfo_employee_phone = get_post_meta( $employee_id, 'kmempinfo_employee_phone', true );
                $kmempinfo_employee_email = get_post_meta( $employee_id, 'kmempinfo_employee_email', true );
                //начинаем вывод в буфер профайла сотрудника
                ob_start();

                ?>
                <div class="kmempinfo_employee_profile <?= esc_attr_e(($employee_sex_name == 'женский' ? 'female' : ($employee_sex_name == 'мужской' ? 'male' : 'unknownsex'))); ?>">

                    <h2><?= get_the_title(); ?></h2>
                    <?php
                    if(has_post_thumbnail()) {
                        the_post_thumbnail('kmempinfo_employee-thumbnail');
                    }
                    else {
                        $noimage220= plugin_dir_url( __FILE__ ) . '../images/noimage220.png';
                        echo '<img width="220" height="220" src="'.$noimage220.'" alt="Нет изображения" title="Нет изображения"/>';
                    }
                    ?>
                    <ul>
                        <li>Пол: <?= esc_attr_e($employee_sex_name); ?></li>
                        <li>Должность: <?= esc_attr_e($employee_position_name); ?></li>
                        <li>Статус: <?= esc_attr_e(($kmempinfo_employee_status == 1 ? 'доступен' : 'недоступен')); ?></li>
                        <li>Телефон: <?= esc_attr_e(((empty($kmempinfo_employee_phone)) ? 'не указан' : $kmempinfo_employee_phone)); ?></li>
                        <li>Email: <?= esc_attr_e(((empty($kmempinfo_employee_email)) ? 'не указан' : $kmempinfo_employee_email)); ?></li>
                    </ul>

                </div>

                <?php
                //запоминаем содержимое буфера и очищаем его
                $output=ob_get_contents();
                ob_clean();

                //запишем в лог данные о дате, странице и сотруднике

                //получаем адрес текущей страницы Вордпресс
                $current_url=get_permalink( get_queried_object_id() );
                $current_url=urldecode(esc_attr( wp_unslash($current_url)));
                //получаем текущую дату и время
                $date=new DateTime();
                $datestr=$date->format('Y-m-d H:i:s');
                //создаем объект логера, вносим нужные данные и сохраняем в базе
                $logger = new Logger();
                $logger->setEmployeeId($employee_id);
                $logger->setLogDate($datestr);
                $logger->setWebpageUrl($current_url);
                $logger->save();

            endwhile;


        //если в базе не найден сотрудник с переданным id
        else :
            $output=esc_attr_e('Такого сотрудника не существует.');

        endif;
    }
    //если был вставлен шорткод [employee] без id или был передан пустой id
    else {
        //выводим список всех сотрудников
        //начинаем вывод в буфер
        ob_start();

        $output=esc_attr_e('Выводим список всех сотрудников');

        $args = array(
            'post_type' => 'kmempinfo_employee',
            'posts_per_page' => -1
        );
        $employees = new WP_Query($args);
        //если в базе были заведены профайлы сотрудников
        if($employees->have_posts()):
            while($employees->have_posts()): $employees->the_post();
                $employee_id=get_the_ID();
                //получаем должность сотрудника
                $employee_position = wp_get_object_terms( $employee_id, 'position', array( 'orderby' => 'term_id', 'order' => 'ASC' ) );
                $employee_position_name  = '';
                if ( ! is_wp_error( $employee_position ) ) {
                    if ( isset( $employee_position[0] ) && isset( $employee_position[0]->name ) ) {
                        $employee_position_name = $employee_position[0]->name;
                    }
                }
                //получаем пол сотрудника
                $employee_sex = wp_get_object_terms( $employee_id, 'employee-sex', array( 'orderby' => 'term_id', 'order' => 'ASC' ) );
                $employee_sex_name  = '';
                if ( ! is_wp_error( $employee_sex ) ) {
                    if ( isset( $employee_sex[0] ) && isset( $employee_sex[0]->name ) ) {
                        $employee_sex_name = $employee_sex[0]->name;
                    }
                }
                //получаем статус, телефон, email сотрудника
                $kmempinfo_employee_status = get_post_meta( $employee_id, 'kmempinfo_employee_status', true );
                $kmempinfo_employee_phone = get_post_meta( $employee_id, 'kmempinfo_employee_phone', true );
                $kmempinfo_employee_email = get_post_meta( $employee_id, 'kmempinfo_employee_email', true );


                ?>
                <div class="kmempinfo_employee_profile_list <?= esc_attr_e(($employee_sex_name == 'женский' ? 'female' : ($employee_sex_name == 'мужской' ? 'male' : 'unknownsex'))); ?>">

                    <h2><?= get_the_title(); ?></h2>
                    <?php
                    if(has_post_thumbnail()) {
                        the_post_thumbnail('kmempinfo_employee-thumbnail');
                    }
                    else {
                        $noimage220= plugin_dir_url( __FILE__ ) . '../images/noimage220.png';
                        echo '<img width="220" height="220" src="'.$noimage220.'" alt="Нет изображения" title="Нет изображения"/>';
                    }
                    ?>
                    <ul>
                        <li>Пол: <?= esc_attr_e($employee_sex_name); ?></li>
                        <li>Должность: <?= esc_attr_e($employee_position_name); ?></li>
                        <li>Статус: <?= esc_attr_e(($kmempinfo_employee_status == 1 ? 'доступен' : 'недоступен')); ?></li>
                        <li>Телефон: <?= esc_attr_e(((empty($kmempinfo_employee_phone)) ? 'не указан' : $kmempinfo_employee_phone)); ?></li>
                        <li>Email: <?= esc_attr_e(((empty($kmempinfo_employee_email)) ? 'не указан' : $kmempinfo_employee_email)); ?></li>
                    </ul>

                </div>

                <?php

            endwhile;
            //сохраняем содержимое буфера и очищаем его
            $output.=ob_get_contents();
            ob_clean();
        //если в базе не было заведено ни одного профайла сотрудников
        else :
            $output=esc_attr_e('Невозможно вывести список сотрудников. Не заведено ни одного сотрудника в базе.');

        endif;
    }
    //выводим данные на страницу
    return $output;
}
//добавляем шорткод [employee]
function kmempinfo_employee_register_shortcode() {

    add_shortcode( 'employee', 'kmempinfo_employee_output' );
}

add_action( 'init', 'kmempinfo_employee_register_shortcode' );

//подключаем нужные для плагина скрипты и стили
function kmempinfo_employee_enqueue_scripts() {

    wp_enqueue_style( 'kmempinfo-employee-styles', plugin_dir_url( __FILE__ ) . '../css/kmempinfo_employee_styles.css' );
}
add_action( 'wp_enqueue_scripts', 'kmempinfo_employee_enqueue_scripts' );