<?php
/**
 * @package    Companies Component
 * @version    1.0.7
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
use Joomla\CMS\Component\ComponentHelper;

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

		$user = Factory::getUser();
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

	/**
	 * Method to delete employee
	 *
	 * @param  int $company_id Company ID
	 * @param int  $user_id    Employee user ID
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function delete($company_id = null, $user_id = null)
	{
		$company_id = (!empty($company_id)) ? $company_id : $this->getState('company.id');

		if (empty($company_id) || empty($user_id))
		{
			$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEE_NOT_FOUND'));

			return false;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('user_id')
			->from('#__companies_employees')
			->where('company_id = ' . $company_id)
			->where('user_id = ' . $user_id);
		$db->setQuery($query);

		if (empty($db->loadResult()))
		{
			$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEE_NOT_FOUND'));

			return false;
		}

		if (!$this->canChange($company_id, $user_id))
		{
			$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEE_PERMISSIONS'));

			return false;
		}

		$db->setQuery($query);

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__companies_employees'))
			->where('company_id = ' . $company_id)
			->where('user_id = ' . $user_id);
		$db->setQuery($query);

		return $db->execute();
	}

	/**
	 * Method to check employee invite permission
	 *
	 * @param  int $company_id Company ID
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function canInvite($company_id = null)
	{
		$user       = Factory::getUser();
		$company_id = (!empty($company_id)) ? $company_id : $this->getState('company.id');

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

	/**
	 * Method to send request
	 *
	 * @param  int    $company_id Company ID
	 * @param  int    $user_id    Employee user ID
	 * @param  string $to         To whom to send a request
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function sendRequest($company_id, $user_id, $to)
	{
		$key = $this->generateKey($to, $company_id, $user_id);
		$db  = Factory::getDbo();

		// Prepare data
		$employee             = new stdClass();
		$employee->company_id = $company_id;
		$employee->user_id    = $user_id;

		if (empty($user_id))
		{
			$this->setError(Text::_('COM_PROFILES_ERROR_PROFILE_NOT_FOUND'));

			return false;
		}

		if (empty($company_id))
		{
			$this->setError(Text::_('COM_COMPANIES_ERROR_COMPANY_NOT_FOUND'));

			return false;
		}

		if (empty($to) || ($to != 'user' && $to != 'company'))
		{
			$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEES_REQUEST'));

			return false;
		}

		// Check exist record
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->quoteName('#__companies_employees'))
			->where('company_id = ' . $company_id)
			->where('user_id = ' . $user_id);
		$db->setQuery($query);
		$exist = ($db->loadResult() > 0);

		// If already confirm employee
		if ($exist && $this->canEditItem($company_id, $user_id, 'com_companies.company.' . $company_id))
		{
			return true;
		}

		// Get company info
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(array('id', 'name', 'created_by as owner'))
			->from('#__companies')
			->where('id = ' . $company_id);
		$db->setQuery($query);
		$company       = $db->loadObject();
		$company->link = Route::_(CompaniesHelperRoute::getCompanyRoute($company->id));

		// Get user info
		$user = Factory::getUser($user_id);
		JLoader::register('ProfilesHelperRoute', JPATH_SITE . '/components/com_profiles/helpers/route.php');
		$user->link = Route::_(ProfilesHelperRoute::getProfileRoute($user_id));

		// Check company owner
		if ($user_id == $company->owner)
		{
			$result = (!$exist) ? $db->insertObject('#__companies_employees', $employee) :
				$db->updateObject('#__companies_employees', $employee, array('company_id', 'user_id'));

			if (!$result)
			{
				$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEES_REQUEST'));

				return false;
			}

			return true;
		}

		// If send to user
		if ($to == 'user')
		{
			// Check can invite
			if (!$this->canInvite($company_id))
			{
				$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEE_PERMISSIONS'));

				return false;
			}

			// If sent to me
			if ($user_id == Factory::getUser()->id)
			{
				$result = (!$exist) ? $db->insertObject('#__companies_employees', $employee) :
					$db->updateObject('#__companies_employees', $employee, array('company_id', 'user_id'));

				if (!$result)
				{
					$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEES_REQUEST'));

					return false;
				}

				return true;
			}
		}

		// If send to company
		if ($to == 'company')
		{
			// If is not  me
			if ($user_id != Factory::getUser()->id)
			{
				$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEE_PERMISSIONS'));

				return false;
			}
		}

		// Add employee record
		$employee->key = $key;
		$result        = (!$exist) ? $db->insertObject('#__companies_employees', $employee) :
			$db->updateObject('#__companies_employees', $employee, array('company_id', 'user_id'));
		if (!$result)
		{
			$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEES_REQUEST'));

			return false;
		}

		// Prepare mail
		$subject = ($to == 'user') ?
			Text::sprintf('COM_COMPANIES_EMPLOYEES_REQUEST_SUBJECT_USER', $company->name) :
			Text::sprintf('COM_COMPANIES_EMPLOYEES_REQUEST_SUBJECT_COMPANY', $user->name);

		$siteConfig = Factory::getConfig();
		$sender     = array($siteConfig->get('mailfrom'), $siteConfig->get('sitename'));

		$recipient = $user->email;
		if ($to == 'company')
		{
			$query = $db->getQuery(true)
				->select('u.email')
				->from($db->quoteName('#__companies_employees', 'ce'))
				->join('LEFT', '#__users AS u ON u.id = ce.user_id')
				->where('company_id = ' . $company_id)
				->where($db->quoteName('ce.key') . ' = ' . $db->quote(''));
			$db->setQuery($query);
			$recipient = $db->loadColumn();

			if (empty($recipient))
			{
				$recipient = Factory::getUser($company->owner)->email;
			}
		}

		$company_name = '<a href="' . $company->link . '" target="_blank">' . $company->name . '</a>';
		$user_name    = '<a href="' . $user->link . '" target="_blank">' . $user->name . '</a>';
		$confirmLink  = trim(Uri::root(), '/') .
			Route::_(CompaniesHelperRoute::getEmployeesConfirmRoute($company_id, $user_id));
		$body         = ($to == 'user') ?
			Text::sprintf('COM_COMPANIES_EMPLOYEES_REQUEST_TEXT_USER', $user_name, $company_name, $confirmLink) :
			Text::sprintf('COM_COMPANIES_EMPLOYEES_REQUEST_TEXT_COMPANY', $company_name, $user_name, $confirmLink);

		// Send email
		$mail = Factory::getMailer();
		$mail->setSubject($subject);
		$mail->setSender($sender);
		$mail->addRecipient($recipient);
		$mail->setBody($body);
		$mail->isHtml(true);
		$mail->Encoding = 'base64';
		$mail->send();

		return true;
	}

	/**
	 * Method to confirm request
	 *
	 * @param  int    $company_id  Company ID
	 * @param  int    $employee_id Employee user ID
	 * @param  string $type        Type of confirmation (user| company | confirm | error)
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function confirm($company_id, $employee_id, $type)
	{
		$user = Factory::getUser();
		$db   = Factory::getDbo();

		// Check type
		if (!$type)
		{
			$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEES_KEY_NOT_FOUND'));

			return false;
		}
		elseif ($type == 'error')
		{
			$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEES_KEY_NOT_FOUND'));

			return false;
		}
		elseif ($type == 'confirm')
		{
			return true;
		}

		if (empty($employee_id) || empty($company_id))
		{
			$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEE_NOT_FOUND'));

			return false;
		}

		// Addition key check
		if ($this->generateKey($type, $company_id, $employee_id) != $this->getKey($company_id, $employee_id))
		{
			$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEES_INVALID_KEY'));

			return false;
		}

		// Prepare data
		$employee             = new stdClass();
		$employee->company_id = $company_id;
		$employee->user_id    = $employee_id;
		$employee->key        = '';

		// If company owner
		$query = $db->getQuery(true)
			->select('created_by')
			->from('#__companies')
			->where('id = ' . $company_id);
		$db->setQuery($query);
		if ($employee_id == $db->loadResult())
		{
			if (!$db->updateObject('#__companies_employees', $employee, array('company_id', 'user_id')))
			{
				$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEES_CONFIRM'));

				return false;
			}

			return true;
		}

		// User confirm
		if ($type == 'user')
		{
			// If it's me
			if ($user->id == $employee_id)
			{
				if (!$db->updateObject('#__companies_employees', $employee, array('company_id', 'user_id')))
				{
					$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEES_CONFIRM'));

					return false;
				}

				return true;
			}

			// If can edit users
			if ($user->authorise('core.edit', 'com_users'))
			{
				if (!$db->updateObject('#__companies_employees', $employee, array('company_id', 'user_id')))
				{
					$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEES_CONFIRM'));

					return false;
				}

				return true;
			}
		}

		// Company confirm
		if ($type == 'company')
		{
			// If can edit companies
			if ($user->authorise('core.edit', 'com_companies'))
			{
				if (!$db->updateObject('#__companies_employees', $employee, array('company_id', 'user_id')))
				{
					$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEES_CONFIRM'));

					return false;
				}

				return true;
			}

			// If can edit company
			$asset = 'com_companies.company.' . $company_id;
			if ($user->authorise('core.edit.own', $asset) && $this->canEditItem($company_id, $user->id, $asset))
			{
				if (!$db->updateObject('#__companies_employees', $employee, array('company_id', 'user_id')))
				{
					$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEES_CONFIRM'));

					return false;
				}

				return true;
			}
		}

		$this->setError(Text::_('COM_COMPANIES_ERROR_EMPLOYEES_CONFIRM'));

		return false;
	}

	/**
	 * Method to get employee confirm key
	 *
	 * @param  int $company_id Company ID
	 * @param int  $user_id    User ID
	 *
	 * @return string | false
	 *
	 * @since 1.0.0
	 */
	public function getKey($company_id, $user_id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(array('e.user_id', 'e.company_id', 'e.key'))
			->from($db->quoteName('#__companies_employees', 'e'))
			->where('company_id = ' . $company_id)
			->where('user_id = ' . $user_id);
		$db->setQuery($query);
		$employee = $db->loadObject();

		return (!empty($employee) && !empty($employee->user_id) && !empty($employee->company_id)) ? $employee->key : false;
	}

	/**
	 * Method to check key
	 *
	 * @param  string $key        Key value
	 * @param  int    $company_id Company ID
	 * @param  int    $user_id    User ID
	 *
	 * @return string Who must confirm
	 *
	 * @since 1.0.0
	 */
	public function checkKey($key, $company_id, $user_id)
	{
		// Already confirm
		if (empty($key))
		{
			return 'confirm';
		}

		// User confirm
		if ($key == $this->generateKey('user', $company_id, $user_id))
		{
			return 'user';
		}

		// Company confirm
		if ($key == $this->generateKey('company', $company_id, $user_id))
		{
			return 'company';
		}

		// Key error
		return 'error';
	}

	/**
	 * Method to generate key
	 *
	 * @param  string $type       Key type
	 * @param  int    $company_id Company ID
	 * @param  int    $user_id    User ID
	 *
	 * @return string key
	 *
	 * @since 1.0.0
	 */
	public function generateKey($type, $company_id, $user_id)
	{
		$secret = ComponentHelper::getParams('com_companies')->get('secret');

		return md5($type . $company_id . $user_id . $secret);
	}
}