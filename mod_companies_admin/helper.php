<?php
/**
 * @package    Companies - Administrator Module
 * @version    1.2.2
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class ModCompaniesAdminHelper
{
	/**
	 * Get Items
	 *
	 * @return bool|string
	 *
	 * @since 1.0.1
	 */
	public static function getAjax()
	{
		if ($params = self::getModuleParams(Factory::getApplication()->input->get('module_id', 0)))
		{
			$app = Factory::getApplication();

			$language = Factory::getLanguage();
			$language->load('com_companies', JPATH_ADMINISTRATOR, $language->getTag(), true);

			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_companies/models',
				'CompaniesModel');
			$model = BaseDatabaseModel::getInstance('Companies', 'CompaniesModel', array('ignore_request' => false));
			$app->setUserState('com_companies.companies.list', array(
				'fullordering' => $params->get('ordering', 'c.created DESC'),
				'limit'        => $params->get('limit', 5)
			));

			$items = $model->getItems();
			$app->setUserState('com_companies.companies.list', '');

			if (count($items))
			{
				ob_start();
				require ModuleHelper::getLayoutPath('mod_' . $app->input->get('module'),
					$params->get('layout', 'default') . '_items');
				$response = ob_get_contents();
				ob_end_clean();

				return $response;
			}
			else
			{
				throw new Exception(Text::_('JGLOBAL_NO_MATCHING_RESULTS'), 404);
			}
		}

		throw new Exception(Text::_('MOD_COMPANIES_ADMIN_ERROR_MODULE_NOT_FOUND'), 404);
	}

	/**
	 * Get Module parameters
	 *
	 * @param int $pk module id
	 *
	 * @return bool|Registry
	 *
	 * @since 1.0.1
	 */
	protected static function getModuleParams($pk = null)
	{
		$pk = (empty($pk)) ? Factory::getApplication()->input->get('module_id', 0) : $pk;
		if (empty($pk))
		{
			return false;
		}

		// Get Params
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('params')
			->from('#__modules')
			->where('id =' . $pk);
		$db->setQuery($query);
		$params = $db->loadResult();

		return (!empty($params)) ? new Registry($params) : false;
	}
}