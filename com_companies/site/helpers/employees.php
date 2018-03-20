<?php
/**
 * @package    Companies Component
 * @version    1.0.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class CompaniesHelperEmployees
{
	protected static $_canEditItems = array();

	/**
	 * Method to check edit permission for companies item
	 *
	 * @param  int    $company_id Company ID
	 * @param  int    $user_id    User ID
	 * @param  string $asset      Asset name
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function canEditItem($company_id = null, $user_id = null, $asset)
	{
		if (!isset(self::$_canEditItems[$asset]))
		{
			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_companies/models');
			$model = BaseDatabaseModel::getInstance('Employees', 'CompaniesModel', array('ignore_request' => true));

			self::$_canEditItems[$asset] = $model->canEditItem($company_id, $user_id, $asset);
		}

		return self::$_canEditItems[$asset];
	}
}