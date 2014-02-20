<?php

/*
	Question2Answer (c) Gideon Greenspan

	http://www.question2answer.org/


	File: qa-plugin/tag-cloud-widget/qa-plugin.php
	Version: See define()s at top of qa-include/qa-base.php
	Description: Initiates tag cloud widget plugin


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
*/

/*
	[metadata]
	Plugin Name [name]: Tag Cloud Widget
	Plugin URI [uri]: 
	Plugin Description [description]: Provides a list of tags with size indicating popularity
	Plugin Version [version]: 1.0.1
	Plugin Date [date]: 2011-12-06
	Plugin Author [author]: Question2Answer
	Plugin Author URI [author_uri]: http://www.question2answer.org/
	Plugin License [license]: GPLv2
	Plugin Minimum Question2Answer Version [q2a_version]: 1.4
	Plugin Update Check URI [update_uri]: 
	[/metadata]
*/


	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}


	qa_register_plugin_module('widget', 'qa-tag-cloud.php', 'qa_tag_cloud', 'Tag Cloud');


/*
	Omit PHP closing tag to help avoid accidental output
*/