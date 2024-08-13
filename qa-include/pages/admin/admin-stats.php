<?php
/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

	Description: Controller for admin page showing usage statistics and clean-up buttons


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
require_once QA_INCLUDE_DIR . 'app/admin.php';
require_once QA_INCLUDE_DIR . 'db/admin.php';
require_once QA_INCLUDE_DIR . 'app/format.php';

// Check admin privileges (do late to allow one DB query)

if (!qa_admin_check_privileges($qa_content)) {
	return $qa_content;
}

// Get the information to display

$qcount = (int)qa_opt('cache_qcount');
$qcount_anon = qa_db_count_posts('Q', false);
$qcount_unans = (int)qa_opt('cache_unaqcount');

$acount = (int)qa_opt('cache_acount');
$acount_anon = qa_db_count_posts('A', false);

$ccount = (int)qa_opt('cache_ccount');
$ccount_anon = qa_db_count_posts('C', false);

// Prepare content for theme

$qa_content = qa_content_prepare();

$qa_content['title'] = qa_lang_html('admin/admin_title') . ' - ' . qa_lang_html('admin/stats_title');

$qa_content['error'] = qa_admin_page_error();

$qa_content['form'] = array(
	'style' => 'wide',

	'fields' => array(
		'q2a_version' => array(
			'label' => qa_lang_html('admin/q2a_version'),
			'value' => qa_html(QA_VERSION),
		),

		'q2a_date' => array(
			'label' => qa_lang_html('admin/q2a_build_date'),
			'value' => qa_html(QA_BUILD_DATE),
		),

		'q2a_latest' => array(
			'label' => qa_lang_html('admin/q2a_latest_version'),
			'type' => 'custom',
			'html' => '<span id="q2a-version">...</span>',
		),

		'break0' => array(
			'type' => 'blank',
		),

		'db_version' => array(
			'label' => qa_lang_html('admin/q2a_db_version'),
			'value' => qa_html(qa_opt('db_version')),
		),

		'db_size' => array(
			'label' => qa_lang_html('admin/q2a_db_size'),
			'value' => qa_html(qa_format_number(qa_db_table_size() / 1048576, 1) . ' MB'),
		),

		'break1' => array(
			'type' => 'blank',
		),

		'php_version' => array(
			'label' => qa_lang_html('admin/php_version'),
			'value' => qa_html(phpversion()),
		),

		'mysql_version' => array(
			'label' => qa_lang_html('admin/mysql_version'),
			'value' => qa_html(qa_db_mysql_version()),
		),

		'break2' => array(
			'type' => 'blank',
		),

		'qcount' => array(
			'label' => qa_lang_html('admin/total_qs'),
			'value' => qa_html(qa_format_number($qcount)),
		),

		'qcount_unans' => array(
			'label' => qa_lang_html('admin/total_qs_unans'),
			'value' => qa_html(qa_format_number($qcount_unans)),
		),

		'qcount_users' => array(
			'label' => qa_lang_html('admin/from_users'),
			'value' => qa_html(qa_format_number($qcount - $qcount_anon)),
		),

		'qcount_anon' => array(
			'label' => qa_lang_html('admin/from_anon'),
			'value' => qa_html(qa_format_number($qcount_anon)),
		),

		'break3' => array(
			'type' => 'blank',
		),

		'acount' => array(
			'label' => qa_lang_html('admin/total_as'),
			'value' => qa_html(qa_format_number($acount)),
		),

		'acount_users' => array(
			'label' => qa_lang_html('admin/from_users'),
			'value' => qa_html(qa_format_number($acount - $acount_anon)),
		),

		'acount_anon' => array(
			'label' => qa_lang_html('admin/from_anon'),
			'value' => qa_html(qa_format_number($acount_anon)),
		),

		'break4' => array(
			'type' => 'blank',
		),

		'ccount' => array(
			'label' => qa_lang_html('admin/total_cs'),
			'value' => qa_html(qa_format_number($ccount)),
		),

		'ccount_users' => array(
			'label' => qa_lang_html('admin/from_users'),
			'value' => qa_html(qa_format_number($ccount - $ccount_anon)),
		),

		'ccount_anon' => array(
			'label' => qa_lang_html('admin/from_anon'),
			'value' => qa_html(qa_format_number($ccount_anon)),
		),

		'break5' => array(
			'type' => 'blank',
		),

		'users' => array(
			'label' => qa_lang_html('admin/users_registered'),
			'value' => QA_FINAL_EXTERNAL_USERS ? '' : qa_html(qa_format_number(qa_db_count_users())),
		),

		'users_active' => array(
			'label' => qa_lang_html('admin/users_active'),
			'value' => qa_html(qa_format_number((int)qa_opt('cache_userpointscount'))),
		),

		'users_posted' => array(
			'label' => qa_lang_html('admin/users_posted'),
			'value' => qa_html(qa_format_number(qa_db_count_active_users('posts'))),
		),

		'users_voted' => array(
			'label' => qa_lang_html('admin/users_voted'),
			'value' => qa_html(qa_format_number(qa_db_count_active_users('uservotes'))),
		),
	),
);

