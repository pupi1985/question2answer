/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

	File: qa-content/qa-admin.js
	Description: Javascript for admin pages to handle Ajax-triggered operations


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

const qa_recalcProcesses = new Map();

window.onbeforeunload = event => {
	for (let [processKey, process] of qa_recalcProcesses.entries()) {
		if (process.clientRunning) {
			event.preventDefault();
			event.returnValue = true;
		}
	}
};

/**
 * @param {String} processKey
 * @param {object} options - See keys and default values below
 * @returns {boolean}
 */
function qa_recalc_click(processKey, options = {})
{
	options = {
		forceRestart: false,
		requiresServerTracking: true,
		callbackStart: process => {},
		callbackStop: hasFinished => {},
		...options,
	};

	let process = qa_recalcProcesses.get(processKey) ?? {processKey: processKey};

	const startButton = document.getElementById(processKey);
	const continueButton = document.getElementById(processKey + '_continue');
	const statusLabel = document.getElementById(processKey + '_status');

	if (process.clientRunning) {
		process.stopRequest = true;
	} else {
		process = {
			...process,
			"startButton": startButton,
			"continueButton": continueButton,
			"statusLabel": statusLabel,
			"clientRunning": true,
			"stopRequest": false,
			"options": options
		};

		qa_recalcProcesses.set(processKey, process);

		qa_conceal(process.continueButton);

		statusLabel.innerHTML = qa_langs.please_wait;
		startButton.value = qa_langs.process_stop;

		process.options.callbackStart(process);

		qa_recalc_update(process);
	}

	return false;
}

function qa_recalc_update(process)
{
	const recalcCode = process.startButton.form.elements.code.value;

	qa_ajax_post(
		'recalc',
		{
			process: process.processKey,
			forceRestart: process.options.forceRestart,
			code: recalcCode
		},
		function (lines) {
			const result = lines[0] ?? '0';
			const message = lines[1] ?? null;
			const nextProcessKey = lines[2] ?? null;
			const hasFinished = (lines[3] ?? '0') === '1';

			switch (result) {
				case '1':
					if (message !== null) {
						process.statusLabel.innerHTML = message;
					}

					process.serverProcessPending = process.options.requiresServerTracking ? !hasFinished : false;
					if (hasFinished || process.stopRequest) {
						process.clientRunning = false;
						qa_recalc_cleanup(process, hasFinished);
					} else {
						process.options.forceRestart = false;
						qa_recalc_update(process);
					}
					break;
				case '0':
					process.statusLabel.innerHTML = message;
					process.clientRunning = false;
					qa_recalc_cleanup(process);
					break;
				default:
					qa_ajax_error();
					process.clientRunning = false;
					qa_recalc_cleanup(process);
			}
		}
	);
}

function qa_recalc_cleanup(process, hasFinished = false)
{
	process.options.callbackStop(hasFinished);

	if (process.options.requiresServerTracking && process.serverProcessPending) {
		process.startButton.value = qa_langs.process_restart;
		process.statusLabel.innerHTML = qa_langs.process_unfinished;
		qa_reveal(process.continueButton);
	} else {
		process.startButton.value = qa_langs.process_start;
		qa_conceal(process.continueButton);
	}
}

function qa_mailing_start(noteid, pauseid)
{
	qa_ajax_post('mailing', {},
		function(lines) {
			if (lines[0] == '1') {
				document.getElementById(noteid).innerHTML = lines[1];
				window.setTimeout(function() {
					qa_mailing_start(noteid, pauseid);
				}, 1); // don't recurse

			} else if (lines[0] == '0') {
				document.getElementById(noteid).innerHTML = lines[1];
				document.getElementById(pauseid).style.display = 'none';

			} else {
				qa_ajax_error();
			}
		}
	);
}

function qa_admin_click(target)
{
	var p = target.name.split('_');

	var params = {entityid: p[1], action: p[2]};
	params.code = target.form.elements.code.value;

	qa_ajax_post('click_admin', params,
		function(lines) {
			if (lines[0] == '1')
				qa_conceal(document.getElementById('p' + p[1]), 'admin');
			else if (lines[0] == '0') {
				alert(lines[1]);
				qa_hide_waiting(target);
			} else
				qa_ajax_error();
		}
	);

	qa_show_waiting_after(target, false);

	return false;
}

function qa_version_check(uri, version, elem, isCore)
{
	var params = {uri: uri, version: version, isCore: isCore};

	qa_ajax_post('version', params,
		function(lines) {
			if (lines[0] == '1')
				document.getElementById(elem).innerHTML = lines[1];
		}
	);
}

function qa_get_enabled_plugins_hashes()
{
	var hashes = [];
	$('[id^=plugin_enabled]:checked').each(
		function(idx, elem) {
			hashes.push(elem.id.replace("plugin_enabled_", ""));
		}
	);

	$('[name=enabled_plugins_hashes]').val(hashes.join(';'));
}
