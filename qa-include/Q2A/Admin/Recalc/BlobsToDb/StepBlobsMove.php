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

class Q2A_Admin_Recalc_BlobsToDb_StepBlobsMove extends Q2A_Admin_Recalc_AbstractStep
{
	const BATCH_AMOUNT = 1;

	public function __construct()
	{
		$this->messageLangId = 'admin/blobs_move_moved';
	}

	public function setup()
	{
		require_once QA_INCLUDE_DIR . 'db/recalc.php';

		$this->totalItems = qa_db_count_blobs_on_disk();
		$this->lastProcessedItemId = '';
	}

	public function execute()
	{
		require_once QA_INCLUDE_DIR . 'db/recalc.php';

		$blobs = qa_db_get_next_blobs_on_disk($this->lastProcessedItemId, self::BATCH_AMOUNT);

		if (!empty($blobs)) {
			end($blobs);
			$this->lastProcessedItemId = key($blobs);

			require_once QA_INCLUDE_DIR . 'app/blobs.php';
			require_once QA_INCLUDE_DIR . 'db/blobs.php';

			foreach ($blobs as $blob) {
				$content = qa_read_blob_file($blob['blobid'], $blob['format']);
				qa_db_blob_set_content($blob['blobid'], $content);
				qa_delete_blob_file($blob['blobid'], $blob['format']);
			}

			$this->processedItems += count($blobs);
			$this->totalItems = max($this->totalItems, $this->processedItems);
		}

		if (count($blobs) < self::BATCH_AMOUNT) {
			$this->isFinished = true;
		}
	}
}
