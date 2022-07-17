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

class Q2A_Admin_Recalc_ReindexContent_StepWordsCount extends Q2A_Admin_Recalc_AbstractStep
{
	const BATCH_AMOUNT = 1000;

	public function __construct()
	{
		$this->messageLangId = 'admin/reindex_posts_wordcounted';
	}

	public function setup()
	{
		require_once QA_INCLUDE_DIR . 'db/recalc.php';

		$this->totalItems = qa_db_count_words();
	}

	public function execute()
	{
		require_once QA_INCLUDE_DIR . 'db/recalc.php';
		require_once QA_INCLUDE_DIR . 'db/post-create.php';

		$wordids = qa_db_words_prepare_for_recounting($this->nextItemId, self::BATCH_AMOUNT);

		if (!empty($wordids)) {
			$lastpostid = max($wordids);

			qa_db_words_recount($this->nextItemId, $lastpostid);

			$this->nextItemId = $lastpostid + 1;
			$this->processedItems += count($wordids);
			$this->totalItems = max($this->totalItems, $this->processedItems);
		}

		if (count($wordids) < self::BATCH_AMOUNT) {
			require_once QA_INCLUDE_DIR . 'db/recalc.php';

			qa_db_tagcount_update(); // this is quick so just do it here

			$this->isFinished = true;
		}
	}
}
