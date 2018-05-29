<?php
/**
 * @package    Companies Component
 * @version    1.0.8
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\RouteHelper;

class CompaniesHelperRoute extends RouteHelper
{
	/**
	 * Fetches the list route
	 *
	 * @return  string
	 *
	 * @since 1.0.0
	 */
	public static function getListRoute()
	{
		return 'index.php?option=com_companies&view=list&key=1';
	}

	/**
	 * Fetches the company route
	 *
	 * @param  int $id Company ID
	 *
	 * @return  string
	 *
	 * @since 1.0.0
	 */
	public static function getCompanyRoute($id = null)
	{
		return 'index.php?option=com_companies&view=company&key=1&id=' . $id;
	}

	/**
	 * Fetches the form route
	 *
	 * @param  int $id Company ID
	 *
	 * @return  string
	 *
	 * @since  1.0.0
	 *
	 */
	public static function getFormRoute($id = null)
	{
		$link = 'index.php?option=com_companies&view=form&key=1';
		if (!empty($id))
		{
			$link .= '&id=' . $id;
		}

		return $link;
	}

	/**
	 * Fetches the employees change data route
	 *
	 * @param int $company_id Company ID
	 * @param int $user_id    User ID
	 *
	 * @return  string
	 *
	 * @since 1.0.0
	 */
	public static function getEmployeesChangeDataRoute($company_id = null, $user_id = null)
	{
		$link = 'index.php?option=com_companies&task=employees.changeData';

		if (!empty($company_id))
		{
			$link .= '&company_id=' . $company_id;
		}

		if (!empty($user_id))
		{
			$link .= '&user_id=' . $user_id;
		}

		return $link;
	}

	/**
	 * Fetches the employees delete route
	 *
	 * @param int $company_id Company ID
	 * @param int $user_id    User ID
	 *
	 * @return  string
	 *
	 * @since 1.0.0
	 */
	public static function getEmployeesDeleteRoute($company_id = null, $user_id = null)
	{
		$link = 'index.php?option=com_companies&task=employees.delete';

		if (!empty($company_id))
		{
			$link .= '&company_id=' . $company_id;
		}

		if (!empty($user_id))
		{
			$link .= '&user_id=' . $user_id;
		}

		return $link;
	}


	/**
	 * Fetches the employees send request route
	 *
	 * @param int    $company_id Company ID
	 * @param int    $user_id    User ID
	 * @param string $to         To whom to send a request (user | company)
	 *
	 * @return  string
	 *
	 * @since 1.0.0
	 */
	public static function getEmployeesSendRequestRoute($company_id = null, $user_id = null, $to = null)
	{
		$link = 'index.php?option=com_companies&task=employees.sendRequest';

		if (!empty($company_id))
		{
			$link .= '&company_id=' . $company_id;
		}

		if (!empty($user_id))
		{
			$link .= '&user_id=' . $user_id;
		}

		if (!empty($to))
		{
			$link .= '&to=' . $to;
		}

		return $link;
	}


	/**
	 * Fetches the employees confirm route
	 *
	 * @param int $company_id Company ID
	 * @param int $user_id    User ID
	 *
	 * @return  string
	 *
	 * @since 1.0.0
	 */
	public static function getEmployeesConfirmRoute($company_id = null, $user_id = null)
	{
		$link = 'index.php?option=com_companies&task=employees.confirm';

		if (!empty($company_id))
		{
			$link .= '&company_id=' . $company_id;
		}

		if (!empty($user_id))
		{
			$link .= '&user_id=' . $user_id;
		}

		if (!empty($type))
		{
			$link .= '&type=' . $type;
		}

		if (!empty($key))
		{
			$link .= '&key=' . $key;
		}

		return $link;
	}
}