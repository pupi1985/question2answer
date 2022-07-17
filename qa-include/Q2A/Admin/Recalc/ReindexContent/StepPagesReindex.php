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

class Q2A_Admin_Recalc_ReindexContent_StepPagesReindex extends Q2A_Admin_Recalc_AbstractStep
{
	const BATCH_AMOUNT = 10;

	public function __construct()
	{
		$this->messageLangId = 'admin/reindex_pages_reindexed';
	}

	public function setup()
	{
		require_once QA_INCLUDE_DIR . 'db/recalc.php';

		$this->totalItems = qa_db_count_pages();
	}

	public function execute()
	{
		require_once QA_INCLUDE_DIR . 'db/recalc.php';

		$pages = qa_db_pages_get_for_reindexing($this->nextItemId, self::BATCH_AMOUNT);

		if (!empty($pages)) {
			require_once QA_INCLUDE_DIR . 'app/format.php';

			$lastpageid = max(array_keys($pages));

			foreach ($pages as $pageid => $page) {
				if (!($page['flags'] & QA_PAGE_FLAGS_EXTERNAL)) {
					$searchmodules = qa_load_modules_with('search', 'unindex_page');
					foreach ($searchmodules as $searchmodule) {
						$searchmodule->unindex_page($pageid);
					}

					$searchmodules = qa_load_modules_with('search', 'index_page');
					if (count($searchmodules)) {
						$indextext = qa_viewer_text($page['content'], 'html');

						foreach ($searchmodules as $searchmodule) {
							$searchmodule->index_page($pageid, $page['tags'], $page['heading'], $page['content'], 'html', $indextext);
						}
					}
				}
			}

			$this->nextItemId = $lastpageid + 1;
			$this->processedItems += count($pages);
			$this->totalItems = max($this->totalItems, $this->processedItems);
		}

		if (count($pages) < self::BATCH_AMOUNT) {
			$this->isFinished = true;
		}
	}
}
