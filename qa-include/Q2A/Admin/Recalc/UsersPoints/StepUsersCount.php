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

class Q2A_Admin_Recalc_UsersPoints_StepUsersCount extends Q2A_Admin_Recalc_AbstractStep
{
	public function __construct()
	{
		$this->messageLangId = 'admin/recalc_users_points_usercount';
	}

	public function setup()
	{
		// Rough approximation of the amount of work to perform
		$this->totalItems = qa_opt('cache_userpointscount');
	}

	public function execute()
	{
		require_once QA_INCLUDE_DIR . 'db/points.php';
		require_once QA_INCLUDE_DIR . 'db/users.php';

		qa_db_userpointscount_update(); // for progress update - not necessarily accurate
		qa_db_uapprovecount_update(); // needs to be somewhere and this is the most appropriate place

		$this->processedItems = $this->totalItems;
		$this->isFinished = true;
	}
}
