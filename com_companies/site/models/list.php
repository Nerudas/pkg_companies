<?php
/**
 * @package    Companies Component
 * @version    1.0.1
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Form\Form;

class CompaniesModelList extends ListModel
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
				'c.name', 'name',
				'c.alias', 'alias',
				'c.about', 'about',
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
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		// Load the parameters. Merge Global and Menu Item params into new object
		$params     = $app->getParams();
		$menuParams = new Registry;
		$menu       = $app->getMenu()->getActive();
		if ($menu)
		{
			$menuParams->loadString($menu->getParams());
		}
		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);
		$this->setState('params', $mergedParams);

		// Published state
		if ((!$user->authorise('core.manage', 'com_companies')))
		{
			// Limit to published for people who can't edit or edit.state.
			$this->setState('filter.published', 1);
		}
		else
		{
			$this->setState('filter.published', array(0, 1));
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$region = $this->getUserStateFromRequest($this->context . '.filter.region', 'filter_region', '');
		$this->setState('filter.region', $region);

		$tags = $this->getUserStateFromRequest($this->context . '.filter.tags', 'filter_tags', '');
		$this->setState('filter.tags', $tags);

		// List state information.
		parent::populateState($ordering, $direction);

		// Set limit & limitstart for query.
		$this->setState('list.limit', $params->get('companies_limit', 10, 'uint'));
		$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));

		// Set ordering for query.
		$ordering  = empty($ordering) ? 'c.created' : $ordering;
		$direction = empty($direction) ? 'desc' : $direction;
		$this->setState('list.ordering', $ordering);
		$this->setState('list.direction', $direction);
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
		$id .= ':' . serialize($this->getState('filter.published'));
		$id .= ':' . serialize($this->getState('filter.tags'));

		return parent::getStoreId($id);
	}

	/**
	 * Get the filter form
	 *
	 * @param   array   $data     data
	 * @param   boolean $loadData load current data
	 *
	 * @return  Form|boolean  The Form object or false on error
	 *
	 * @since 1.0.0
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		$component = ComponentHelper::getParams('com_companies');
		if ($form = parent::getFilterForm())
		{
			// Set tags Filter
			if ($component->get('company_tags', 0))
			{
				$form->setFieldAttribute('tags', 'parents', implode(',', $component->get('company_tags')), 'filter');
			}

			$params = $this->getState('params');
			if ($params->get('search_placeholder', ''))
			{
				$form->setFieldAttribute('search', 'hint', $params->get('search_placeholder'), 'filter');
			}
		}

		return $form;
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
		$user = Factory::getUser();

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('c.*')
			->from($db->quoteName('#__companies', 'c'));

		// Join over the regions.
		$query->select(array('r.id as region_id', 'r.name AS region_name'))
			->join('LEFT', '#__regions AS r ON r.id = 
					(CASE c.region WHEN ' . $db->quote('*') . ' THEN 100 ELSE c.region END)');

		// Filter by access level
		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('c.access IN (' . $groups . ')');
		}

		// Filter by published state.
		$published = $this->getState('filter.published');
		if (!empty($published))
		{
			if (is_numeric($published))
			{
				$query->where('( c.state = ' . (int) $published .
					' OR ( c.created_by = ' . $user->id . ' AND c.state IN (0,1)))');
			}
			elseif (is_array($published))
			{
				$query->where('c.state IN (' . implode(',', $published) . ')');
			}
		}

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

		// Filter by tags.
		$tags = $this->getState('filter.tags');
		if (is_array($tags))
		{
			$tags = ArrayHelper::toInteger($tags);
			$tags = implode(',', $tags);
			if (!empty($tags))
			{
				$query->join('LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
					. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('c.id')
					. ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_companies.company'))
					->where($db->quoteName('tagmap.tag_id') . ' IN (' . $tags . ')');
			}
		}

		// Filter by search.
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('c.id = ' . (int) substr($search, 3));
			}
			else
			{
				$text_columns = array('c.name', 'c.about', 'c.contacts', 'c.requisites', 'c.tags_search', 'r.name');

				$sql = array();
				foreach ($text_columns as $column)
				{
					$sql[] = $db->quoteName($column) . ' LIKE '
						. $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				}
				$number = $this->clearPhoneNumber($search);
				echo '<pre>', print_r($number, true), '</pre>';
				$code = '+7';
				if (!empty($number))
				{
					$phone         = $code . $number;
					$phone_columns = array('c.contacts');
					foreach ($phone_columns as $column)
					{
						$sql[] = $column . ' LIKE ' . $db->quote('%' . $phone . '%');
					}
				}
				$query->where('(' . implode(' OR ', $sql) . ')');
			}
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

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since 1.0.0
	 */
	public function getItems()
	{
		$items = parent::getItems();
		if (!empty($items))
		{
			$user = Factory::getUser();
			JLoader::register('CompaniesHelperEmployees', JPATH_SITE . '/components/com_companies/helpers/employees.php');

			foreach ($items as &$item)
			{
				$item->logo = (!empty($item->logo) && JFile::exists(JPATH_ROOT . '/' . $item->logo)) ?
					Uri::root(true) . '/' . $item->logo : false;

				$item->link     = Route::_(CompaniesHelperRoute::getCompanyRoute($item->id));
				$item->editLink = false;
				if (!$user->guest)
				{
					$userId = $user->id;
					$asset  = 'com_companies.company.' . $item->id;

					$editLink = Route::_(CompaniesHelperRoute::getFormRoute($item->id));

					// Check general edit permission first.
					if ($user->authorise('core.edit', $asset))
					{
						$item->editLink = $editLink;
					}
					// Now check if edit.own is available.
					elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
					{
						// Check for a valid user and that they are the owner.
						if ($userId == $item->created_by)
						{
							$item->editLink = $editLink;
						}

						// Check for a valid user and that they are the employee.
						elseif (CompaniesHelperEmployees::canEditItem($item->id, $userId, $asset))
						{
							$item->editLink = $editLink;
						}
					}
				}

				// Convert the contacts field from json.
				$item->contacts = new Registry($item->contacts);
				if ($phones = $item->contacts->get('phones'))
				{
					$phones = ArrayHelper::fromObject($phones, false);
					$item->contacts->set('phones', $phones);
				}

				// Get Tags
				$item->tags = new TagsHelper;
				$item->tags->getItemTags('com_companies.company', $item->id);
			}
		}

		return $items;
	}

	/**
	 * Gets the value of a user state variable and sets it in the session
	 *
	 * This is the same as the method in \JApplication except that this also can optionally
	 * force you back to the first page when a filter has changed
	 *
	 * @param   string  $key       The key of the user state variable.
	 * @param   string  $request   The name of the variable passed in a request.
	 * @param   string  $default   The default value for the variable if not found. Optional.
	 * @param   string  $type      Filter for the variable, for valid values see {@link \JFilterInput::clean()}. Optional.
	 * @param   boolean $resetPage If true, the limitstart in request is set to zero
	 *
	 * @return  mixed  The request user state.
	 *
	 * @since 1.0.0
	 */
	public function getUserStateFromRequest($key, $request, $default = null, $type = 'none', $resetPage = true)
	{
		$app       = Factory::getApplication();
		$set_state = $app->input->get($request, null, $type);
		$new_state = parent::getUserStateFromRequest($key, $request, $default, $type, $resetPage);
		if ($new_state == $set_state)
		{
			return $new_state;
		}
		$app->setUserState($key, $set_state);

		return $set_state;
	}

	/**
	 * Clear phone number
	 *
	 * @param  string $number Phone number
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function clearPhoneNumber($number)
	{
		if (mb_strlen($number) > 10)
		{
			$number = str_replace(array('+7'), '', $number);
			$number = preg_replace('/\D/', '', $number);

		}
		if (mb_strlen($number) > 10 && mb_substr($number, 0, 1) == 8)
		{
			$number = mb_substr($number, 1);
		}
		$number = (int) $number;

		return $number;
	}
}