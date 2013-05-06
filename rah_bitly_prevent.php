<?php

/**
 * This is an example plugin for rah_bitly showcasing extending.
 *
 * The plugin will prevent generating bitly links, and pinging bitly, when
 * article's section is set to private.
 *
 * @package rah_bitly
 * @author  Jukka Svahn
 * @license GNU GPLv2
 * @link    https://github.com/gocom/rah_bitly_prevent
 *
 * Copyright (C) 2013 Jukka Svahn http://rahforum.biz
 * Licensed under GNU Genral Public License version 2
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

class rah_bitly_prevent
{
	/**
	 * Constructor.
	 */

	public function __construct()
	{
		register_callback(array($this, 'install'), 'plugin_lifecycle.rah_bitly_prevent', 'installed');
		register_callback(array($this, 'uninstall'), 'plugin_lifecycle.rah_bitly_prevent', 'deleted');
		register_callback(array($this, 'filter'), 'rah_bitly.update');
		register_callback(array($this, 'section_ui'), 'section_ui', 'extend_detail_form');
		register_callback(array($this, 'section_save'), 'section', 'section_save');
	}

	/**
	 * Installer.
	 */

	public function install()
	{
		if (!in_array('rah_bitly_prevent_private', getThings('describe '.safe_pfx('txp_section'))))
		{
			safe_alter('txp_section', 'ADD rah_bitly_prevent_private TINYINT(1) NOT NULL DEFAULT 1');
		}
	}

	/**
	 * Uninstaller.
	 */

	public function uninstall()
	{
		if (in_array('rah_bitly_prevent_private', getThings('describe '.safe_pfx('txp_section'))))
		{
			safe_alter('txp_section', 'DROP COLUMN rah_bitly_prevent_private');
		}
	}
 
 	/**
 	 * Does validation prior to generating a new link.
 	 */

	public function filter()
	{
		if (safe_field('rah_bitly_prevent_private', 'txp_section', "name = '".doSlash(ps('Section'))."'"))
		{
			rah_bitly::get()->permlink = false;
		}
	}

	/**
	 * Shows settings at the Sections panel.
	 *
	 * @param  string $event The event
	 * @param  string $step  The step
	 * @param  bool   $void  Not used
	 * @param  array  $r     The section data as an array
	 * @return string HTML
	 */

	public function section_ui($event, $step, $void, $r)
	{
		if ($r['name'] !== 'default')
		{
			return inputLabel('rah_bitly_prevent_private', yesnoradio('rah_bitly_prevent_private', !empty($r['rah_bitly_prevent_private']), '', ''), '', 'rah_bitly_prevent_private');
		}
	}

	/**
	 * Updates a section.
	 */

	public function section_save()
	{
		safe_update(
			'txp_section',
			'rah_bitly_prevent_private = '.intval(ps('rah_bitly_prevent_private')),
			"name = '".doSlash(ps('name'))."'"
		);
	}
}

new rah_bitly_prevent();