<?php
/**
 * @package    Profiles Component
 * @version    1.0.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

class com_CompaniesInstallerScript
{
	/**
	 * Runs right after any installation action is preformed on the component.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	function postflight()
	{
		$path = '/components/com_companies';

		$this->fixTables($path);
		$this->tagsIntegration();
		$this->createImageFolder();
		$this->moveLayouts($path);
		$this->createSecret();

		return true;
	}

	/**
	 * Create or image folders
	 *
	 * @since 1.0.0
	 */
	protected function createImageFolder()
	{
		$folder = JPATH_ROOT . '/images/companies';
		if (!JFolder::exists($folder))
		{
			JFolder::create($folder);
			JFile::write($folder . '/index.html', '<!DOCTYPE html><title></title>');
		}
	}

	/**
	 * Create or update tags integration
	 *
	 * @since 1.0.0
	 */
	protected function tagsIntegration()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('type_id')
			->from($db->quoteName('#__content_types'))
			->where($db->quoteName('type_alias') . ' = ' . $db->quote('com_companies.company'));
		$db->setQuery($query);
		$current_id = $db->loadResult();

		$company                                               = new stdClass();
		$company->type_id                                      = (!empty($current_id)) ? $current_id : '';
		$company->type_title                                   = 'Companies Company';
		$company->type_alias                                   = 'com_companies.company';
		$company->table                                        = new stdClass();
		$company->table->special                               = new stdClass();
		$company->table->special->dbtable                      = '#__companies';
		$company->table->special->key                          = 'id';
		$company->table->special->type                         = 'Companies';
		$company->table->special->prefix                       = 'CompaniesTable';
		$company->table->special->config                       = 'array()';
		$company->table->common                                = new stdClass();
		$company->table->common->dbtable                       = '#__ucm_content';
		$company->table->common->key                           = 'ucm_id';
		$company->table->common->type                          = 'Corecontent';
		$company->table->common->prefix                        = 'JTable';
		$company->table->common->config                        = 'array()';
		$company->table                                        = json_encode($company->table);
		$company->rules                                        = '';
		$company->field_mappings                               = new stdClass();
		$company->field_mappings->common                       = new stdClass();
		$company->field_mappings->common->core_content_item_id = 'id';
		$company->field_mappings->common->core_title           = 'name';
		$company->field_mappings->common->core_state           = 'state';
		$company->field_mappings->common->core_alias           = 'alias';
		$company->field_mappings->common->core_created_time    = 'created';
		$company->field_mappings->common->core_modified_time   = 'modified';
		$company->field_mappings->common->core_body            = 'about';
		$company->field_mappings->common->core_hits            = 'hits';
		$company->field_mappings->common->core_publish_up      = 'created';
		$company->field_mappings->common->core_publish_down    = 'null';
		$company->field_mappings->common->core_access          = 'access';
		$company->field_mappings->common->core_params          = 'null';
		$company->field_mappings->common->core_featured        = 'null';
		$company->field_mappings->common->core_metadata        = 'metadata';
		$company->field_mappings->common->core_language        = 'null';
		$company->field_mappings->common->core_images          = 'logo';
		$company->field_mappings->common->core_urls            = 'null';
		$company->field_mappings->common->core_version         = 'null';
		$company->field_mappings->common->core_ordering        = 'created';
		$company->field_mappings->common->core_metakey         = 'metakey';
		$company->field_mappings->common->core_metadesc        = 'metadesc';
		$company->field_mappings->common->core_catid           = 'null';
		$company->field_mappings->common->core_xreference      = 'null';
		$company->field_mappings->common->asset_id             = 'null';
		$company->field_mappings->special                      = new stdClass();
		$company->field_mappings->special->contacts            = 'contacts';
		$company->field_mappings->special->region              = 'region';
		$company->field_mappings                               = json_encode($company->field_mappings);
		$company->router                                       = 'CompaniesHelperRoute::getCompanyRoute';
		$company->content_history_options                      = '';

		(!empty($current_id)) ? $db->updateObject('#__content_types', $company, 'type_id')
			: $db->insertObject('#__content_types', $company);
	}

	/**
	 * Move layouts folder
	 *
	 * @param string $path path to files
	 *
	 * @since 1.0.0
	 */
	protected function moveLayouts($path)
	{
		$component = JPATH_ADMINISTRATOR . $path . '/layouts';
		$layouts   = JPATH_ROOT . '/layouts' . $path;
		if (!JFolder::exists(JPATH_ROOT . '/layouts/components'))
		{
			JFolder::create(JPATH_ROOT . '/layouts/components');
		}
		if (JFolder::exists($layouts))
		{
			JFolder::delete($layouts);
		}
		JFolder::move($component, $layouts);
	}

	/**
	 *
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance $adapter The object responsible for running this script
	 *
	 * @since 1.0.0
	 */
	public function uninstall(JAdapterInstance $adapter)
	{
		// Remove content_type
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__content_types'))
			->where($db->quoteName('type_alias') . ' = ' . $db->quote('com_companies.company'));
		$db->setQuery($query)->execute();

		// Remove tag_map
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__contentitem_tag_map'))
			->where($db->quoteName('type_alias') . ' = ' . $db->quote('com_companies.company'));
		$db->setQuery($query)->execute();

		// Remove ucm_content
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ucm_content'))
			->where($db->quoteName('core_type_alias') . ' = ' . $db->quote('com_companies.company'));
		$db->setQuery($query)->execute();

		// Remove images
		JFolder::delete(JPATH_ROOT . '/images/companies');

		// Remove layouts
		JFolder::delete(JPATH_ROOT . '/layouts/components/com_companies');
	}

	/**
	 * Method to fix tables
	 *
	 * @param string $path path to component directory
	 *
	 * @since 1.0.0
	 */
	protected function fixTables($path)
	{
		$file = JPATH_ADMINISTRATOR . $path . '/sql/install.mysql.utf8.sql';
		if (!empty($file))
		{
			$sql = JFile::read($file);

			if (!empty($sql))
			{
				$db      = Factory::getDbo();
				$queries = $db->splitSql($sql);
				foreach ($queries as $query)
				{
					$db->setQuery($db->convertUtf8mb4QueryToUtf8($query));
					try
					{
						$db->execute();
					}
					catch (JDataBaseExceptionExecuting $e)
					{
						JLog::add(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $e->getMessage()),
							JLog::WARNING, 'jerror');
					}
				}
			}
		}
	}

	/**
	 * Method to create secret key
	 *
	 * @since 1.0.0
	 */
	function createSecret()
	{
		$component = ComponentHelper::getComponent('com_companies');
		$params    = $component->getParams();
		if (empty($params->get('secret')))
		{
			$secret = '';
			$array  = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's',
				't', 'u', 'v', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
				'P', 'R', 'S', 'T', 'U', 'V', 'X', 'Y', 'Z');
			for ($i = 0; $i < 15; $i++)
			{
				$key    = rand(0, count($array) - 1);
				$secret .= $array[$key];
			}
			$params->set('secret', $secret);

			$object               = new stdClass();
			$object->extension_id = $component->id;
			$object->params       = (string) $params;
			Factory::getDbo()->updateObject('#__extensions', $object, 'extension_id');
		}
	}
}