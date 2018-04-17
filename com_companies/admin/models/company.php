<?php
/**
 * @package    Companies Component
 * @version    1.0.3
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Application\SiteApplication;

class CompaniesModelCompany extends AdminModel
{
	/**
	 * Imagefolder helper helper
	 *
	 * @var    new imageFolderHelper
	 *
	 * @since  1.0.0
	 */
	protected $imageFolderHelper = null;


	/**
	 * Profile information
	 *
	 * @var    array
	 *
	 * @since 1.0.1
	 */
	protected $_information = null;


	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @see     AdminModel
	 *
	 * @since   1.0.0
	 */
	public function __construct($config = array())
	{
		JLoader::register('imageFolderHelper', JPATH_PLUGINS . '/fieldtypes/ajaximage/helpers/imagefolder.php');
		$this->imageFolderHelper = new imageFolderHelper('images/companies');

		parent::__construct($config);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer $pk The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			// Convert the notes field to an array.
			$registry    = new Registry($item->notes);
			$item->notes = $registry->toArray();

			// Convert the metadata field to an array.
			$registry       = new Registry($item->metadata);
			$item->metadata = $registry->toArray();

			// Convert the contacts field to an array.
			$registry       = new Registry($item->contacts);
			$item->contacts = $registry->toArray();

			// Convert the attribs field to an array.
			$registry      = new Registry($item->attribs);
			$item->attribs = $registry->toArray();

			// Convert the requisites fields to an array.
			$registry         = new Registry($item->requisites);
			$item->requisites = $registry->toArray();

			// Get Tags
			$item->tags = new TagsHelper;
			$item->tags->getTagIds($item->id, 'com_companies.company');

			// Get Info
			$item->information = $this->getInformation($item);

		}

		return $item;
	}

	/**
	 * Method to get information.
	 *
	 * @param   object $item Company object
	 *
	 * @return array
	 *
	 * @since 1.0.1
	 */
	public function getInformation($item)
	{
		if (!is_array($this->_information))
		{
			$information = array();
			if (!empty($item->id))
			{
				$db = Factory::getDbo();


				$information['id']   = $item->id;
				$information['name'] = $item->name;

				JLoader::register('CompaniesHelperRoute', JPATH_SITE . '/components/com_companies/helpers/route.php');
				$siteRouter          = SiteApplication::getRouter();
				$link                = $siteRouter->build(CompaniesHelperRoute::getCompanyRoute($item->id))->toString();
				$information['link'] = str_replace('administrator/', '', $link);

				$information['logo'] = (!empty($item->logo) && JFile::exists(JPATH_ROOT . '/' . $item->logo)) ?
					Uri::root(true) . '/' . $item->logo : false;


				$contacts = (!empty($item->contacts)) ? $item->contacts : array();
				if (!empty($contacts['email']))
				{
					$information['contacts_email'] = $contacts['email'];
				}
				if (!empty($contacts['phones']))
				{
					$phones = array();
					foreach ($contacts['phones'] as $phone)
					{
						$phones[] = $phone['code'] . $phone['number'];
					}
					$information['contacts_phones'] = implode(',', $phones);
				}
				if (!empty($contacts['site']))
				{
					$information['contacts_site'] = $contacts['site'];
				}
				if (!empty($contacts['vk']))
				{
					$information['contacts_vk'] = $contacts['vk'];
				}
				if (!empty($contacts['facebook']))
				{
					$information['contacts_facebook'] = $contacts['facebook'];
				}
				if (!empty($contacts['instagram']))
				{
					$information['contacts_instagram'] = $contacts['instagram'];
				}
				if (!empty($contacts['odnoklassniki']))
				{
					$information['contacts_odnoklassniki'] = $contacts['odnoklassniki'];
				}

				// Check as_company
				$query = $db->getQuery(true)
					->select('COUNT(*)')
					->from($db->quoteName('#__companies_employees', 'employees'))
					->where('employees.company_id = ' . $item->id)
					->where('employees.as_company = ' . 1)
					->where($db->quoteName('employees.key') . ' = ' . $db->quote(''));
				$db->setQuery($query);
				$information['as_company'] = ($db->loadResult() > 0);


				if ($item->region == '*')
				{
					$information['region'] = Text::_('JGLOBAL_FIELD_REGIONS_ALL');
				}
				else
				{
					$query = $db->getQuery(true)
						->select('name')
						->from('#__regions')
						->where('id = ' . $item->region);
					$db->setQuery($query);
					$region                = $db->loadResult();
					$information['region'] = (!empty($region)) ? $region : Text::_('JGLOBAL_FIELD_REGIONS_NULL');
				}

				// Get Tags
				$tags = '';
				if ((!empty($item->tags->tags)))
				{
					$query = $db->getQuery(true)
						->select('title')
						->from('#__tags')
						->where('id IN (' . $item->tags->tags . ')');
					$db->setQuery($query);
					$tags = implode(',', $db->loadColumn());
				}
				$information['tags'] = $tags;
			}
			$this->_information = $information;
		}

		return $this->_information;
	}


	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   string $type   The table type to instantiate
	 * @param   string $prefix A prefix for the table class name. Optional.
	 * @param   array  $config Configuration array for model. Optional.
	 *
	 * @return  Table    A database object
	 * @since  1.0.0
	 */
	public function getTable($type = 'Companies', $prefix = 'CompaniesTable', $config = array())
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_companies/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array   $data     Data for the form.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A JForm object on success, false on failure
	 *
	 * @since  1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$app  = Factory::getApplication();
		$form = $this->loadForm('com_companies.company', 'company', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		/*
		 * The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		 * The back end uses id so we use that the rest of the time and set it to 0 by default.
		 */
		$id   = ($this->getState('company.id')) ? $this->getState('company.id') : $app->input->get('id', 0);
		$user = Factory::getUser();

		// Check for existing item.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_companies.company.' . (int) $id)))
		{
			// Disable fields for display.
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an item you can edit.
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		// Set Check alias link
		$form->setFieldAttribute('alias', 'checkurl',
			Uri::base(true) . '/index.php?option=com_companies&task=company.checkAlias');

		// Set update images links
		$saveurl = Uri::base(true) . '/index.php?option=com_companies&task=company.updateImages&id='
			. $id . '&field=';
		$form->setFieldAttribute('logo', 'saveurl', $saveurl . 'logo');
		$form->setFieldAttribute('header', 'saveurl', $saveurl . 'header');
		$form->setFieldAttribute('portfolio', 'saveurl', $saveurl . 'portfolio');

		// Set Tags parents
		$config = ComponentHelper::getParams('com_companies');
		if ($config->get('company_tags'))
		{
			$form->setFieldAttribute('tags', 'parents', implode(',', $config->get('company_tags')));
		}

		if (empty($id))
		{
			$form->removeField('employees', '');
			$form->removeField('invite', '');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since  1.0.0
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_companies.edit.company.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}
		$this->preprocessData('com_companies.company', $data);

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array $data The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since 1.0.0
	 */
	public function save($data)
	{
		$app    = Factory::getApplication();
		$pk     = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$filter = InputFilter::getInstance();
		$table  = $this->getTable();
		$db     = Factory::getDbo();
		$isNew  = true;

		// Load the row if saving an existing type.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		$data['id']    = (!isset($data['id'])) ? 0 : $data['id'];
		$data['alias'] = (!isset($data['alias'])) ? '' : $data['alias'];

		// Check alias
		$alias = $this->checkAlias($data['id'], $data['alias']);
		if (!empty($alias->msg))
		{
			$app->enqueueMessage(Text::sprintf('COM_COMPANIES_ERROR_ALIAS', $alias->msg),
				($alias->status == 'error') ? 'error' : 'warning');
		}
		$data['alias'] = $alias->data;

		if (empty($data['created']))
		{
			$data['created'] = Factory::getDate()->toSql();
		}
		$data['modified'] = Factory::getDate()->toSql();

		if (empty($data['region']))
		{
			$data['region'] = $app->input->cookie->get('region', '*');
		}

		if (isset($data['metadata']) && isset($data['metadata']['author']))
		{
			$data['metadata']['author'] = $filter->clean($data['metadata']['author'], 'TRIM');
		}

		if (isset($data['notes']) && is_array($data['notes']))
		{
			$registry      = new Registry($data['notes']);
			$data['notes'] = (string) $registry;
		}

		if (isset($data['contacts']) && is_array($data['contacts']))
		{
			$registry         = new Registry($data['contacts']);
			$data['contacts'] = (string) $registry;
		}

		if (isset($data['requisites']) && is_array($data['requisites']))
		{
			$registry           = new Registry($data['requisites']);
			$data['requisites'] = (string) $registry;
		}

		if (isset($data['attribs']) && is_array($data['attribs']))
		{
			$registry        = new Registry($data['attribs']);
			$data['attribs'] = (string) $registry;
		}

		if (isset($data['metadata']) && is_array($data['metadata']))
		{
			$registry         = new Registry($data['metadata']);
			$data['metadata'] = (string) $registry;
		}

		if (empty($data['created_by']))
		{
			$data['created_by'] = Factory::getUser()->id;
		}

		// Get tags search
		if (!empty($data['tags']))
		{
			$query = $db->getQuery(true)
				->select(array('id', 'title'))
				->from('#__tags')
				->where('id IN (' . implode(',', $data['tags']) . ')');
			$db->setQuery($query);
			$tags = $db->loadObjectList();

			$tags_search = array();
			$tags_map    = array();
			foreach ($tags as $tag)
			{
				$tags_search[$tag->id] = $tag->title;
				$tags_map[$tag->id]    = '[' . $tag->id . ']';
			}

			$data['tags_search'] = implode(', ', $tags_search);
			$data['tags_map']    = implode('', $tags_map);
		}
		else
		{
			$data['tags_search'] = '';
			$data['tags_map']    = '';
		}

		if (parent::save($data))
		{
			$id = $this->getState($this->getName() . '.id');

			// Save images
			$data['imagefolder'] = (!empty($data['imagefolder'])) ? $data['imagefolder'] :
				$this->imageFolderHelper->getItemImageFolder($id);
			if (isset($data['logo']))
			{
				$this->imageFolderHelper->saveItemImages($id, $data['imagefolder'], '#__companies', 'logo', $data['logo']);
			}
			if (isset($data['header']))
			{
				$this->imageFolderHelper->saveItemImages($id, $data['imagefolder'], '#__companies', 'header', $data['header']);
			}
			if (isset($data['portfolio']))
			{
				$this->imageFolderHelper->saveItemImages($id, $data['imagefolder'], '#__companies', 'portfolio', $data['portfolio']);
			}
			// Fix alias
			if ($data['alias'] == 'id0' || $data['alias'] == 'id')
			{
				$alias = $this->checkAlias($id, $data['alias'])->data;

				$update        = new stdClass();
				$update->id    = $id;
				$update->alias = $alias;
				$db->updateObject('#__companies', $update, 'id');

				$update             = new stdClass();
				$update->core_alias = $alias;

				$query = $db->getQuery(true)
					->select('core_content_id')
					->from('#__ucm_content')
					->where($db->quoteName('core_type_alias') . ' = ' . $db->quote('com_companies.company'))
					->where($db->quoteName('core_content_item_id') . ' = ' . $id);
				$db->setQuery($query);
				$update->core_content_id = $db->loadResult();
				if ($update->core_content_id)
				{
					$db->updateObject('#__ucm_content', $update, 'core_content_id');
				}
			}

			// Add employee if new company
			if ($isNew)
			{
				$employee             = new stdClass();
				$employee->company_id = $id;
				$employee->user_id    = $data['created_by'];
				$employee->position   = (!empty($data['position'])) ? $data['position'] : '';
				$employee->as_company = (!empty($data['as_company'])) ? 1 : 0;

				$db->insertObject('#__companies_employees', $employee);
			}

			return $id;
		}

		return false;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array &$pks An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since 1.0.0
	 */
	public function delete(&$pks)
	{
		if (parent::delete($pks))
		{
			// Delete employees
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__companies_employees'))
				->where($db->quoteName('company_id') . ' IN (' . implode(',', $pks) . ')');
			$db->setQuery($query)->execute();

			// Delete images
			foreach ($pks as $pk)
			{
				$this->imageFolderHelper->deleteItemImageFolder($pk);
			}

			return true;
		}

		return false;
	}

	/**
	 * Method to check alias
	 *
	 * @param  int    $id    Item Id
	 * @param  string $alias Item alias
	 *
	 * @return stdClass|string
	 *
	 * @since 1.0.0
	 */
	public function checkAlias($id = 0, $alias = null)
	{
		$response         = new stdClass();
		$response->status = 'success';
		$response->msg    = '';
		$response->data   = $alias;
		$default_alias    = 'id' . $id;
		if (empty($alias))
		{
			$response->data = $default_alias;

			return $response;
		}

		if ($alias == $default_alias)
		{
			$response->data = $default_alias;

			return $response;
		}

		// Check form
		if (in_array($alias, array('form', 'edit', 'add')))
		{
			$response->status = 'error';
			$response->msg    = Text::_('COM_COMPANIES_ERROR_ALIAS_EXIST');
			$response->data   = $default_alias;

			return $response;
		}

		// Check idXXX
		preg_match('/^id(.*)/', $alias, $matches);
		$idFromAlias = (!empty($matches[1])) ? $matches[1] : false;
		if ($idFromAlias && $id != $idFromAlias)
		{
			$response->status = 'error';
			$response->msg    = Text::_('COM_COMPANIES_ERROR_ALIAS_ID');
			$response->data   = $default_alias;

			return $response;
		}

		// Check numeric
		if (is_numeric($alias))
		{
			$response->status = 'error';
			$response->msg    = Text::_('COM_COMPANIES_ERROR_ALIAS_NUMBER');
			$response->data   = $default_alias;

			return $response;
		}

		// Check slug
		if (Factory::getConfig()->get('unicodeslugs') == 1)
		{
			$slug = OutputFilter::stringURLUnicodeSlug($alias);
		}
		else
		{
			$slug = OutputFilter::stringURLSafe($alias);
		}

		if ($alias != $slug)
		{
			$response->msg  = Text::_('COM_COMPANIES_ERROR_ALIAS_SLUG');
			$response->data = $slug;

			$alias = $slug;

		}

		// Check count
		if (mb_strlen($alias) < 5)
		{
			$response->status = 'error';
			$response->msg    = Text::_('COM_COMPANIES_ERROR_ALIAS_LENGTH');
			$response->data   = $default_alias;

			return $response;
		}

		$table = $this->getTable();
		$table->load(array('alias' => $alias));
		if (!empty($table->id) && ($table->id != $id))
		{
			$response->status = 'error';
			$response->msg    = Text::_('COM_COMPANIES_ERROR_ALIAS_EXIST');
			$response->data   = $default_alias;

			return $response;
		}

		return $response;
	}

	/**
	 * Method to set in_work to one or more records.
	 *
	 * @param   array &$pks An array of record primary keys.
	 *
	 * @return true
	 *
	 * @since 1.0.0
	 */
	public function toWork($pks = array())
	{
		try
		{
			$db = $this->getDbo();
			foreach ($pks as $pk)
			{
				$update          = new stdClass();
				$update->id      = $pk;
				$update->in_work = 1;

				$db->updateObject('#__companies', $update, 'id');
			}
		}
		catch (Exception $e)
		{
			$this->setError($e);

			return false;
		}

		return true;
	}
}