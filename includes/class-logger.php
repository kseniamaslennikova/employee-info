<?php

/**
 * The file that defines the plugin class Logger
 *
 * A class definition that includes attributes and functions used
 * to log employee profile page views.
 *
 * @link       https://github.com/kseniamaslennikova/employee-info
 * @since      1.0.0
 *
 * @package    Employee info
 * @subpackage Employee info/includes
 */

class Logger {


    private $log_id;
    private $employee_id;
    private $log_date;
    private $webpage_url;

    //устанавливаем значения свойств
    public function setEmployeeId($employee_id){
        $this->employee_id=$employee_id;
    }

    public function setLogDate($logdate){
        $this->log_date=$logdate;
    }

    public function setWebpageUrl($webpage_url){
        $this->webpage_url=$webpage_url;
    }

    //получаем значения свойств
    public function getLogId(){
        return $this->log_id;
    }

    public function getEmployeeId(){
        return $this->employee_id;
    }

    public function getLogDate(){
        return $this->log_date;
    }

    public function getWebpageUrl(){
        return $this->webpage_url;
    }

    //сохранение лога в базе
    public function save(){
        global $wpdb;

        $table_name = $wpdb->prefix . 'kmempinfologs';

        $wpdb->insert($table_name, array(
            'employee_id' => $this->employee_id,
            'log_date' => $this->log_date,
            'webpage_url' => $this->webpage_url
        ), array(
            '%u','%s','%s'
        ));
        $wpdb->print_error();
        $this->log_id = $wpdb->insert_id;
    }
    
}