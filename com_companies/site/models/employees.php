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
use Joomla\CMS\Language\Text;

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
	 * Permission for companies items
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $_canEditItems = array();

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

	/**
	 * Method to check edit permission for company item
	 *
	 * @param  int    $company_id Company ID
	 * @param  int    $user_id    User ID
	 * @param  string $asset      Asset name
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function canEditItem($company_id = null, $user_id = null, $asset)
	{
		if (!isset($this->_canEditItems[$asset]))
		{
			$company_id = (!empty($company_id)) ? $company_id : $this->getState('company.id');
			$user_id    = (!empty($user_id)) ? $user_id : Factory::getUser()->id;

			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('user_id')
				->from('#__companies_employees')
				->where('company_id = ' . (int) $company_id)
				->where('user_id = ' . (int) $user_id)
				->where($db->quoteName('key') . ' = ' . $db->quote(''));
			$db->setQuery($query);

			$this->_canEditItems[$asset] = (!empty($db->loadResult()));
		}

		return $this->_canEditItems[$asset];
	}

	/**
	 * Method to change employee data
	 *
	 * @param array $data to change
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function changeData($data = array())
	{
		$data['company_id'] = (!empty($data['company_id'])) ? $data['company_id'] : $this->getState('company.id');
		$data['user_id']    = (!empty($data['user_id'])) ? $data['user_id'] : Factory::getUser()->id;

		if (empty($data['user_id']) || empty($data['company_id']))
		{
			$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEE_NOT_FOUND'));

			return false;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('user_id')
			->from('#__companies_employees')
			->where('company_id = ' . $data['company_id'])
			->where('user_id = ' . $data['user_id']);
		$db->setQuery($query);

		if (empty($db->loadResult()))
		{
			$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEE_NOT_FOUND'));

			return false;
		}

		if (!$this->canChange($data['company_id'], $data['user_id']))
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));

			return false;
		}

		// update
		$update = (object) $data;

		return $db->updateObject('#__companies_employees', $update, array('company_id', 'user_id'));
	}

	/**
	 * Method to check employee edit permission
	 *
	 * @param  int $company_id  Company ID
	 * @param int  $employee_id Employee user ID
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function canChange($company_id = null, $employee_id = null)
	{
		$user        = Factory::getUser();
		$company_id  = (!empty($company_id)) ? $company_id : $this->getState('company.id');
		$employee_id = (!empty($employee_id)) ? $employee_id : $user->id;
		$user        = Factory::getUser();

		if (empty($company_id) || empty($employee_id))
		{
			return false;
		}

		// If it's me
		if ($user->id == $employee_id)
		{
			return true;
		}

		// If can edit users
		if ($user->authorise('core.edit', 'com_users'))
		{
			return true;
		}

		// If can edit companies
		if ($user->authorise('core.edit', 'com_companies'))
		{
			return true;
		}

		// If can edit company
		if ($user->authorise('core.edit.own', 'com_companies.company.' . $company_id))
		{
			// If company owner
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('created_by')
				->from('#__companies')
				->where('id = ' . $company_id);
			$db->setQuery($query);
			if ($user->id == $db->loadResult())
			{
				return true;
			}

			if ($this->canEditItem($company_id, $user->id, 'com_companies.company.' . $company_id))
			{
				return true;
			}
		}

		return false;
	}
}