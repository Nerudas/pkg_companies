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

use Joomla\CMS\MVC\Model\ListModel;

class CompaniesModelCompanies extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @since 1.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'c.id', 'id',
				'c.title', 'title',
				'c.alias', 'alias',
				'c.about', 'about',
				'c.status', 'status',
				'c.contacts', 'contacts',
				'c.requisites', 'requisites',
				'c.logo', 'logo',
				'c.header', 'header',
				'c.portfolio', 'portfolio',
				'c.state', 'state',
				'c.created', 'created',
				'c.created_by', 'created_by',
				'c.modified', 'modified',
				'c.attribs', 'attribs',
				'c.metakey', 'metakey',
				'c.metadesc', 'metadesc',
				'c.access', 'access',
				'c.hits', 'hits',
				'c.region', 'region', 'region_name',
				'c.metadata', 'metadata',
				'c.tags_search', 'tags_search',
				'c.tags_map', 'tags_map',
			);
		}
		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string $ordering  An optional ordering field.
	 * @param   string $direction An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since 1.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$region = $this->getUserStateFromRequest($this->context . '.filter.region', 'filter_region', '');
		$this->setState('filter.region', $region);

		$tags = $this->getUserStateFromRequest($this->context . '.filter.tags', 'filter_tags', '');
		$this->setState('filter.tags', $tags);

		// List state information.

		$ordering  = empty($ordering) ? 'c.created' : $ordering;
		$direction = empty($direction) ? 'desc' : $direction;
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string $id A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since 1.0.0
	 */
	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.region');
		$id .= ':' . serialize($this->getState('filter.tags'));

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since 1.0.0
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('c.*')
			->from($db->quoteName('#__companies', 'c'));

		// Join over the regions.
		$query->select(array('r.id as region_id', 'r.name AS region_name'))
			->join('LEFT', '#__regions AS r ON r.id = 
					(CASE c.region WHEN ' . $db->quote('*') . ' THEN 100 ELSE c.region END)');


		// Filter by regions
		$region = $this->getState('filter.region');
		if (is_numeric($region))
		{
			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_nerudas/models');
			$regionModel = JModelLegacy::getInstance('regions', 'NerudasModel');
			$regions     = $regionModel->getRegionsIds($region);
			$regions[]   = $db->quote('*');
			$regions[]   = $regionModel->getRegion($region)->parent;
			$regions     = array_unique($regions);
			$query->where($db->quoteName('c.region') . ' IN (' . implode(',', $regions) . ')');
		}

		// Group by
		$query->group(array('c.id'));

		// Add the list ordering clause.
		$ordering  = $this->state->get('list.ordering', 'c.created');
		$direction = $this->state->get('list.direction', 'desc');
		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Gets an array of objects from the results of database query.
	 *
	 * @param   string  $query      The query.
	 * @param   integer $limitstart Offset.
	 * @param   integer $limit      The number of records.
	 *
	 * @return  object[]  An array of results.
	 *
	 * @since 1.0.0
	 * @throws  \RuntimeException
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$this->getDbo()->setQuery($query, $limitstart, $limit);

		return $this->getDbo()->loadObjectList('id');
	}


}