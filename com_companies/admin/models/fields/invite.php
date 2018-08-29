<?php
/**
 * @package    Companies Component
 * @version    1.2.1
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class JFormFieldInvite extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'invite';

	/**
	 * Company ID
	 *
	 * @var    int
	 * @since  1.0.0
	 */
	protected $company_id;

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $layout = 'components.com_companies.form.invite';

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement $element   The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed            $value     The form field value to validate.
	 * @param   string           $group     The field name group control value. This acts as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   1.0.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if ($return = parent::setup($element, $value, $group))
		{
			$this->company_id = (!empty($this->element['company_id'])) ? (int) $this->element['company_id'] : 0;
		}

		if (empty($this->company_id))
		{
			$this->company_id = $this->form->getValue('id', '');
		}


		return ($this->canInvite()) ? $return : false;
	}

	/**
	 * Method to get the field input markup for a image list.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since  1.0.0
	 */
	protected function getInput()
	{
		if (!empty($this->company_id))
		{
			$renderer = $this->getRenderer($this->layout);

			return $renderer->render($this->getLayoutData());
		}

		return false;
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	protected function getLayoutData()
	{
		$data               = parent::getLayoutData();
		$data['company_id'] = $this->company_id;

		$params               = array();
		$params['company_id'] = $this->company_id;

		JLoader::register('CompaniesHelperRoute', JPATH_SITE . '/components/com_companies/helpers/route.php');
		$root                = Uri::root(true) . '/';
		$params['inviteURL'] = $root . CompaniesHelperRoute::getEmployeesSendRequestRoute();

		Factory::getDocument()->addScriptOptions($this->id, $params);

		return $data;
	}

	/**
	 * Method to get company employees
	 *
	 * @return array  employees list
	 *
	 * @since 1.0.0
	 */
	protected function getEmployees()
	{
		if (!is_array($this->_employees))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select(array('ce.user_id as id', 'ce.position', 'p.name', 'p.avatar', 'ce.key', 'ce.as_company'))
				->from($db->quoteName('#__companies_employees', 'ce'))
				->join('LEFT', '#__profiles AS p ON p.id = ce.user_id')
				->where('ce.company_id = ' . (int) $this->company_id);

			$db->setQuery($query);
			$employees = $db->loadObjectList('id');

			JLoader::register('CompaniesHelperEmployees', JPATH_SITE . '/components/com_companies/helpers/employees.php');

			foreach ($employees as &$employee)
			{
				$avatar           = (!empty($employee->avatar) && JFile::exists(JPATH_ROOT . '/' . $employee->avatar)) ?
					$employee->avatar : 'media/com_profiles/images/no-avatar.jpg';
				$employee->avatar = Uri::root(true) . '/' . $avatar;

				$employee->confirm = CompaniesHelperEmployees::keyCheck($employee->key, $this->company_id, $employee->id);
				unset($employee->key);
				$employee->as_company = ($employee->as_company == 1);
			}

			$this->_employees = $employees;
		}

		return $this->_employees;
	}

	/**
	 * Method to check employee invite permission
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	protected function canInvite()
	{
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_companies/models');
		$model = BaseDatabaseModel::getInstance('Employees', 'CompaniesModel', array('ignore_request' => true));

		return $model->canInvite($this->company_id);
	}
}