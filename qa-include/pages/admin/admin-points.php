<?php
/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

	Description: Controller for admin page for settings about user points


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

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../../');
	exit;
}

require_once QA_INCLUDE_DIR . 'db/recalc.php';
require_once QA_INCLUDE_DIR . 'db/points.php';
require_once QA_INCLUDE_DIR . 'app/options.php';
require_once QA_INCLUDE_DIR . 'app/admin.php';
require_once QA_INCLUDE_DIR . 'util/sort.php';


// Check admin privileges

if (!qa_admin_check_privileges($qa_content)) {
	return $qa_content;
}


// Process user actions

$pendingRecalcs = json_decode(qa_opt('recalc_pending_processes'), true) ?? [];
$recalculate = in_array('users_points', $pendingRecalcs);

$securityexpired = false;
$optionnames = qa_db_points_option_names();

if (qa_clicked('doshowdefaults')) {
	$options = array();

	foreach ($optionnames as $optionname) {
		$options[$optionname] = qa_default_option($optionname);
	}
} else {
	if (qa_clicked('dosave')) {
		if (!qa_check_form_security_code('admin/points', qa_post_text('code'))) {
			$securityexpired = true;
		} else {
			foreach ($optionnames as $optionname) {
				qa_set_option($optionname, (int)qa_post_text('option_' . $optionname));
			}

			if (!$recalculate) {
				$recalculate = true;
				$pendingRecalcs[] = 'users_points';
				qa_opt('recalc_pending_processes', json_encode($pendingRecalcs));
			}
		}
	}

	$options = qa_get_options($optionnames);
}


// Prepare content for theme

$qa_content = qa_content_prepare();

$qa_content['title'] = qa_lang_html('admin/admin_title') . ' - ' . qa_lang_html('admin/points_title');
$qa_content['error'] = $securityexpired ? qa_lang_html('admin/form_security_expired') : qa_admin_page_error();

$qa_content['form'] = array(
	'tags' => 'method="post" action="' . qa_self_html() . '" name="points_form" onsubmit="document.forms.points_form.has_js.value=1; return true;"',

	'style' => 'wide',

	'buttons' => array(
		'save' => array(
			'label' => qa_lang_html('admin/save_options_button'),
		),
	),

	'hidden' => array(
		'dosave' => '1',
		'has_js' => '0',
		'code' => qa_get_form_security_code('admin/points'),
	),
);


if (qa_clicked('doshowdefaults')) {
	$qa_content['form']['ok'] = qa_lang_html('admin/points_defaults_shown');

	$qa_content['form']['buttons']['cancel'] = array(
		'tags' => 'name="docancel"',
		'label' => qa_lang_html('main/cancel_button'),
	);
} else {
	if ($recalculate) {
		$qa_content['error'] = strtr(qa_lang_html('admin/recalc_needed'), [
			'^1' => sprintf('<a href="%s">', qa_path_html('admin/stats', null, null, null, 'form_users_points')),
			'^2' => qa_lang_html('admin/recalc_users_points_title'),
			'^3' => '</a>',
		]);
	}

	$qa_content['form']['buttons']['showdefaults'] = array(
		'tags' => 'name="doshowdefaults"',
		'label' => qa_lang_html('admin/show_defaults_button'),
	);
}


foreach ($optionnames as $optionname) {
	$optionfield = array(
		'label' => qa_lang_html('options/' . $optionname),
		'tags' => 'name="option_' . $optionname . '"',
		'value' => qa_html($options[$optionname]),
		'type' => 'number',
		'note' => qa_lang_html('admin/points'),
	);

	switch ($optionname) {
		case 'points_multiple':
			$prefix = '&#215;';
			unset($optionfield['note']);
			break;

		case 'points_per_q_voted_up':
		case 'points_per_a_voted_up':
		case 'points_per_c_voted_up':
		case 'points_q_voted_max_gain':
		case 'points_a_voted_max_gain':
		case 'points_c_voted_max_gain':
			$prefix = '+';
			break;

		case 'points_per_q_voted_down':
		case 'points_per_a_voted_down':
		case 'points_per_c_voted_down':
		case 'points_q_voted_max_loss':
		case 'points_a_voted_max_loss':
		case 'points_c_voted_max_loss':
			$prefix = '&ndash;';
			break;

		case 'points_base':
			$prefix = '+';
			break;

		default:
			$prefix = '<span style="visibility:hidden;">+</span>'; // for even alignment
			break;
	}

	$optionfield['prefix'] = '<span style="width:1em; display:inline-block; display:-moz-inline-stack;">' . $prefix . '</span>';

	$qa_content['form']['fields'][$optionname] = $optionfield;
}

qa_array_insert($qa_content['form']['fields'], 'points_post_a', array('blank0' => array('type' => 'blank')));
qa_array_insert($qa_content['form']['fields'], 'points_per_c_voted_up', array('blank1' => array('type' => 'blank')));
qa_array_insert($qa_content['form']['fields'], 'points_vote_up_q', array('blank2' => array('type' => 'blank')));
qa_array_insert($qa_content['form']['fields'], 'points_multiple', array('blank3' => array('type' => 'blank')));


$qa_content['navigation']['sub'] = qa_admin_sub_navigation();


return $qa_content;
