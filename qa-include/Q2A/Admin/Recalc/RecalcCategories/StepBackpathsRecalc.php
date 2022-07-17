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

class Q2A_Admin_Recalc_RecalcCategories_StepBackpathsRecalc extends Q2A_Admin_Recalc_AbstractStep
{
	const BATCH_AMOUNT = 10;

	public function __construct()
	{
		$this->messageLangId = 'admin/recalc_categories_backpaths';
	}

	public function setup()
	{
		require_once QA_INCLUDE_DIR . 'db/admin.php';

		$this->totalItems = qa_db_count_categories();
	}

	public function execute()
	{
		// For qa_db_categories_get_for_recalcs()
		require_once QA_INCLUDE_DIR . 'db/recalc.php';

		// For qa_db_categories_recalc_backpaths()
		require_once QA_INCLUDE_DIR . 'db/admin.php';

		$categoryids = qa_db_categories_get_for_recalcs($this->nextItemId, self::BATCH_AMOUNT);

		if (!empty($categoryids)) {
			$lastcategoryid = max($categoryids);

			qa_db_categories_recalc_backpaths($this->nextItemId, $lastcategoryid);

			$this->nextItemId = $lastcategoryid + 1;
			$this->processedItems += count($categoryids);
			$this->totalItems = max($this->totalItems, $this->processedItems);
		}

		if (count($categoryids) < self::BATCH_AMOUNT) {
			$this->isFinished = true;
		}
	}
}
