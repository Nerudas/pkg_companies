<?php
/**
 * @package    Companies Component
 * @version    1.1.1
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

JLoader::register('CompaniesModelCompany', JPATH_ADMINISTRATOR . '/components/com_companies/models/company.php');

class CompaniesModelForm extends CompaniesModelCompany
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('id', 0);
		$this->setState('company.id', $pk);

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		parent::populateState();
	}


	/**
	 * Method to save the form data.
	 *
	 * @param   array $data The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since  1.0.0
	 */
	public function save($data)
	{
		if ($id = parent::save($data))
		{
			Factory::getApplication()->input->set('id', $id);

			return true;
		}

		return false;
	}

	/**
	 * Get the return URL.
	 *
	 * @return  string    The return URL.
	 *
	 * @since  1.0.0
	 */
	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}

}