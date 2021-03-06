<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */
namespace vtlib;

/**
 * Functions that need-rewrite / to be eliminated.
 */
class Deprecated
{

	public static function getFullNameFromQResult($result, $row_count, $module)
	{
		$adb = \PearDatabase::getInstance();
		$rowdata = $adb->query_result_rowdata($result, $row_count);
		$entity_field_info = \includes\Modules::getEntityInfo($module);
		$fieldsName = $entity_field_info['fieldname'];
		$name = '';
		if ($rowdata != '' && count($rowdata) > 0) {
			$name = self::getCurrentUserEntityFieldNameDisplay($module, $fieldsName, $rowdata);
		}
		$name = textlength_check($name);
		return $name;
	}

	public static function getFullNameFromArray($module, $fieldValues)
	{
		$entityInfo = \includes\Modules::getEntityInfo($module);
		$fieldsName = $entityInfo['fieldname'];
		$displayName = self::getCurrentUserEntityFieldNameDisplay($module, $fieldsName, $fieldValues);
		return $displayName;
	}

	/**
	* this function returns the entity field name for a given module; for e.g. for Contacts module it return concat(lastname, ' ', firstname)
	* @param1 $module - name of the module
	* @param2 $fieldsName - fieldname with respect to module (ex : 'Accounts' - 'accountname', 'Contacts' - 'lastname','firstname')
	* @param3 $fieldValues - array of fieldname and its value
	* @return string $fieldConcatName - the entity field name for the module
	*/
	public static function getCurrentUserEntityFieldNameDisplay($module, $fieldsName, $fieldValues)
	{
		$current_user = vglobal('current_user');
		if (strpos($fieldsName, ',') === false) {
			return $fieldValues[$fieldsName];
		} else {
			$accessibleFieldNames = [];
			foreach (explode(',', $fieldsName) as $field) {
				if ($module == 'Users' || getColumnVisibilityPermission($current_user->id, $field, $module) == '0') {
					$accessibleFieldNames[] = $fieldValues[$field];
				}
			}
			if (count($accessibleFieldNames) > 0) {
				return implode(' ', $accessibleFieldNames);
			}
		}
		return '';
	}

	public static function getBlockId($tabid, $label)
	{
		$adb = \PearDatabase::getInstance();
		$query = "select blockid from vtiger_blocks where tabid=? and blocklabel = ?";
		$result = $adb->pquery($query, array($tabid, $label));
		$noofrows = $adb->num_rows($result);

		$blockid = '';
		if ($noofrows == 1) {
			$blockid = $adb->query_result($result, 0, "blockid");
		}
		return $blockid;
	}

	public static function createModuleMetaFile()
	{
		$adb = \PearDatabase::getInstance();

		$sql = "select * from vtiger_tab";
		$result = $adb->pquery($sql, []);
		$num_rows = $adb->num_rows($result);
		$result_array = [];
		$seq_array = [];
		$ownedby_array = [];

		for ($i = 0; $i < $num_rows; $i++) {
			$tabid = $adb->query_result($result, $i, 'tabid');
			$tabname = $adb->query_result($result, $i, 'name');
			$presence = $adb->query_result($result, $i, 'presence');
			$ownedby = $adb->query_result($result, $i, 'ownedby');
			$result_array[$tabname] = $tabid;
			$seq_array[$tabid] = $presence;
			$ownedby_array[$tabid] = $ownedby;
		}

		//Constructing the actionname=>actionid array
		$actionid_array = [];
		$sql1 = "select * from vtiger_actionmapping";
		$result1 = $adb->pquery($sql1, []);
		$num_seq1 = $adb->num_rows($result1);
		for ($i = 0; $i < $num_seq1; $i++) {
			$actionname = $adb->query_result($result1, $i, 'actionname');
			$actionid = $adb->query_result($result1, $i, 'actionid');
			$actionid_array[$actionname] = $actionid;
		}

		//Constructing the actionid=>actionname array with securitycheck=0
		$actionname_array = [];
		$sql2 = "select * from vtiger_actionmapping where securitycheck=0";
		$result2 = $adb->pquery($sql2, []);
		$num_seq2 = $adb->num_rows($result2);
		for ($i = 0; $i < $num_seq2; $i++) {
			$actionname = $adb->query_result($result2, $i, 'actionname');
			$actionid = $adb->query_result($result2, $i, 'actionid');
			$actionname_array[$actionid] = $actionname;
		}

		$filename = 'user_privileges/tabdata.php';

		if (file_exists($filename)) {
			if (is_writable($filename)) {
				if (!$handle = fopen($filename, 'w+')) {
					echo "Cannot open file ($filename)";
					exit;
				}
				require_once('modules/Users/CreateUserPrivilegeFile.php');
				$newbuf = "<?php\n";
				$newbuf .= "\$tab_info_array=" . \vtlib\Functions::varExportMin($result_array) . ";\n";
				$newbuf .= "\$tab_seq_array=" . \vtlib\Functions::varExportMin($seq_array) . ";\n";
				$newbuf .= "\$tab_ownedby_array=" . \vtlib\Functions::varExportMin($ownedby_array) . ";\n";
				$newbuf .= "\$action_id_array=" . \vtlib\Functions::varExportMin($actionid_array) . ";\n";
				$newbuf .= "\$action_name_array=" . \vtlib\Functions::varExportMin($actionname_array) . ";\n";
				$tabdata = [
					'tabId' => $result_array,
					'tabPresence' => $seq_array,
					'tabOwnedby' => $ownedby_array,
					'actionId' => $actionid_array,
					'actionName' => $actionname_array,
				];
				$newbuf .= 'return ' . \vtlib\Functions::varExportMin($tabdata) . ";\n";
				fputs($handle, $newbuf);
				fclose($handle);
			} else {
				echo "The file $filename is not writable";
			}
		} else {
			echo "The file $filename does not exist";
		}
	}

