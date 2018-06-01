<?php

/**
 * Job.logretention API specification (optional)
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_job_logretention_spec(&$params) {
  $params['runInNonProductionEnvironment'] = array(
    'title' => 'Run in Development?',
    'type' => CRM_Utils_Type::T_BOOLEAN,
  );
}

/**
 * Job.logretention API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_job_logretention($params) {
  $logging = CRM_Core_Config::singleton()->logging;
  $logging = CRM_Core_Config::singleton()->logging;
  if (!CRM_Core_Config::singleton()->logging) {
    $msg = ts('Logging is disabled');
    CRM_Core_Session::setStatus($msg, ts('Warning: Loggin status'), 'alert');
    return; //return if logging is disable
  }
  
  $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
  $loggingDB = $dsn['database']; //logging database
  $retention_period = Civi::settings()->get('retention_period');
  if(!isset($retention_period) || empty($retention_period)){
    $msg = ts('Retention period is not set of Log Retention Setting page. Set a value in months');
    CRM_Core_Session::setStatus($msg, ts('Warning: Loggin status'), 'alert');
    CRM_Utils_System::redirect('/civicrm/admin/logretention');
  }
  $retention_unit = 'month';
  if($retention_period > 1){
    $retention_unit = 'months';
  }
  $retentionPeriod = $retention_period .' '.$retention_unit;
  $ages_ago = date('Y-m-d H:i:s', strtotime("today -$retentionPeriod"));
  
  $schema = new CRM_Logging_Schema();
  $tables = $schema->getLogTableSpec();

  // build _logTables for custom tables
  $customTables = $schema->entityCustomDataLogTables('Contact');
  $logTables = array();
  foreach($tables as $key=>$value) {
    if($value['engine'] == 'INNODB')
      {
         $logTables[] = 'log_'.$key;
      }
  }
  $logTables = $logTables + $customTables;
  
  $params = array(
    1 => array($ages_ago, 'String'),
  );
  

  $tables_excluded = Civi::settings()->get('tables_excluded');//get tables excluded from log retention
  foreach($logTables as $table){
    if( !in_array($table, $tables_excluded) ){
      $max_log_date = '';
      $fetch_data = "SELECT max(log_date) as max_log_date FROM `{$loggingDB}`.$table WHERE log_date < %1";
      $fetch_dao = CRM_Core_DAO::executeQuery($fetch_data, $params);
      while ($fetch_dao->fetch()) {
        $max_log_date = $fetch_dao->max_log_date;
      }
    
      $sql = "DELETE FROM `{$loggingDB}`.$table WHERE log_date < %1 AND log_date <> '$max_log_date'";
      $dao = CRM_Core_DAO::executeQuery($sql, $params);
      
      $sql = "DELETE FROM civicrm_log WHERE modified_date < %1 AND modified_date <> '$max_log_date'";
      $dao = CRM_Core_DAO::executeQuery($sql, $params);
    }
  }
  return civicrm_api3_create_success("Deleted log entries that were older than $retentionPeriod");
}
