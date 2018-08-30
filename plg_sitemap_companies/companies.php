<?php
/**
 * @package    Sitemap - Companies Plugin
 * @version    1.2.2
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

class plgSitemapCompanies extends CMSPlugin
{

	/**
	 * Urls array
	 *
	 * @var    array
	 *
	 * @since  1.0.0
	 */
	protected $_urls = null;

	/**
	 * Method to get Links array
	 *
	 * @return array
	 *
	 * @since 1.1.1
	 */
	public function getUrls()
	{
		if ($this->_urls === null)
		{

			// Include route helper
			JLoader::register('CompaniesHelperRoute', JPATH_SITE . '/components/com_companies/helpers/route.php');

			$db   = Factory::getDbo();
			$user = Factory::getUser(0);

			// Get items
			$query = $db->getQuery(true)
				->select(array('c.id', 'c.modified'))
				->from($db->quoteName('#__companies', 'c'))
				->where('c.state = 1')
				->where('c.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')')
				->order('c.modified DESC');

			$db->setQuery($query);
			$companies = $db->loadObjectList('id');

			$company_changefreq = $this->params->def('company_changefreq', 'weekly');
			$company_priority   = $this->params->def('company_priority', '0.5');


			foreach ($companies as $company)
			{
				$url             = new stdClass();
				$url->loc        = CompaniesHelperRoute::getCompanyRoute($company->id);
				$url->changefreq = $company_changefreq;
				$url->priority   = $company_priority;
				$url->lastmod    = $company->modified;

				$companies_urls[] = $url;
			}

			// Get Tags
			$navtags        = ComponentHelper::getParams('com_companies')->get('tags', array());
			$tag_changefreq = $this->params->def('tag_changefreq', 'weekly');
			$tag_priority   = $this->params->def('tag_priority', '0.5');

			$tags              = array();
			$tags[1]           = new stdClass();
			$tags[1]->id       = 1;
			$tags[1]->modified = array_shift($companies)->modified;

			if (!empty($navtags))
			{
				$query = $db->getQuery(true)
					->select(array('tm.tag_id as id', 'max(tm.tag_date) as modified'))
					->from($db->quoteName('#__contentitem_tag_map', 'tm'))
					->join('LEFT', '#__tags AS t ON t.id = tm.tag_id')
					->where($db->quoteName('tm.type_alias') . ' = ' . $db->quote('com_companies.company'))
					->where('tm.tag_id IN (' . implode(',', $navtags) . ')')
					->where('t.published = 1')
					->where('t.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')')
					->group('t.id');
				$db->setQuery($query);

				$tags = $tags + $db->loadObjectList('id');
			}

			$tags_urls = array();
			foreach ($tags as $tag)
			{
				$url             = new stdClass();
				$url->loc        = CompaniesHelperRoute::getListRoute($tag->id);
				$url->changefreq = $tag_changefreq;
				$url->priority   = $tag_priority;
				$url->lastmod    = $tag->modified;

				$tags_urls[] = $url;
			}

			$this->_urls = array_merge($tags_urls, $companies_urls);
		}

		return $this->_urls;

	}
}