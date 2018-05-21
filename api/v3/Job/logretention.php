<?php

/**
 * Job.logretention API specification (optional)
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_job_logretention_spec(&$spec) {
  // no arguments
  // TODO: configure for a date range, report, etc.
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
 
  $processed = array();
  // save all my api result error messages as well
  $error_log = array();
  
  $message = '';
 
  if (count($error_log) > 0) {
    return civicrm_api3_create_error($message . '</br />' . implode('<br />', $error_log));
  }
  return civicrm_api3_create_success($message);
}
