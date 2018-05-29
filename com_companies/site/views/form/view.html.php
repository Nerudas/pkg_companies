<?php
/**
 * @package    Companies Component
 * @version    1.0.8
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class CompaniesViewForm extends HtmlView
{
	/**
	 * The JForm object
	 *
	 * @var  JForm
	 *
	 * @since  1.0.0
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  object
	 *
	 * @since  1.0.0
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var  object
	 *
	 * @since  1.0.0
	 */
	protected $state;

	/**
	 * The categories array
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $categories;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var  JObject
	 *
	 * @since  1.0.0
	 */
	protected $canDo;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return mixed A string if successful, otherwise an Error object.
	 *
	 * @throws Exception
	 * @since  1.0.0
	 */
	public function display($tpl = null)
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		// Get model data.
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		$this->link        = CompaniesHelperRoute::getFormRoute($this->state->get('company.id'));
		$this->return_page = $this->get('ReturnPage');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode('\n', $errors), 500);
		}

		// Set Layout
		$params = $this->state->get('params');
		$active = $app->getMenu()->getActive();

		$layout = ($active && !empty($active->query['layout']) &&
			strpos($active->link, 'view=form') &&
			strpos($active->link, '&id=' . (string) $this->state->get('company.id'))
		) ? $active->query['layout'] : $params->get('form_layout', 'default');

		// Check actions
		$asset = 'com_companies.company';
		if (!empty($this->item->id))
		{
			$asset .= '.' . $this->item->id;
		}
		$authorised = (empty($this->item->id)) ? $user->authorise('core.create', $asset) :
			$user->authorise('core.edit', $asset);

		if (!$authorised && !empty($this->item->id) && $user->authorise('core.edit.own', $asset))
		{
			JLoader::register('CompaniesHelperEmployees', JPATH_SITE . '/components/com_companies/helpers/employees.php');

			$authorised = ($this->item->created_by == $user->id) ? true :
				CompaniesHelperEmployees::canEditItem($this->item->id, $user->id, $asset);
		}
		if (!$authorised && $user->guest)
		{
			$login = Route::_('index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()));
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'notice');
			$app->redirect($login, 403);
		}
		elseif (!$authorised)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return false;
		}


		$this->setLayout($layout);

		$this->_prepareDocument();

		return parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	protected function _prepareDocument()
	{
		$app        = Factory::getApplication();
		$link       = $this->link;
		$breadcrumb = ($this->item->id) ? Text::_('COM_COMPANIES_EDIT') : Text::_('COM_COMPANIES_ADD');
		$pathway    = $app->getPathway();
		$pathway->addItem($breadcrumb, $link);

		// Set pathway title
		$title = array();
		foreach ($pathway->getPathWay() as $value)
		{
			$title[] = $value->name;
		}
		$title = implode(' / ', $title);

		$this->document->setTitle($title);
		$this->document->setMetadata('robots', 'noindex');
	}
}
