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

class Q2A_Admin_Recalc_RecalcCategories_StepPostsUpdate extends Q2A_Admin_Recalc_AbstractStep
{
	const BATCH_AMOUNT = 100;

	public function __construct()
	{
		$this->messageLangId = 'admin/recalc_categories_updated';
	}

	public function setup()
	{
		require_once QA_INCLUDE_DIR . 'db/post-create.php';
		require_once QA_INCLUDE_DIR . 'db/admin.php';

		qa_db_acount_update();
		qa_db_ccount_update();

		$this->totalItems = qa_db_count_posts();
	}

	public function execute()
	{
		// For qa_db_posts_get_for_recategorizing()
		require_once QA_INCLUDE_DIR . 'db/recalc.php';

		// For qa_db_posts_calc_category_path()
		require_once QA_INCLUDE_DIR . 'db/post-create.php';

		$postids = qa_db_posts_get_for_recategorizing($this->nextItemId, self::BATCH_AMOUNT);

		if (!empty($postids)) {
			$lastpostid = max($postids);

			qa_db_posts_recalc_categoryid($this->nextItemId, $lastpostid);
			qa_db_posts_calc_category_path($this->nextItemId, $lastpostid);

			$this->nextItemId = $lastpostid + 1;
			$this->processedItems += count($postids);
			$this->totalItems = max($this->totalItems, $this->processedItems);
		}

		if (count($postids) < self::BATCH_AMOUNT) {
			$this->isFinished = true;
		}
	}
}
