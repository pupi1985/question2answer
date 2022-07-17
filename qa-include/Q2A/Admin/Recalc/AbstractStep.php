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

abstract class Q2A_Admin_Recalc_AbstractStep
{
	/** @var int|null */
	protected $processedItems = 0;

	/** @var int|null */
	protected $totalItems;

	/** @var mixed */
	protected $nextItemId = 0;

	/** @var mixed */
	protected $lastProcessedItemId = 0;

	/** @var bool */
	protected $isFinished = false;

	/** @var string */
	protected $messageLangId = '';

	abstract public function setup();

	abstract public function execute();

	/**
	 * @return array
	 */
	public function asArray()
	{
		return [
			'is_finished' => $this->isFinished,
			'processed_items' => $this->processedItems,
			'total_items' => $this->totalItems,
			'next_item_id' => $this->nextItemId,
			'last_processed_item_id' => $this->lastProcessedItemId,
		];
	}

	public function loadFromJson($state)
	{
		$this->isFinished = $state['is_finished'];
		$this->processedItems = $state['processed_items'];
		$this->totalItems = $state['total_items'];
		$this->nextItemId = $state['next_item_id'];
		$this->lastProcessedItemId = $state['last_processed_item_id'];
	}

	/**
	 * @return bool
	 */
	public function isFinished()
	{
		return $this->isFinished;
	}

	public function getMessage()
	{
		require_once QA_INCLUDE_DIR . 'app/format.php';

		return strtr(qa_lang($this->messageLangId), array(
			'^1' => qa_format_number($this->processedItems),
			'^2' => qa_format_number($this->totalItems),
		));
	}
}
