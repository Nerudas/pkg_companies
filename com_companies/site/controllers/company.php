<?php
/**
 * @package    Companies Component
 * @version    1.0.9
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

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
	 * Method to update item icon
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since  1.0.0
	 */
	public function updateImages()
	{
		$app   = Factory::getApplication();
		$id    = $app->input->get('id', 0, 'int');
		$value = $app->input->get('value', '', 'raw');
		$field = $app->input->get('field', '', 'raw');
		if (!empty($id) & !empty($field))
		{
			JLoader::register('imageFolderHelper', JPATH_PLUGINS . '/fieldtypes/ajaximage/helpers/imagefolder.php');
			$helper = new imageFolderHelper('images/companies');
			$helper->saveImagesValue($id, '#__companies', $field, $value);
		}

		$app->close();

		return true;
	}

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

	/**
	 * Method to save a record.
	 *
	 * @param   string $key    The name of the primary key of the URL variable.
	 * @param   string $urlVar The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since  1.0.0
	 */
	public function save($key = null, $urlVar = null)
	{
		$result = parent::save($key, $urlVar);
		$app    = Factory::getApplication();
		$id     = $app->input->getInt('id');

		if ($result)
		{
			$this->setMessage(Text::_($this->text_prefix . (($data['id'] == 0) ? '_SUBMIT' : '') . '_SAVE_SUCCESS'));
		}

		$return = ($result) ? CompaniesHelperRoute::getCompanyRoute($id) :
			CompaniesHelperRoute::getFormRoute($id);
		$this->setRedirect(Route::_($return));

		return $result;
	}

	/**
	 * Method to check if you can edit an existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data An array of input data.
	 * @param   string $key  The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$user     = Factory::getUser();
		$selector = (!empty($data[$key])) ? $data[$key] : 0;
		$author   = (!empty($data['created_by'])) ? $data['created_by'] : 0;

		$asset   = 'com_companies.company.' . $selector;
		$canEdit = $user->authorise('core.edit', $asset);
		if (!$canEdit && $user->authorise('core.edit.own', $asset))
		{
			JLoader::register('CompaniesHelperEmployees', JPATH_SITE . '/components/com_companies/helpers/employees.php');
			$canEdit = ($author == $user->id) ? true :
				CompaniesHelperEmployees::canEditItem($selector, $user->id, $asset);
		}

		return $canEdit;
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string $key The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since  1.0.0
	 */

	public function cancel($key = null)
	{
		parent::cancel($key);

		$app = Factory::getApplication();
		$id  = $app->input->getInt('id');

		$return = (!empty($id)) ? CompaniesHelperRoute::getCompanyRoute($id) :
			CompaniesHelperRoute::getListRoute();

		$this->setRedirect(Route::_($return));

		return $result;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string $name   The model name. Optional.
	 * @param   string $prefix The class prefix. Optional.
	 * @param   array  $config Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since  1.0.0
	 */
	public function getModel($name = 'Form', $prefix = 'CompaniesModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

}