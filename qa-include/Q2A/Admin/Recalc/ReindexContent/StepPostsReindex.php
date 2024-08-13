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

class Q2A_Admin_Recalc_ReindexContent_StepPostsReindex extends Q2A_Admin_Recalc_AbstractStep
{
	const BATCH_AMOUNT = 1000;

	public function __construct()
	{
		$this->messageLangId = 'admin/reindex_posts_reindexed';
	}

	public function setup()
	{
		require_once QA_INCLUDE_DIR . 'db/recalc.php';

		$this->totalItems = qa_db_posts_count_for_reindexing();
	}

	public function execute()
	{
		require_once QA_INCLUDE_DIR . 'db/recalc.php';

		$posts = qa_db_posts_get_for_reindexing($this->nextItemId, self::BATCH_AMOUNT);

		if (!empty($posts)) {

			require_once QA_INCLUDE_DIR . 'app/post-update.php';
			require_once QA_INCLUDE_DIR . 'app/post-update.php';
			require_once QA_INCLUDE_DIR . 'app/format.php';

			$lastpostid = max(array_keys($posts));

			qa_db_prepare_for_reindexing($this->nextItemId, $lastpostid);
			qa_suspend_update_counts();

			foreach ($posts as $postid => $post) {
				qa_post_unindex($postid);
				qa_post_index($postid, $post['type'], $post['questionid'], $post['parentid'], $post['title'], $post['content'],
					$post['format'], qa_viewer_text($post['content'], $post['format']), $post['tags'], $post['categoryid']);
			}

			$this->nextItemId = $lastpostid + 1;
			$this->processedItems += count($posts);
			$this->totalItems = max($this->totalItems, $this->processedItems);
		}

		if (count($posts) < self::BATCH_AMOUNT) {

			qa_db_truncate_indexes($this->nextItemId);

			$this->isFinished = true;
		}
	}
}
