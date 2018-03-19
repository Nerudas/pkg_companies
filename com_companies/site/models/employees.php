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
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

class CompaniesModelEmployees extends BaseDatabaseModel
{
	/**
	 * Company employees
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $_items = null;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->getInt('company');
		$this->setState('company.id', $pk);

		// Published state
		if ((!$user->authorise('core.manage', 'com_companies')))
		{
			// Limit to published for people who can't edit or edit.state.
			$this->setState('filter.published', 1);
		}
		else
		{
			$this->setState('filter.published', array(0, 1));
		}

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
	}


	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string $id A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since 1.0.0
	 */
	protected function getStoreId($id = '')
	{
		$id .= ':' . serialize($this->getState('company.id'));
		$id .= ':' . serialize($this->getState('filter.published'));

		return parent::getStoreId($id);
	}

	/**
	 * Method to get company employees
	 *
	 * @param int $pk Company ID
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 */
	public function getItems($pk = null)
	{
		if (!is_array($this->_items))
		{
			$pk   = (!empty($pk)) ? $pk : $this->getState('company.id');
			$user = Factory::getUser();

			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select(array('ce.user_id as id', 'ce.position', 'p.name', 'p.avatar'))
				->from($db->quoteName('#__companies_employees', 'ce'))
				->join('LEFT', '#__profiles AS p ON p.id = ce.user_id')
				->where('ce.company_id = ' . (int) $pk)
				->where($db->quoteName('key') . ' = ' . $db->quote(''));

			// Filter by access level & published
			if (!$user->authorise('core.admin'))
			{
				$groups = implode(',', $user->getAuthorisedViewLevels());
				$query->where('p.access IN (' . $groups . ')')
					->where('( p.state = 1  OR ( p.id = ' . $user->id . ' AND p.state IN (0,1)))');;
			}

			$db->setQuery($query);
			$items = $db->loadObjectList('id');

			JLoader::register('ProfilesHelperRoute', JPATH_SITE . '/components/com_profiles/helpers/route.php');
			foreach ($items as &$item)
			{
				$item->link = Route::_(ProfilesHelperRoute::getProfileRoute($item->id));

				$avatar       = (!empty($item->avatar) && JFile::exists(JPATH_ROOT . '/' . $item->avatar)) ?
					$item->avatar : 'media/com_profiles/images/no-avatar.jpg';
				$item->avatar = Uri::root(true) . '/' . $avatar;
			}

			$this->_items = $items;
		}

		return $this->_items;
	}
}