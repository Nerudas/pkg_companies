<?php
/**
 * @package    Companies Component
 * @version    1.3.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

class JFormFieldEmployees extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'employees';

	/**
	 * Company ID
	 *
	 * @var    int
	 * @since  1.0.0
	 */
	protected $company_id;

	/**
	 * Company employees
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $_employees = null;

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $layout = 'components.com_companies.form.employees';

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

		return $return;
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
		$data['employees']  = $this->getEmployees();
		$data['company_id'] = $this->company_id;

		$params               = array();
		$params['company_id'] = $this->company_id;

		JLoader::register('CompaniesHelperRoute', JPATH_SITE . '/components/com_companies/helpers/route.php');
		$root                 = Uri::root(true) . '/';
		$params['changeURL']  = $root . CompaniesHelperRoute::getEmployeesChangeDataRoute();
		$params['deleteURL']  = $root . CompaniesHelperRoute::getEmployeesDeleteRoute();
		$params['confirmURL'] = $root . CompaniesHelperRoute::getEmployeesConfirmRoute();

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
				->select(array('ce.user_id as id', 'ce.position', 'p.name', 'ce.key', 'ce.as_company'))
				->from($db->quoteName('#__companies_employees', 'ce'))
				->join('LEFT', '#__profiles AS p ON p.id = ce.user_id')
				->where('ce.company_id = ' . (int) $this->company_id);

			$db->setQuery($query);
			$employees = $db->loadObjectList('id');

			JLoader::register('CompaniesHelperEmployees', JPATH_SITE . '/components/com_companies/helpers/employees.php');
			JLoader::register('ProfilesHelperRoute', JPATH_SITE . '/components/com_profiles/helpers/route.php');
			foreach ($employees as &$employee)
			{
				$imagesHelper     = new FieldTypesFilesHelper();
				$avatar           = $imagesHelper->getImage('avatar', 'images/profiles/' . $employee->id,
					'media/com_profiles/images/no-avatar.jpg', false);
				$employee->avatar = Uri::root(true) . '/' . $avatar;

				$employee->confirm = CompaniesHelperEmployees::keyCheck($employee->key, $this->company_id, $employee->id);
				unset($employee->key);

				$link = 'index.php?option=com_users&task=user.edit&id=' . $employee->id;
				if (Factory::getApplication()->isSite())
				{
					$link = (Factory::getUser()->id == $employee->id) ?
						Route::_('index.php?option=com_users&view=profile&layout=edit')
						: Route::_(ProfilesHelperRoute::getProfileRoute($employee->id));
				}
				$employee->link = $link;

				$employee->as_company = ($employee->as_company == 1);
			}

			$this->_employees = $employees;
		}

		return $this->_employees;
	}
}