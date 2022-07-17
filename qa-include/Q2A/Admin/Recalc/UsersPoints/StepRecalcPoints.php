<?php

/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

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

class Q2A_Admin_Recalc_UsersPoints_StepRecalcPoints extends Q2A_Admin_Recalc_AbstractStep
{
	const BATCH_AMOUNT = 10;

	public function __construct()
	{
		$this->messageLangId = 'admin/recalc_users_points_recalced';
	}

	public function setup()
	{
		require_once QA_INCLUDE_DIR . 'db/admin.php';

		$this->totalItems = qa_opt('cache_userpointscount');

		$this->lastProcessedItemId = QA_FINAL_EXTERNAL_USERS ? '' : 0;
	}

	public function execute()
	{
		require_once QA_INCLUDE_DIR . 'db/recalc.php';

		$userids = qa_db_users_get_for_recalc_points($this->lastProcessedItemId, self::BATCH_AMOUNT);

		if (!empty($userids)) {
			$firstUserId = reset($userids);
			$this->lastProcessedItemId = end($userids);

			qa_db_users_recalc_points($firstUserId, $this->lastProcessedItemId);

			$this->processedItems += count($userids);
			$this->totalItems = max($this->totalItems, $this->processedItems);
		}

		if (count($userids) < self::BATCH_AMOUNT) {
			require_once QA_INCLUDE_DIR . 'db/points.php';

			qa_db_truncate_userpoints($this->lastProcessedItemId);
			qa_db_userpointscount_update(); // quick so just do it here

			$this->isFinished = true;
		}
	}
}