if (QA_FINAL_EXTERNAL_USERS) {
	unset($qa_content['form']['fields']['users']);
} else {
	unset($qa_content['form']['fields']['users_active']);
}

foreach ($qa_content['form']['fields'] as $index => $field) {
	if (empty($field['type'])) {
		$qa_content['form']['fields'][$index]['type'] = 'static';
	}
}

$qa_lang_keys = ['please_wait', 'process_start', 'process_stop', 'process_restart', 'process_unfinished'];

$qa_langs = [];
foreach ($qa_lang_keys as $key) {
	$qa_langs[$key] = qa_lang('admin/' . $key);
}

$allProcessesKeys = [
	'recount_posts',
	'reindex_content',
	'users_points',
	'refill_events',
	'delete_hidden_posts',
	'recalc_categories',
];

if (qa_using_categories()) {
	$allProcessesKeys[] = 'recalc_categories';
}

if (defined('QA_BLOBS_DIRECTORY')) {
	if (qa_db_has_blobs_in_db()) {
		$allProcessesKeys[] = 'blobs_to_disk';
	}

	if (qa_db_has_blobs_on_disk()) {
		$allProcessesKeys[] = 'blobs_to_db';
	}
}

$allProcesses = [];
foreach ($allProcessesKeys as $processKey) {
	// One of: qa_recalc_recount_posts_state, qa_recalc_reindex_content_state, qa_recalc_users_points_state,
	// qa_recalc_refill_events_state, qa_recalc_recalc_categories_state, qa_recalc_delete_hidden_posts_state
	$stateOption = 'qa_recalc_' . $processKey . '_state';
	$allProcesses[$processKey] = [
		'serverProcessPending' => !empty(qa_opt($stateOption)),
	];

	$qa_content['form_' . $processKey] = [
		'tags' => sprintf('method="post" action="%s", id="form_%s"', qa_path_html("admin/recalc"), $processKey),

		'style' => 'tall',

		'fields' => [
			'process_title' => [
				'type' => 'static',
				// One of: recalc_recount_posts_title, recalc_reindex_content_title, recalc_users_points_title,
				// recalc_refill_events_title, recalc_recalc_categories_title, recalc_delete_hidden_posts_title
				'value' => qa_lang_html(sprintf('admin/recalc_%s_title', $processKey)),
				// One of: recalc_recount_posts_note, recalc_reindex_content_note, recalc_users_points_note,
				// recalc_refill_events_note, recalc_recalc_categories_note, recalc_delete_hidden_posts_note
				'note' => qa_lang_html(sprintf('admin/recalc_%s_note', $processKey)),
			],
			'status' => [
				'type' => 'custom',
				'html' => sprintf(
					'<span id="%s_status">%s</span>',
					$processKey,
					$allProcesses[$processKey]['serverProcessPending'] ? qa_html($qa_langs['process_unfinished']) : '',
				),
			],
		],

		'buttons' => [
			$processKey . '_restart' => [
				'label' => $allProcesses[$processKey]['serverProcessPending'] ? qa_html($qa_langs['process_restart']) : qa_html($qa_langs['process_start']),
				'tags' => sprintf('id="%s" name="%s" onclick="return qa_recalc_click(this.name, {forceRestart: true})"', $processKey, $processKey),
			],
			$processKey . '_continue' => [
				'label' => qa_lang_html('admin/process_continue'),
				'tags' => sprintf(
					'id="%s_continue" name="%s_continue" data-process="%s" onclick="return qa_recalc_click(this.dataset.process)"%s',
					$processKey,
					$processKey,
					$processKey,
					empty(qa_opt($stateOption)) ? ' style="display: none"' : ''
				),
			],
		],

		'hidden' => [
			'code' => qa_get_form_security_code('admin/recalc'),
		],
	];
}

$qa_content['script_lines'][] = [
	sprintf('const qa_langs = %s;', json_encode($qa_langs)),
	sprintf('const qa_serverProcessesInfo = %s;', json_encode($allProcesses)),
	'window.onbeforeunload = event => {',
	'    for (let [processKey, process] of qa_recalcProcesses.entries()) {',
	'        if (process.clientRunning) {',
	'            event.preventDefault();',
	'            event.returnValue = true;',
	'        }',
	'    }',
	'};',
];

$qa_content['script_onloads'][] = [
	'for (const processKey in qa_serverProcessesInfo) {',
	'    qa_recalcProcesses.set(processKey, {',
	'        "processKey": processKey,',
	'        "serverProcessPending": qa_serverProcessesInfo[processKey]["serverProcessPending"]',
	'    });',
	'}',
];

$qa_content['script_rel'][] = 'qa-content/qa-admin.js?' . QA_VERSION;

$qa_content['script_onloads'][] = array(
	"qa_version_check('https://raw.githubusercontent.com/q2a/question2answer/master/VERSION.txt', " . qa_js(qa_html(QA_VERSION), true) . ", 'q2a-version', true);"
);

$qa_content['navigation']['sub'] = qa_admin_sub_navigation();

return $qa_content;
