<?php
/**
 * ISTN CRMEntity Class
 * @package YetiForce.CRMEntity
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class ISTN extends Vtiger_CRMEntity
{

	var $table_name = 'u_yf_istn';
	var $table_index = 'istnid';
	protected $lockFields = ['istn_status' => ['PLL_ACCEPTED']];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('u_yf_istncf', 'istnid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'u_yf_istn', 'u_yf_istncf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'u_yf_istn' => 'istnid',
		'u_yf_istncf' => 'istnid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
// tablename should not have prefix 'vtiger_'
		'FL_SUBJECT' => Array('istn', 'subject'),
		'Assigned To' => Array('crmentity', 'smownerid')
	);
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'FL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	);
// Make the field link to detail view
	var $list_link_field = 'subject';
// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
// tablename should not have prefix 'vtiger_'
		'FL_SUBJECT' => Array('istn', 'subject'),
		'Assigned To' => Array('vtiger_crmentity', 'assigned_user_id'),
	);
	var $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'FL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	);
// For Popup window record selection
	var $popup_fields = Array('subject');
// For Alphabetical search
	var $def_basicsearch_col = 'subject';
// Column value to use on detail view record text display
	var $def_detailview_recname = 'subject';
// Used when enabling/disabling the mandatory fields for the module.
// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('subject', 'assigned_user_id');
	var $default_order_by = '';
	var $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	public function vtlib_handler($moduleName, $eventType)
	{
		if ($eventType == 'module.postinstall') {

		} else if ($eventType == 'module.disabled') {

		} else if ($eventType == 'module.preuninstall') {

		} else if ($eventType == 'module.preupdate') {

		} else if ($eventType == 'module.postupdate') {

		}
	}
}
