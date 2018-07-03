<?php
/**
 * @package    Companies Component
 * @version    1.1.0
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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

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
	 * Method to change employee data
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

		$return = Route::_('index.php?option=com_users&view=profile');

		if (empty($user_id) || empty($company_id))
		{
			return $this->setResponse('error', 'COM_COMPANIES_ERROR_EMPLOYEE_NOT_FOUND', $return);
		}

		if (!$model->delete($company_id, $user_id))
		{
			return $this->setResponse('error', $model->getError());
		}

		return $this->setResponse('success', 'COM_COMPANIES_EMPLOYEES_DELETE_SUCCESS', $return);
	}

	/**
	 * Method to send employee request
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function sendRequest()
	{
		$app    = Factory::getApplication();
		$user   = Factory::getUser();
		$model  = $this->getModel();
		$return = Route::_('index.php?option=com_users&view=profile');

		$user_id    = $app->input->get('user_id', 0, 'int');
		$company_id = $app->input->get('company_id', 0, 'int');
		$to         = $app->input->get('to', 0, 'raw');

		if ($user->guest)
		{
			$login = Route::_('index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()));
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'notice');
			$app->redirect($login, 403);
		}

		if (empty($to) || ($to != 'user' && $to != 'company'))
		{
			return $this->setResponse('error', 'COM_COMPANIES_ERROR_EMPLOYEES_REQUEST', $return);
		}

		if (empty($user_id) && $to == 'company')
		{
			$user_id = $user->id;
		}

		if (empty($user_id))
		{
			return $this->setResponse('error', 'COM_PROFILES_ERROR_PROFILE_NOT_FOUND', $return);
		}

		if (empty($company_id))
		{
			return $this->setResponse('error', 'COM_COMPANIES_ERROR_COMPANY_NOT_FOUND', $return);
		}


		if (!$model->sendRequest($company_id, $user_id, $to))
		{
			return $this->setResponse('error', $model->getError(), $return);
		}

		return $this->setResponse('success', 'COM_COMPANIES_EMPLOYEES_REQUEST_SUCCESS', $return);
	}

	/**
	 * Method to confirm employee request
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function confirm()
	{
		$app    = Factory::getApplication();
		$user   = Factory::getUser();
		$model  = $this->getModel();
		$return = Route::_('index.php?option=com_users&view=profile');

		$user_id    = $app->input->get('user_id', $user->id, 'int');
		$company_id = $app->input->get('company_id', 0, 'int');

		// If guest
		if ($user->guest)
		{
			$login = Route::_('index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()));
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'notice');
			$app->redirect($login, 403);
		}

		if (empty($user_id))
		{
			return $this->setResponse('error', 'COM_PROFILES_ERROR_PROFILE_NOT_FOUND', $return);
		}

		if (empty($company_id))
		{
			return $this->setResponse('error', 'COM_COMPANIES_ERROR_COMPANY_NOT_FOUND', $return);
		}

		// Get key
		$key = $model->getKey($company_id, $user_id);
		if (!$key)
		{
			return $this->setResponse('error', 'COM_COMPANIES_ERROR_EMPLOYEE_NOT_FOUND', $return);
		}

		// Get confirm type
		$type = $model->checkKey($key, $company_id, $user_id);
		if (!$type || $type == 'error')
		{
			return $this->setResponse('error', 'COM_COMPANIES_ERROR_EMPLOYEES_KEY_NOT_FOUND', $return);
		}
		elseif ($type == 'confirm')
		{
			return $this->setResponse('success', 'COM_COMPANIES_EMPLOYEES_CONFIRM_SUCCESS', $return);
		}

		if (!$model->confirm($company_id, $user_id, $type))
		{
			return $this->setResponse('error', $model->getError(), $return);
		}

		return $this->setResponse('success', 'COM_COMPANIES_EMPLOYEES_CONFIRM_SUCCESS', $return);
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
	 * @param string  $return Return Link
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	protected function setResponse($status, $text = '', $return = '')
	{
		$app   = Factory::getApplication();
		$popup = $app->input->get('popup', false);

		// Set no cache
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Pragma: no-cache');
		header('Expires: 0');

		if (!empty($text))
		{
			if (!empty($return) && !$popup)
			{
				$type = ($status == 'success') ? 'success' : 'error';
				$app->enqueueMessage(Text::_($text), $type);
			}
			else
			{
				echo Text::_($text);
			}

		}

		if ($status == 'success' && $popup)
		{
			echo '<script>setTimeout(function(){ window.opener.location.reload();window.close();},1000)</script>';
		}

		if ($popup)
		{
			$app->close();
		}
		elseif (!empty($return))
		{
			$app->redirect($return);
		}

		return ($status !== 'error');
	}

}