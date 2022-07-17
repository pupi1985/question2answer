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

abstract class Q2A_Admin_Recalc_AbstractProcessManager
{
	/** @var string */
	protected $stateOption;

	/** @var int */
	protected $currentStepIndex = 0;

	/** @var array */
	protected $steps;

	/**
	 * @return array Return step and process state data
	 */
	public function execute($forceRestart = false)
	{
		$newStep = false;

		try {
			if ($forceRestart) {
				throw new Exception('Force exception');
			}

			$step = $this->loadState();

			if ($step->isFinished()) {
				$this->currentStepIndex++;

				if ($this->currentStepIndex >= count($this->steps)) {
					$this->clearState();

					return [
						'process_finished' => true,
						'message' => qa_lang('admin/process_complete'),
					];
				}

				$newStep = true;
				$step = $this->getCurrentStepInstance();
			}
		} catch (Exception $e) {
			$step = $this->getCurrentStepInstance();
			$newStep = true;
		}

		if ($newStep) {
			$step->setup();
		} else {
			$step->execute();
		}

		$result = [
			'step_state' => $step->asArray(),
			'step_index' => $this->currentStepIndex,
			'process_finished' => false,
		];
		$this->saveState($result);

		$result['message'] = $step->getMessage();

		return $result;
	}

	/**
	 * @throws Exception
	 */
	private function loadState()
	{
		$state = qa_opt($this->stateOption);
		$state = json_decode($state, true);

		if (!isset($state['step_index'])) {
			throw new Exception('Nothing to load');
		}

		$this->currentStepIndex = $state['step_index'];

		$step = $this->getCurrentStepInstance();
		$step->loadFromJson($state['step_state']);

		return $step;
	}

	private function saveState($state)
	{
		qa_opt($this->stateOption, json_encode($state));
	}

	private function clearState()
	{
		qa_opt($this->stateOption, '');
	}

	/**
	 * @return Q2A_Admin_Recalc_AbstractStep
	 */
	protected function getCurrentStepInstance()
	{
		// Make sure the step index to instantiate is valid
		$this->currentStepIndex = min(max(0, $this->currentStepIndex), count($this->steps) - 1);

		return new $this->steps[$this->currentStepIndex];
	}
}
