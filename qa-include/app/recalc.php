<?php
/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

	Description: Managing database recalculations (clean-up operations) and status messages


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

/*
	A full list of redundant (non-normal) information in the database that can be recalculated:

	Recalculated in reindex_content:
	================================
	^titlewords (all): index of words in titles of posts
	^contentwords (all): index of words in content of posts
	^tagwords (all): index of words in tags of posts (a tag can contain multiple words)
	^posttags (all): index tags of posts
	^words (all): list of words used for indexes
	^options (title=cache_*): cached values for various things (e.g. counting questions)

	Recalculated in recount_posts:
	==============================
	^posts (upvotes, downvotes, netvotes, hotness, acount, amaxvotes, flagcount): number of votes, hotness, answers, answer votes, flags

	Recalculated in users_points:
	===============================
	^userpoints (all except bonus): points calculation for all users
	^options (title=cache_userpointscount):

	Recalculated in recalc_categories:
	===================================
	^posts (categoryid): assign to answers and comments based on their antecedent question
	^posts (catidpath1, catidpath2, catidpath3): hierarchical path to category ids (requires QA_CATEGORY_DEPTH=4)
	^categories (qcount): number of (visible) questions in each category
	^categories (backpath): full (backwards) path of slugs to that category

	Recalculated in refill_events:
	=================================
	^sharedevents (all): per-entity event streams (see big comment in /qa-include/db/favorites.php)
	^userevents (all): per-subscriber event streams

	[but these are not entirely redundant since they can contain historical information no longer in ^posts]
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

/**
 * Return the process manager for the given process string.
 * @param string $process
 * @return Q2A_Admin_Recalc_AbstractProcessManager
 */
function qa_recalc_get_process_manager($process)
{
	$processes = [
		'recount_posts' => Q2A_Admin_Recalc_RecountPosts_ProcessManager::class,
		'reindex_content' => Q2A_Admin_Recalc_ReindexContent_ProcessManager::class,
		'users_points' => Q2A_Admin_Recalc_UsersPoints_ProcessManager::class,
		'refill_events' => Q2A_Admin_Recalc_RefillEvents_ProcessManager::class,
		'recalc_categories' => Q2A_Admin_Recalc_RecalcCategories_ProcessManager::class,
		'delete_hidden_posts' => Q2A_Admin_Recalc_DeleteHiddenPosts_ProcessManager::class,
		'blobs_to_disk' => Q2A_Admin_Recalc_BlobsToDisk_ProcessManager::class,
		'blobs_to_db' => Q2A_Admin_Recalc_BlobsToDb_ProcessManager::class,
		'cache_trim' => Q2A_Admin_Recalc_Caching_CacheTrim_ProcessManager::class,
		'cache_clear' => Q2A_Admin_Recalc_Caching_CacheClear_ProcessManager::class,
	];

	// Make sure something is run and avoid the error handling for such an unlikely case
	if (!isset($processes[$process])) {
		$process = key($processes);
	}

	$managerClassName = $processes[$process];

	return new $managerClassName();
}
