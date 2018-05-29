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
use Joomla\Registry\Registry;

class CompaniesViewList extends HtmlView
{

	/**
	 * The link to add form
	 *
	 * @var  string
	 *
	 * @since 1.0.0
	 */
	protected $addLink;

	/**
	 * The link
	 *
	 * @var  string
	 *
	 * @since 1.0.0
	 */
	protected $link;

	/**
	 * An array of items
	 *
	 * @var  array
	 *
	 * @since 1.0.0
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  JPagination
	 *
	 * @since 1.0.0
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  object
	 *
	 * @since 1.0.0
	 */
	protected $state;

	/**
	 * Form object for search filters
	 *
	 * @var  JForm
	 *
	 * @since 1.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var  array
	 *
	 * @since 1.0.0
	 */
	public $activeFilters;

	/**
	 * Application params
	 *
	 * @var  Registry
	 *
	 * @since 1.0.0
	 */
	public $params;

	/**
	 * Pageclass_sfx params
	 *
	 * @var  string
	 *
	 * @since 1.0.0
	 */
	public $pageclass_sfx;

	/**
	 * Display the view
	 *
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return mixed A string if successful, otherwise an Error object.
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();

		$this->state         = $this->get('State');
		$this->link          = Route::_(CompaniesHelperRoute::getListRoute());
		$this->addLink       = Route::_(CompaniesHelperRoute::getFormRoute());
		$this->items         = $this->get('Items');
		$this->params        = $this->state->get('params');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$active = $app->getMenu()->getActive();

		// Check to see which parameters should take priority
		if ($active)
		{
			$currentLink = $active->link;
			// Load layout from active query (in case it is an alternative menu item)
			if (strpos($currentLink, 'view=companies') && isset($active->query['layout']))
			{
				$this->setLayout($active->query['layout']);
			}
		}

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$this->_prepareDocument();

		return parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since 1.0.0
	 */
	protected function _prepareDocument()
	{
		$app      = Factory::getApplication();
		$url      = rtrim(URI::root(), '/') . $this->link;
		$sitename = $app->get('sitename');
		$menu     = $app->getMenu()->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('COM_COMPANIES'));
		}
		$title = $this->params->get('page_title', $sitename);

		if ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $sitename, $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $sitename);
		}

		// Set Meta Title
		$this->document->setTitle($title);

		// Set Meta Description
		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		// Set Meta Keywords
		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		// Set Meta Robots
		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		// Set Meta Image
		if ($this->params->get('menu-meta_image', ''))
		{
			$this->document->setMetaData('image', Uri::base() . $this->params->get('menu-meta_image'));
		}

		// Set Meta twitter
		$this->document->setMetaData('twitter:card', 'summary_large_image');
		$this->document->setMetaData('twitter:site', $sitename);
		$this->document->setMetaData('twitter:creator', $sitename);
		$this->document->setMetaData('twitter:title', $this->document->getTitle());
		if ($this->document->getMetaData('description'))
		{
			$this->document->setMetaData('twitter:description', $this->document->getMetaData('description'));
		}
		if ($this->document->getMetaData('image'))
		{
			$this->document->setMetaData('twitter:image', $this->document->getMetaData('image'));
		}
		$this->document->setMetaData('twitter:url', $url);

		// Set Meta Open Graph
		$this->document->setMetadata('og:type', 'website', 'property');
		$this->document->setMetaData('og:site_name', $sitename, 'property');
		$this->document->setMetaData('og:title', $this->document->getTitle(), 'property');
		if ($this->document->getMetaData('description'))
		{
			$this->document->setMetaData('og:description', $this->document->getMetaData('description'), 'property');
		}
		if ($this->document->getMetaData('image'))
		{
			$this->document->setMetaData('og:image', $this->document->getMetaData('image'), 'property');
		}
		$this->document->setMetaData('og:url', $url, 'property');
	}
}