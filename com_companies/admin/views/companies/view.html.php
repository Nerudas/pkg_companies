<?php
/**
 * @package    Companies Component
 * @version    1.0.5
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;


class CompaniesViewCompanies extends HtmlView
{
	/**
	 * An array of items
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  JPagination
	 *
	 * @since  1.0.0
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  object
	 *
	 * @since  1.0.0
	 */
	protected $state;

	/**
	 * Form object for search filters
	 *
	 * @var  JForm
	 *
	 * @since  1.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	public $activeFilters;

	/**
	 * View sidebar
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	public $sidebar;

	/**
	 * Display the view
	 *
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return mixed A string if successful, otherwise an Error object.
	 *
	 * @throws Exception
	 *
	 * @since  1.0.0
	 */
	public function display($tpl = null)
	{
		CompaniesHelper::addSubmenu('companies');

		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->state         = $this->get('State');

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		return parent::display($tpl);
	}


	/**
	 * Add the extension title and toolbar.
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	protected function addToolbar()
	{
		$user  = Factory::getUser();
		$canDo = CompaniesHelper::getActions('com_companies', 'companies');

		JToolBarHelper::title(Text::_('COM_COMPANIES'), 'list');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('company.add');
		}
		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('company.edit');
		}
		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('companies.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('companies.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		}
		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::custom('companies.toWork', 'box-add', 'toWork',
				'COM_PROFILES_TOOLBAR_TO_WORK', true);
			JToolbarHelper::custom('companies.unWork', 'box-remove', 'unWork',
				'COM_PROFILES_TOOLBAR_UN_WORK', true);
		}
		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'companies.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('companies.trash');
		}
		if ($user->authorise('core.admin', 'com_companies') || $user->authorise('core.options', 'com_companies'))
		{
			JToolbarHelper::preferences('com_companies');
		}
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since  1.0.0
	 */
	protected function getSortFields()
	{
		return [
			'c.state'      => Text::_('JSTATUS'),
			'c.id'         => Text::_('JGRID_HEADING_ID'),
			'c.name'       => Text::_('COM_COMPANIES_COMPANY_NAME'),
			'c.created'    => Text::_('JGLOBAL_CREATED_DATE'),
			'c.created_by' => Text::_('JAUTHOR'),
			'c.hits'       => Text::_('JGLOBAL_HITS'),
			'access_level' => Text::_('JGRID_HEADING_ACCESS'),
		];
	}
}