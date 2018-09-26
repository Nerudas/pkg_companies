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

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Response\JsonResponse;

class CompaniesControllerCompany extends FormController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $text_prefix = 'COM_COMPANIES_COMPANY';

	/**
	 * Method to update profile Images
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since 1.0.0
	 */
	public function checkAlias()
	{
		$app   = Factory::getApplication();
		$data  = $this->input->post->get('jform', array(), 'array');
		$model = $this->getModel();
		$check = $model->checkAlias($data['id'], $data['alias']);

		echo new JsonResponse($check->data, $check->msg, ($check->status == 'error'));

		$app->close();

		return true;
	}
}