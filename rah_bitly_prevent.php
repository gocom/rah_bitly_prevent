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

class rah_bitly_prevent {

	protected $ignore_sections = array();

	/**
	 * Constructor
	 */
	
	public function __construct() {
		
		if(defined('rah_bitly__prevent_sections')) {
			$this->ignore_sections = do_list(rah_bitly__prevent_sections);
		}
		
		register_callback(array($this, 'filter'), 'rah_bitly.update');
	}
 
 	/**
 	 * Does validation prior to generating a new link
 	 */

	public function filter() {
		if(strpos(ps('Section'), '_') === 0 || in_array(ps('Section'), $this->ignore_sections)) {
			rah_bitly::get()->permlink = false;
		}
	}
}

new rah_bitly_prevent();