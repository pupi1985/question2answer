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

abstract class Q2A_Admin_Recalc_DeleteHiddenPosts_AbstractStepDeleteHiddenPosts extends Q2A_Admin_Recalc_AbstractStep
{
	const BATCH_AMOUNT = 1;

	protected abstract function getPostType();

	public function setup()
	{
		require_once QA_INCLUDE_DIR . 'db/recalc.php';

		$postType = $this->getPostType();
		$this->totalItems = qa_db_posts_count_for_deleting($postType);
	}

	public function execute()
	{
		require_once QA_INCLUDE_DIR . 'db/recalc.php';

		$postType = $this->getPostType();

		$postids = qa_db_posts_get_for_deleting($postType, $this->nextItemId, self::BATCH_AMOUNT);

		if (!empty($postids)) {
			require_once QA_INCLUDE_DIR . 'app/posts.php';

			$lastpostid = max($postids);

			foreach ($postids as $postid) {
				qa_post_delete($postid);
			}

			$this->nextItemId = $lastpostid + 1;
			$this->processedItems += count($postids);
			$this->totalItems = max($this->totalItems, $this->processedItems);
		}

		if (count($postids) < self::BATCH_AMOUNT) {
			$this->isFinished = true;
		}
	}
}
