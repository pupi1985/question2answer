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

abstract class Q2A_Admin_Recalc_Caching_AbstractStepCaching extends Q2A_Admin_Recalc_AbstractStep
{
	const BATCH_AMOUNT = 500;

	abstract public function onlyProcessExpiredItems();

	public function __construct()
	{
		$this->messageLangId = 'admin/caching_delete_progress';
	}

	public function setup()
	{
		$cacheDriver = Q2A_Storage_CacheFactory::getCacheDriver();
		$cacheStats = $cacheDriver->getStats();
		$this->totalItems = $cacheStats['files'];
	}

	public function execute()
	{
		$cacheDriver = Q2A_Storage_CacheFactory::getCacheDriver();
		$cacheStats = $cacheDriver->getStats();
		$remaining = $cacheStats['files'] - $this->nextItemId;
		$limit = min($remaining, self::BATCH_AMOUNT);

		if ($remaining > 0) {
			$expiredOnly = $this->onlyProcessExpiredItems();

			$deleted = $cacheDriver->clear($limit, $this->nextItemId, $expiredOnly);

			$this->nextItemId += $limit - $deleted; // skip files that weren't deleted on next iteration
			$this->processedItems += $limit;
			$this->totalItems = max($this->totalItems, $this->processedItems);
		}

		if ($limit < self::BATCH_AMOUNT) {
			$this->isFinished = true;
		}
	}
}