	public static function getTemplateDetails($templateid)
	{
		$adb = \PearDatabase::getInstance();
		$returndata = [];
		$result = $adb->pquery("select body, subject from vtiger_emailtemplates where templateid=?", array($templateid));
		$returndata[] = $templateid;
		$returndata[] = $adb->query_result($result, 0, 'body');
		$returndata[] = $adb->query_result($result, 0, 'subject');
		return $returndata;
	}

	public static function getModuleTranslationStrings($language, $module)
	{
		static $cachedModuleStrings = [];

		if (!empty($cachedModuleStrings[$module])) {
			return $cachedModuleStrings[$module];
		}
		$newStrings = \Vtiger_Language_Handler::getModuleStringsFromFile($language, $module);
		$cachedModuleStrings[$module] = $newStrings['languageStrings'];

		return $cachedModuleStrings[$module];
	}

	/**
	* Get translated currency name string.
	* @param String $str - input currency name
	* @return String $str - translated currency name
	*/
	public static function getTranslatedCurrencyString($str)
	{
		global $app_currency_strings;
		if (isset($app_currency_strings) && isset($app_currency_strings[$str])) {
			return $app_currency_strings[$str];
		}
		return $str;
	}
	
	/**
	* This function is used to get cvid of default "all" view for any module.
	* @return a cvid of a module
	*/
	public static function getIdOfCustomViewByNameAll($module)
	{
		$adb = \PearDatabase::getInstance();

		static $cvidCache = [];
		if (!isset($cvidCache[$module])) {
			$qry_res = $adb->pquery("select cvid from vtiger_customview where viewname='All' and entitytype=?", array($module));
			$cvid = $adb->query_result($qry_res, 0, "cvid");
			$cvidCache[$module] = $cvid;
		}
		return isset($cvidCache[$module]) ? $cvidCache[$module] : '0';
	}

	/** Stores the option in database to display  the tagclouds or not for the current user
	* * @param $id -- user id:: Type integer
	* * Added to provide User based Tagcloud
	* */
	public static function SaveTagCloudView($id = '')
	{
		$adb = \PearDatabase::getInstance();
		$tag_cloud_status = \AppRequest::get('tagcloudview');
		if ($tag_cloud_status == "true") {
			$tag_cloud_view = 0;
		} else {
			$tag_cloud_view = 1;
		}
		if ($id == '') {
			$tag_cloud_view = 1;
		} else {
			$query = "update vtiger_homestuff set visible = ? where userid=? and stufftype='Tag Cloud'";
			$adb->pquery($query, array($tag_cloud_view, $id));
		}
	}

	public static function getSmartyCompiledTemplateFile($template_file, $path = null)
	{
		if ($path == null) {
			$path = ROOT_DIRECTORY . '/cache/templates_c/';
		}
		$mydir = @opendir($path);
		$compiled_file = null;
		while (false !== ($file = readdir($mydir)) && $compiled_file == null) {
			if ($file != '.' && $file != '..' && $file != '.svn') {
				if (is_dir($path . $file)) {
					chdir('.');
					$compiled_file = self::getSmartyCompiledTemplateFile($template_file, $path . $file . '/');
				} else {
					// Check if the file name matches the required template fiel name
					if (strripos($file, $template_file . '.php') == (strlen($file) - strlen($template_file . '.php'))) {
						$compiled_file = $path . $file;
					}
				}
			}
		}
		@closedir($mydir);
		return $compiled_file;
	}

	/** Function to check the file access is made within web root directory and whether it is not from unsafe directories */
	public static function checkFileAccessForInclusion($filepath)
	{
		$unsafeDirectories = array('storage', 'cache', 'test');
		$realfilepath = realpath($filepath);

		/** Replace all \\ with \ first */
		$realfilepath = str_replace('\\\\', '\\', $realfilepath);
		$rootdirpath = str_replace('\\\\', '\\', ROOT_DIRECTORY . DIRECTORY_SEPARATOR);

		/** Replace all \ with / now */
		$realfilepath = str_replace('\\', '/', $realfilepath);
		$rootdirpath = str_replace('\\', '/', $rootdirpath);

		$relativeFilePath = str_replace($rootdirpath, '', $realfilepath);
		$filePathParts = explode('/', $relativeFilePath);

		if (stripos($realfilepath, $rootdirpath) !== 0 || in_array($filePathParts[0], $unsafeDirectories)) {
			$log = \LoggerManager::getInstance();
			$log->error(__CLASS__ . ':' . __FUNCTION__ . '(' . $filepath . ') - Sorry! Attempt to access restricted file. realfilepath: ' . print_r($realfilepath, true));
			throw new \Exception\AppException('Sorry! Attempt to access restricted file.');
		}
	}

