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

class Q2A_Admin_Recalc_ReindexContent_StepPostsCount extends Q2A_Admin_Recalc_AbstractStep
{
	public function __construct()
	{
		$this->messageLangId = 'admin/recalc_posts_count';
	}

	public function setup()
	{
		require_once QA_INCLUDE_DIR . 'db/admin.php';

		$this->totalItems = qa_db_count_posts();
	}

	public function execute()
	{
		require_once QA_INCLUDE_DIR . 'db/post-create.php';

		qa_db_qcount_update();
		qa_db_acount_update();
		qa_db_ccount_update();

		$this->processedItems = $this->totalItems;
		$this->isFinished = true;
	}
}
