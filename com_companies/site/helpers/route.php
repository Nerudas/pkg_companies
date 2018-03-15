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
	 * Fetches the profile route
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
}