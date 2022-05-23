<?php

require_once 'civimobileactivitycategory.civix.php';

// phpcs:disable
use CRM_Civimobileactivitycategory_ExtensionUtil as E;

// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function civimobileactivitycategory_civicrm_config(&$config) {

  // Bind wrapper for API Event
  $null = NULL;

  $civimobile = CRM_Utils_Request::retrieve('civimobile', 'Int', $null, FALSE, FALSE, 'GET');

  if ($civimobile) {
    Civi::dispatcher()
        ->addListener(\Civi\API\Events::PREPARE, [
          'CRM_CiviMobileActivityCategory_OptionValue_APIWrapper',
          'PREPARE',
        ], -100);
  }

  _civimobileactivitycategory_civix_civicrm_config($config);
}


/**
 * Implements an API Wrapper to change the return value of Activity Types
 */
class CRM_CiviMobileActivityCategory_OptionValue_APIWrapper {

  /**
   * Callback to wrap OptionValues API calls.
   */
  public static function PREPARE($event) {
    $request = $event->getApiRequestSig();
    switch ($request) {
      case '3.optionvalue.get':
        $event->wrapAPI([
          'CRM_CiviMobileActivityCategory_OptionValue_APIWrapper',
          'OptionValues',
        ]);
        break;

    }
  }

  /**
   * OptionValues API Wrapper function
   *
   * @param   array  $apiRequest
   * @param   array  $callsame  - function callback see
   *                            \Civi\Api\Provider\WrappingProvider
   */
  public function OptionValues($apiRequest, $callsame) {
    // Does this API call include the Option Group ID as a parameter
    if (!empty($apiRequest['params']['option_group_id'])) {

      // Get the Activity Type, Option Group ID
      $ActivityTypeOptionGroupID = \Civi\Api4\OptionGroup::get()
                                                         ->addSelect('id')
                                                         ->addWhere('name', '=', 'activity_type')
                                                         ->setLimit(1)
                                                         ->execute()
                                                         ->getArrayCopy();

      // Check that the API call is for the Activity Type, Option Group
      if ($apiRequest['params']['option_group_id'] == $ActivityTypeOptionGroupID[0]['id']) {
        $apiRequest['params']['grouping']        = ['LIKE' => "%CiviMobile%"];
        $apiRequest['params']['options']['sort'] = 'weight ASC';
      }
    }

    return $callsame($apiRequest);
  }

}


/**
 * Implements hook_civicrm_buildForm().
 */
function civimobileactivitycategory_civicrm_buildForm($formName, &$form) {

  // Display category option for activity types and activity statuses.
  if ($formName == 'CRM_Admin_Form_Options'
      && in_array($form->getVar('_gName'), [
      'activity_type',
      'activity_status',
    ])) {
    $options = civicrm_api3('optionValue', 'get', [
      'option_group_id' => 'activity_category',
      'is_active'       => 1,
      'options'         => ['limit' => 0, 'sort' => 'weight'],
    ]);
    $opts    = [];
    if ($form->getVar('_gName') == 'activity_status') {
      $placeholder = ts('All');
      // Activity status can also apply to uncategorized activities.
      $opts[] = [
        'id'   => 'none',
        'text' => ts('Uncategorized'),
      ];
    }
    else {
      $placeholder = ts('Uncategorized');
    }
    foreach ($options['values'] as $opt) {
      $opts[] = [
        'id'   => $opt['name'],
        'text' => $opt['label'],
      ];
    }
    $form->add('select2', 'grouping', ts('Activity Category'), $opts, FALSE, [
      'class'       => 'crm-select2',
      'multiple'    => TRUE,
      'placeholder' => $placeholder,
    ]);
  }
}

/**
 * Create the required Activity Category and Activity Category, CiviMobile
 * option
 */

function civimobileactivitycategory_setup_optiongroups() {
  // Check that the Activity Category Option exists
  $optionGroups = \Civi\Api4\OptionGroup::get()
                                        ->addSelect('id')
                                        ->addWhere('name', '=', 'activity_category')
                                        ->execute();
  if ($optionGroups->rowCount == 0) {
    \Civi\Api4\OptionGroup::create()
                          ->addValue('name', 'activity_category')
                          ->addValue('title', 'Activity Category')
                          ->addValue('description', 'Activity Category')
                          ->addValue('data_type', 'String')
                          ->addValue('is_active', TRUE)
                          ->execute();
  }

  // Check that the Activity Category, CiviMobile exists
  $optionValues = \Civi\Api4\OptionValue::get()
                                        ->addSelect('id')
                                        ->addWhere('option_group_id:name', '=', 'activity_category')
                                        ->addWhere('value', '=', 'CiviMobile')
                                        ->setLimit(1)
                                        ->execute();

  // Create the CiviMobile Activity Category if it does not already exist
  if ($optionValues->rowCount == 0) {
    \Civi\Api4\OptionValue::create()
                          ->addValue('option_group_id.name', 'activity_category')
                          ->addValue('label', 'CiviMobile')
                          ->addValue('value', 'CiviMobile')
                          ->addValue('name', 'CiviMobile')
                          ->execute();
  }
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function civimobileactivitycategory_civicrm_install() {
  civimobileactivitycategory_setup_optiongroups();

  _civimobileactivitycategory_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function civimobileactivitycategory_civicrm_postInstall() {
  _civimobileactivitycategory_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function civimobileactivitycategory_civicrm_uninstall() {
  _civimobileactivitycategory_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function civimobileactivitycategory_civicrm_enable() {
  civimobileactivitycategory_setup_optiongroups();

  _civimobileactivitycategory_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function civimobileactivitycategory_civicrm_disable() {
  _civimobileactivitycategory_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function civimobileactivitycategory_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _civimobileactivitycategory_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function civimobileactivitycategory_civicrm_entityTypes(&$entityTypes) {
  _civimobileactivitycategory_civix_civicrm_entityTypes($entityTypes);
}