	/** Function to check the file deletion within the deletable (safe) directories */
	public static function checkFileAccessForDeletion($filepath)
	{
		$safeDirectories = array('storage', 'cache', 'test');
		$realfilepath = realpath($filepath);

		/** Replace all \\ with \ first */
		$realfilepath = str_replace('\\\\', '\\', $realfilepath);
		$rootdirpath = str_replace('\\\\', '\\', ROOT_DIRECTORY . DIRECTORY_SEPARATOR);

		/** Replace all \ with / now */
		$realfilepath = str_replace('\\', '/', $realfilepath);
		$rootdirpath = str_replace('\\', '/', $rootdirpath);

		$relativeFilePath = str_replace($rootdirpath, '', $realfilepath);
		$filePathParts = explode('/', $relativeFilePath);

		if (stripos($realfilepath, $rootdirpath) !== 0 || !in_array($filePathParts[0], $safeDirectories)) {
			$log = \LoggerManager::getInstance();
			$log->error(__CLASS__ . ':' . __FUNCTION__ . '(' . $filepath . ') - Sorry! Attempt to access restricted file. realfilepath: ' . print_r($realfilepath, true));
			throw new \Exception\AppException('Sorry! Attempt to access restricted file.');
		}
	}

	/** Function to check the file access is made within web root directory. */
	public static function checkFileAccess($filepath)
	{
		if (!self::isFileAccessible($filepath)) {
			$log = vglobal('log');
			$log->error(__CLASS__ . ':' . __FUNCTION__ . '(' . $filepath . ') - Sorry! Attempt to access restricted file. realfilepath: ' . print_r($realfilepath, true));
			throw new \Exception\AppException('Sorry! Attempt to access restricted file.');
		}
	}

	/**
	 * function to return whether the file access is made within vtiger root directory
	 * and it exists.
	 * @param String $filepath relative path to the file which need to be verified
	 * @return Boolean true if file is a valid file within vtiger root directory, false otherwise.
	 */
	public static function isFileAccessible($filepath)
	{
		$realfilepath = realpath($filepath);

		/** Replace all \\ with \ first */
		$realfilepath = str_replace('\\\\', '\\', $realfilepath);
		$rootdirpath = str_replace('\\\\', '\\', ROOT_DIRECTORY . DIRECTORY_SEPARATOR);

		/** Replace all \ with / now */
		$realfilepath = str_replace('\\', '/', $realfilepath);
		$rootdirpath = str_replace('\\', '/', $rootdirpath);

		if (stripos($realfilepath, $rootdirpath) !== 0) {
			return false;
		}
		return true;
	}

	/**
	* This function is used to get the blockid of the settings block for a given label.
	* @param $label - settings label
	* @return string type value
	*/
	public static function getSettingsBlockId($label)
	{
		$adb = \PearDatabase::getInstance();
		$blockid = '';
		$query = "select blockid from vtiger_settings_blocks where label = ?";
		$result = $adb->pquery($query, array($label));
		$noofrows = $adb->num_rows($result);
		if ($noofrows == 1) {
			$blockid = $adb->query_result($result, 0, "blockid");
		}
		return $blockid;
	}

	public static function getSqlForNameInDisplayFormat($input, $module, $glue = ' ')
	{
		$entityFieldInfo = \includes\Modules::getEntityInfo($module);
		$fieldsName = $entityFieldInfo['fieldnameArr'];
		if (is_array($fieldsName)) {
			foreach ($fieldsName as &$value) {
				$formattedNameList[] = $input[$value];
			}
			$formattedNameListString = implode(",'" . $glue . "',", $formattedNameList);
		} else {
			$formattedNameListString = $input[$fieldsName];
		}
		$sqlString = "CONCAT(" . $formattedNameListString . ")";
		return $sqlString;
	}

	public static function getModuleFieldTypeOfDataInfos($tables, $tabid = '')
	{
		$result = [];
		if (!empty($tabid)) {
			$module = Functions::getModuleName($tabid);
			$fieldInfos = Functions::getModuleFieldInfos($tabid);
			foreach ($fieldInfos as $name => $field) {
				if (($field['displaytype'] == '1' || $field['displaytype'] == '3') &&
					($field['presence'] == '0' || $field['presence'] == '2')) {

					$label = Functions::getTranslatedString($field['fieldlabel'], $module);
					$result[$name] = array($label => $field['typeofdata']);
				}
			}
		} else {
			throw new \Exception('Field lookup by table no longer supported');
		}

		return $result;
	}

	public static function return_app_list_strings_language($language, $module = 'Vtiger')
	{
		$strings = \Vtiger_Language_Handler::getModuleStringsFromFile($language, $module);
		return $strings['languageStrings'];
	}
}
