<?php

require_once 'civimobileactivitycategory.civix.php';

// phpcs:disable
use Civi\Api4\OptionGroup;
use Civi\Api4\OptionValue;
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
      $ActivityTypeOptionGroupID = OptionGroup::get()
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
  $optionGroups = OptionGroup::save(FALSE)->addRecord([
    'name'        => 'activity_category',
    'title'       => 'Activity Category',
    'description' => 'Activity Category',
    'data_type'   => 'String',
    'is_active'   => TRUE,
  ])->setMatch(['name'])->execute();

  $optionValues = OptionValue::save(FALSE)->addRecord([
    'option_group_id.name' => 'activity_category',
    'label'                => 'CiviMobile',
    'value'                => 'CiviMobile',
    'name'                 => 'CiviMobile',
  ])->setMatch(['value'])->execute();
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
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function civimobileactivitycategory_civicrm_enable() {
  civimobileactivitycategory_setup_optiongroups();

  _civimobileactivitycategory_civix_civicrm_enable();
}
