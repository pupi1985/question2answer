<?php
/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

	Description: Server-side response to Ajax admin recalculation requests


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

require_once QA_INCLUDE_DIR . 'app/users.php';
require_once QA_INCLUDE_DIR . 'app/recalc.php';

$response = "QA_AJAX_RESPONSE\n1\n";

if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN) {
	if (!qa_check_form_security_code('admin/recalc', qa_post_text('code'))) {
		$response .= qa_lang('misc/form_security_reload');
	} else {
		$process = qa_post_text('process');
		$forceRestart = qa_post_text('forceRestart') === 'true';
		$stoptime = time() + 3;

		do {
			$result = qa_recalc_get_process_manager($process)->execute($forceRestart);
			$stepFinished = $result['step_state']['is_finished'] ?? false;
			$processedItems = $result['step_state']['processed_items'] ?? 0;
			$shouldShowProgress = $result['process_finished'] || $stepFinished || $processedItems === 0;
		} while (!$shouldShowProgress && time() < $stoptime);

		// Remove reminder to run pending processes
		if ($result['process_finished']) {
			$pendingRecalcs = json_decode(qa_opt('recalc_pending_processes'), true) ?? [];
			$processKey = array_search($process, $pendingRecalcs);
			if ($processKey !== false) {
				unset($pendingRecalcs[$processKey]);
				qa_opt('recalc_pending_processes', json_encode($pendingRecalcs));
			}
		}

		$response .= $result['message'] . "\n";
		$response .= $process . "\n";
		$response .= (int)$result['process_finished'];
	}
} else {
	$response .= qa_lang('admin/no_privileges');
}

echo $response;
