<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Filter by relationships
 *
 * @package        low_search
 * @author         Lodewijk Schutte ~ Low <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-search
 * @copyright      Copyright (c) 2014, Low
 */
class Low_search_filter_relationships extends Low_search_filter {

	/**
	 * Search parameters for (parents|children):field params and return set of ids that match it
	 *
	 * @access      public
	 * @return      void
	 */
	public function filter($entry_ids)
	{
		// --------------------------------------
		// Check prefixed parameters needed
		// --------------------------------------

		$rels = array_filter(array_merge(
			$this->params->get_prefixed('parent:'),
			$this->params->get_prefixed('child:')
		));

		// --------------------------------------
		// Don't do anything if nothing's there
		// --------------------------------------

		if (empty($rels)) return $entry_ids;

		// --------------------------------------
		// Log it
		// --------------------------------------

		$this->_log('Applying '.__CLASS__);

		// --------------------------------------
		// Loop through relationships
		// --------------------------------------

		foreach ($rels AS $key => $val)
		{
			// List out match
			list($type, $field) = explode(':', $key, 2);

			// Get the field id, skip if non-existent
			if ( ! ($field_id = $this->_get_field_id($field))) continue;

			// Prep the value
			$val = $this->params->prep($key, $val);

			// Get the parameter
			list($ids, $in) = low_explode_param($val);

			// Match all?
			$all = (bool) strpos($val, '&');

			// Init vars
			$rel_ids = $table = $playa = FALSE;
			$parent = ($type == 'parent');

			// Native relationship field
			if ($this->_is_rel_field($field))
			{
				// Account for new EE rels
				$prefix = (version_compare(APP_VER, '2.6.0', '<')) ? 'rel_' : '';

				// Set the table & attributes
				$table  = 'relationships';
				$select = $parent ? $prefix.'child_id' : $prefix.'parent_id';
				$where  = $parent ? $prefix.'parent_id' : $prefix.'child_id';
			}
			elseif ($this->_is_playa_field($field_id))
			{
				// Set the table
				$table  = 'playa_relationships';
				$select = $parent ? 'child_entry_id' : 'parent_entry_id';
				$where  = $parent ? 'parent_entry_id' : 'child_entry_id';
			}

			// Execute query
			if ($table)
			{
				// Check if $ids are numeric
				if ( ! low_array_is_numeric($ids))
				{
					// Log it!
					$this->_log("Getting entry IDs for given relationship url_titles (field {$field_id})");

					// Translate url_titles to IDs based on field
					$ids = $this->_get_entry_ids($ids, $field_id, $parent);

					if (empty($ids))
					{
						$this->_log('No valid entry IDs found');
						return array();
					}
				}

				// Start query
				ee()->db->select($select.' AS entry_id')
				        ->from($table)
				        ->{$in ? 'where_in' : 'where_not_in'}($where, $ids);

				// Limit by already existing ids
				if ($entry_ids)
				{
					ee()->db->where_in($select, $entry_ids);
				}

				// Focus on specific playa field
				if ($playa)
				{
					ee()->db->where('parent_field_id', $field_id);
				}

				// Do the having-trick to account for *all* given entry ids
				if ($in && $all)
				{
					ee()->db->select('COUNT(*) AS num')
					        ->group_by($select)
					        ->having('num', count($ids));
				}

				// Execute query
				$query = ee()->db->get();

				// And get the entry ids
				$entry_ids = low_flatten_results($query->result_array(), 'entry_id');
				$entry_ids = array_unique($entry_ids);

				// Bail out if there aren't any matches
				if (is_array($entry_ids) && empty($entry_ids)) break;
			}
		}

		return $entry_ids;
	}

	/**
	 * Check whether given string is a relationship field
	 */
	private function _is_rel_field($str)
	{
		$it = FALSE;

		if ($fields = low_get_cache('channel', 'relationship_fields'))
		{
			$it = (bool) $this->_get_field_id($str, $fields);
		}

		return $it;
	}

	/**
	 * Check whether given field id is a playa field
	 */
	private function _is_playa_field($id)
	{
		$it = FALSE;

		if ($fields = low_get_cache('channel', 'pair_custom_fields'))
		{
			$it = (strpos($this->_get_field_id($id, $fields), 'playa') !== FALSE);
		}

		return $it;
	}

	/**
	 * Get entry ids based on given url_titles
	 */
	private function _get_entry_ids($ids, $field_id, $parent)
	{
		static $cache = array();

		if ($parent)
		{
			// Get the channels that have this field assigned to it
			if ( ! isset($cache[$field_id]))
			{
				$query = ee()->db->select('c.channel_id')
				       ->from('channels c')
				       ->join('channel_fields cf', 'c.field_group = cf.group_id')
				       ->where('cf.field_id', $field_id)
				       ->get();

				$cache[$field_id] = low_flatten_results($query->result_array(), 'channel_id');
			}

			$channel_ids = $cache[$field_id];
		}
		else
		{
			// Get the channel IDs assigned to this relationship/playa field
			$settings = ee()->api_channel_fields->get_settings($field_id);
			$channel_ids = $settings['channels'];
		}

		// Price to pay for using url_titles instead of IDs:
		// At least 1 query per parameter. At least it's fairly quick.
		$query = ee()->db->select('entry_id')
		       ->from('channel_titles')
		       ->where_in('channel_id', $channel_ids)
		       ->where_in('url_title', $ids)
		       ->get();

		return low_flatten_results($query->result_array(), 'entry_id');
	}

	/**
	 * Results: remove rogue {low_search_parent/child:...} vars
	 */
	public function results($query)
	{
		$this->_remove_rogue_vars(array('parent:', 'child:'));
		return $query;
	}

}