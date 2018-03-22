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
		$app   = Factory::getApplication();
		$model = $this->getModel();

		// Prepare data
		$data               = array();
		$data['user_id']    = $app->input->get('user_id', 0, 'int');
		$data['company_id'] = $app->input->get('company_id', 0, 'int');
		$data['position']   = $app->input->get('position', '', 'string');
		$data['as_company'] = $app->input->get('as_company', 0, 'int');

		$msg   = Text::_('COM_COMPANIES_EMPLOYEES_CHANGE_SUCCESS');
		$error = false;
		if (!$model->changeData($data))
		{
			$msg   = $model->getError();
			$error = true;
		}

		echo new JsonResponse($data, $msg, $error);
		$app->close();

		return;
	}

	/**
	 * Method to change Employee data
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function delete()
	{
		$app   = Factory::getApplication();
		$model = $this->getModel();

		$user_id    = $app->input->get('user_id', 0, 'int');
		$company_id = $app->input->get('company_id', 0, 'int');

		if (empty($user_id) || empty($company_id))
		{
			return $this->setResponse('error', 'COM_COMPANIES_ERROR_EMPLOYEE_NOT_FOUND');
		}

		if (!$model->delete($company_id, $user_id))
		{
			return $this->setResponse('error', $model->getError());
		}

		return $this->setResponse('success', 'COM_COMPANIES_EMPLOYEES_DELETE_SUCCESS');
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

	/**
	 * Method to set Response
	 *
	 * @param  string $status Response status
	 * @param string  $text   Response text
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	protected function setResponse($status, $text = '')
	{
		$app   = Factory::getApplication();
		$popup = $app->input->get('popup', false);

		if ($popup)
		{
			// Set no cache
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('Pragma: no-cache');
			header('Expires: 0');
		}

		if (!empty($text))
		{
			if ($popup)
			{
				echo Text::_($text);
			}
		}

		if ($status == 'success')
		{
			if ($popup)
			{

				echo '<script>setTimeout(function(){ window.opener.location.reload();window.close();},1000)</script>';
			}
		}

		Factory::getApplication()->close();

		return ($status !== 'error');
	}

}