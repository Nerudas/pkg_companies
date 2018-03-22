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

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Language\Text;

class CompaniesControllerEmployees extends BaseController
{

	/**
	 * Method to change Employee data
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function changeData()
	{
		$app = Factory::getApplication();

		// Prepare data
		$data               = array();
		$data['user_id']    = $app->input->get('user_id', 0, 'int');
		$data['company_id'] = $app->input->get('company_id', 0, 'int');
		$data['position']   = $app->input->get('position', '', 'string');
		$data['as_company'] = $app->input->get('as_company', 0, 'int');

		$msg   = Text::_('COM_COMPANIES_EMPLOYEES_CHANGE_SUCCESS');
		$error = false;
		if (!$this->getModel()->changeData($data))
		{
			$msg   = Text::_('COM_COMPANIES_ERROR_EMPLOYEES_CHANGE');
			$error = true;
		}

		echo new JsonResponse($data, $msg, $error);
		$app->close();

		return;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string $name   The model name. Optional.
	 * @param   string $prefix The class prefix. Optional.
	 * @param   array  $config Configuration array for model. Optional.
	 *
	 * @return  BaseDatabaseModel|boolean  Model object on success; otherwise false on failure.
	 *
	 * @since 1.0.0
	 */
	public function getModel($name = 'Employees', $prefix = 'CompaniesModel', $config = array())
	{
		return parent::getModel($name, $prefix, $config);
	}

}