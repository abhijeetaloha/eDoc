<script>
	/* * * * * * * * */
	/* * VARIABLES * */
	/* * * * * * * * */

	var currentBulkSend = "";
	var noBulkSend = true;
	var defaultEmail =
		'%FROMNAME% has sent you a new eDOCSignature document for signing. To begin, click the "View Document" link below. You can securely sign your document on your computer, tablet, or other mobile device.';
	var clicked = "";

	/* * * * * * * * */
	/* * FUNCTIONS * */
	/* * * * * * * * */

	function forgetBulkSend() {
		currentBulkSend = new BulkSend();
		noBulkSend = true;
	}

	function updateFromTemplate() {
		var tempName =
			get("templatesel").options[get("templatesel").selectedIndex].text;
		if (get("BulkSendName").value == "") {
			get("BulkSendName").value = tempName;
		}
		if (get("EmailSubject").value == "") {
			get("EmailSubject").value = tempName + " is ready for you to sign";
		}
	}

	function loadDates() {
		var today = new Date();
		var currentDate = today.toISOString().slice(0, 10);
		var currentTime =
			(today.getHours() < 10 ? "0" + today.getHours() : today.getHours()) +
			":" +
			(today.getMinutes() < 10 ? "0" + today.getMinutes() : today.getMinutes());
		get("JobDate").min = currentDate;
		get("JobTime").min = currentTime;
		get("JobDate").value = currentDate;
		get("JobTime").value = currentTime;
	}

	function loadCurrentBulkSend() {
		get("BulkSendName").value = currentBulkSend.name;
		get("UserSelect").value = currentBulkSend.id;
		get("templatesel").value = currentBulkSend.otherid;
		get("FilterSel").value = currentBulkSend.tags;
		get("JobDate").value = currentBulkSend.triggeraction;
		get("JobTime").value = currentBulkSend.period;
		get("EmailSubject").value = currentBulkSend.description;
		get("EmailBody").value = hexToString(currentBulkSend.criteria);
		get("LinkExpire").value = currentBulkSend.lastexecuted;
	}

	function getJobDate() {
		var tempDate = get("JobDate").value;
		currentBulkSend.triggeraction = tempDate;
		var tempTime = get("JobTime").value;
		currentBulkSend.period = tempTime;
		tempDate = "" + tempDate + " " + tempTime + "";
		var tempd = new Date(tempDate);
		return getSQLDate(tempd);
	}

	function updateBulkSend() {
		var msg = validBulkName(get("BulkSendName").value);
		if (msg != "") {
			showError(msg, get("BulkSendName"));
			return false;
		}
		currentBulkSend.name = get("BulkSendName").value;
		currentBulkSend.id = get("UserSelect").value;
		if (get("templatesel").value == "") {
			showError("Please select a template.", get("templatesel"));
			return false;
		}
		currentBulkSend.otherid = get("templatesel").value;
		currentBulkSend.nexttest = getJobDate();
		currentBulkSend.tags = get("FilterSel").value;
		var msg = validEmailSubjectName(get("EmailSubject").value);
		if (msg != "") {
			showError(msg, get("EmailSubject"));
			return false;
		}
		currentBulkSend.description = get("EmailSubject").value;
		if (get("EmailBody").value.length == 0) {
			showError("Email body must contain link.", get("EmailBody"));
			return false;
		}
		currentBulkSend.criteria = stringToHex(get("EmailBody").value);
		currentBulkSend.lastexecuted = get("LinkExpire").value;
		loadTemplateFromID();
	}

	function reloadSetupBulk() {
		loadTemplateSelect();
		loadUserSelect();
		drawTags();
		loadDates();
		loadCurrentBulkSend();
	}

	function setupBulkSend() {
		currentBulkSend = new BulkSend();
		loadTemplateSelect();
		loadUserSelect();
		drawTags();
		loadDates();
		if (noBulkSend) {
			get("BulkSendName").value = "";
			get("EmailSubject").value = "";
			get("EmailBody").value = defaultEmail;
			noBulkSend = false;
		}
	}

	function setClicked(element) {
		clicked = element;
	}

	function addTag(tag) {
		if (clicked != "") {
			text = document.getElementById(clicked).value;
			if (selStart >= 0) {
				if (selEnd != selStart) {
					text = text.slice(0, selStart) + tag + text.slice(selEnd);
				} else {
					text = text.slice(0, selStart) + tag + text.slice(selStart);
				}
			} else {
				text = text + tag;
			}
			document.getElementById(clicked).value = text;
		}
	}

	function getPositionInTextArea(areaName) {
		var el = document.getElementById(areaName);
		el.focus();
		var sel = getInputSelection(el);
		selStart = sel.start;
		selEnd = sel.end;
	}

	function setPosition(el) {
		setClicked(el);
		getPositionInTextArea(el);
	}

	function getInputSelection(el) {
		var start = 0,
			end = 0,
			normalizedValue,
			range,
			textInputRange,
			len,
			endRange;

		if (
			typeof el.selectionStart == "number" &&
			typeof el.selectionEnd == "number"
		) {
			start = el.selectionStart;
			end = el.selectionEnd;
		} else {
			range = document.selection.createRange();

			if (range && range.parentElement() == el) {
				len = el.value.length;
				normalizedValue = el.value.replace(/\r\n/g, "\n");

				// Create a working TextRange that lives only in the input
				textInputRange = el.createTextRange();
				textInputRange.moveToBookmark(range.getBookmark());

				// Check if the start and end of the selection are at the very end
				// of the input, since moveStart/moveEnd doesnt return what we want
				// in those cases
				endRange = el.createTextRange();
				endRange.collapse(false);

				if (textInputRange.compareEndPoints("StartToEnd", endRange) > -1) {
					start = end = len;
				} else {
					start = -textInputRange.moveStart("character", -len);
					start += normalizedValue.slice(0, start).split("\n").length - 1;

					if (textInputRange.compareEndPoints("EndToEnd", endRange) > -1) {
						end = len;
					} else {
						end = -textInputRange.moveEnd("character", -len);
						end += normalizedValue.slice(0, end).split("\n").length - 1;
					}
				}
			}
		}
		return {
			start: start,
			end: end,
		};
	}

	/* * * * * * * * * * */
	/* * DRAW FUNCTIONS* */
	/* * * * * * * * * * */

	function drawTags() {
		var page =
			'<select onchange="filterTemplateListByTags();" id="FilterSel" name="FilterSel" class="form-select"><option value="">';
		for (var i = 0; i < Tags.length; i++) {
			page += '<option value="' + Tags[i].id + '"';
			if (lastFilterTag == Tags[i].id) {
				page += " selected";
			}
			page += ">" + Tags[i].name;
		}
		page += "</select>";
		get("TagFilter").innerHTML = page;
	}

	function loadTemplateSelect() {
		var page =
			'<table cellpadding="0" cellspacing="0" height="100%" width="100%">';
		page =
			page +
			'<tr><td><table cellpadding="0" cellspacing="0" width="100%"><tr><td align="left" width="190px">Filter by tag:</td><td id="TagFilter">&nbsp;</td></tr></table></td></tr><tr height="20px"><td></td><td></td></tr>';
		page =
			page +
			'<tr><td><table cellpadding="0" cellspacing="0" width="100%"><tr><td align="left" width="190px">Template:</td><td><select id="templatesel" name="templatesel" class="form-select" onChange="updateFromTemplate();">';
		page += '<option value=""></option>';
		for (var t = 0; t < filteredTemplates.length; t++) {
			page =
				page +
				'<option value="' +
				filteredTemplates[t].id +
				'">' +
				filteredTemplates[t].name;
		}
		page += "</select></td></tr></table></td></tr>";
		page += "</table>";
		get("TemplateSelect").innerHTML = page;
	}

	function loadUserSelect() {
		var page =
			'<td width="190px"><label for="UserSelect">Package Owner: </label></td>';
		if (users.length > 1) {
			page += '<td><select class="form-select" id="UserSelect">';
			userSorted = users.slice(0);
			userSorted.sort(function(a, b) {
				var nameA = a.name.toUpperCase();
				var nameB = b.name.toUpperCase();
				if (nameA < nameB) {
					return -1;
				}
				if (nameA > nameB) {
					return 1;
				}
				return 0;
			});
			for (var u = 0; u < userSorted.length; u++) {
				var tempHTML = userSorted[u].name == sessionUser ? "selected" : "";
				page =
					page +
					'<option value="' +
					userSorted[u].id +
					'"' +
					tempHTML +
					">" +
					userSorted[u].name;
			}
			page += "</select></td>";
		} else {
			page =
				page +
				'<td><input type="hidden" name="UserSelect" id="UserSelect" value="' +
				users[0].id +
				'">' +
				users[0].name +
				"</td>";
		}
		get("UserSelectDiv").innerHTML = page;
	}
</script>
<table cellpadding="0" cellspacing="0" width="100%" id="SetupBulkTable">
	<tr>
		<td class="navrow" align="center" height="40px" width="100%">
			<table height="40px" width="100%">
				<tr>
					<td width="20px">&nbsp;</td>
					<td width="80px">
						<div id="DocResBackCell" title="Return to main menu" class="ediBtn navBack" onclick='lastFilterTag="";forgetBulkSend();removeSigner("all");drawSigners();showPage("DocSearchResults");'>Back</div>
					</td>
					<td>&nbsp;</td>
					<td align="right">
						<div id="NextBtn" title="Next" onclick=updateBulkSend(); class="ediBtn navNext">Next</div>
					</td>
					<td width="20px">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr height="20px">
		<td>
			<div id="alertHolder"></div>
		</td>
	</tr>
	<tr>
		<td>
			<table cellpadding="0" cellspacing="0" width="100%" align="center">
				<tr>
					<td width="50%">
						<table cellpadding="0" cellspacing="0" align="right" style="margin-right:20px" width="50%">
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<td width="190px"><label for="BulkSendName">Job Name: </label></td>
											<td><input type="text" id="BulkSendName" name="Job Name" class="form-control"></td>
										</tr>
									</table>
								</td>
							</tr>
							<tr height="20px">
								<td>
								</td>
							</tr>
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" width="100%">
										<tr id="UserSelectDiv">
											<td></td>
										</tr>
									</table>
								</td>
							</tr>
							<tr height="20px">
								<td>
								</td>
							</tr>
							<tr>
								<td class="TemplateSelect" id="TemplateSelect"></td>
							</tr>
							<tr height="20px">
								<td>
								</td>
							</tr>
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<td width="190px"><label for="JobDate">Job Execution Date: </label></td>
											<td><input type="date" id="JobDate" name="Job Execution Date" class="form-control"></td>
										</tr>
										<tr height="20px">
											<td></td>
											<td></td>
										</tr>
										<tr>
											<td width="190px"><label for="JobTime">Job Execution Time: </label></td>
											<td><input type="time" id="JobTime" name="Job Execution Time" class="form-control"></td>
										</tr>
									</table>
								</td>
							</tr>
							<tr height="20px">
								<td>
								</td>
							</tr>
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<td width="190px"><label for="EmailSubject">Email Subject: </label></td>
											<td><input type="text" id="EmailSubject" name="Email Subject" class="form-control"></tdt>
										</tr>
									</table>
								</td>
							</tr>
							<tr height="20px">
								<td>
								</td>
							</tr>
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<td width="190px"><label for="EmailBody">Email Body: </label></td>
											<td><textarea id="EmailBody" name="Email Body" class="form-control" rows="6" onkeyup="setPosition('EmailBody');" onclick="setPosition('EmailBody');"></textarea></td>
										</tr>
									</table>
								</td>
							</tr>
							<tr height="20px">
								<td>
								</td>
							</tr>
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" width="100%" height="40px">
										<tr>
											<td align="center">
												Links will expire in <input class="form-control" id="LinkExpire" name="Link Expriration" title="Value must be between 1 and 10000" type="number" min="1" max="10000" value="7"> days.
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
					<td>
						<table cellpadding="0" cellspacing="0">
							<tr>
								<td class="allborder" valign="top">
									<div style="overflow-y:scroll;height:500px;width:218px">
										<table width="100%" id="tagTable">
											<tr>
												<td valign="top" style="font-weight:bold">Available Fields:</td>
											</tr>
											<tr>
												<td valign="top" title="Click to add tag" style="cursor:pointer" onclick="addTag('%SIGNERNAME%')">%SIGNERNAME%</td>
											</tr>
											<tr>
												<td valign="top">
													<font size="2">(Signer Name)</font>
												</td>
											</tr>
											<tr>
												<td valign="top" title="Click to add tag" style="cursor:pointer" onclick="addTag('%SPLASHNAME%')">%SPLASHNAME%</td>
											</tr>
											<tr>
												<td valign="top">
													<font size="2">(Institution Name)</font>
												</td>
											</tr>
											<tr>
												<td valign="top" title="Click to add tag" style="cursor:pointer" onclick="addTag('%FROMEMAIL%')">%FROMEMAIL%</td>
											</tr>
											<tr>
												<td valign="top">
													<font size="2">(Sender Email Address)</font>
												</td>
											</tr>
											<tr>
												<td valign="top" title="Click to add tag" style="cursor:pointer" onclick="addTag('%FROMNAME%')">%FROMNAME%</td>
											</tr>
											<tr>
												<td valign="top">
													<font size="2">(Sender Name)</font>
												</td>
											</tr>
											<tr>
												<td valign="top" title="Click to add tag" style="cursor:pointer" onclick="addTag('%PACKAGENAME%')">%PACKAGENAME%</td>
											</tr>
											<tr>
												<td valign="top">
													<font size="2">(Package Name)</font>
												</td>
											</tr>
											<tr height="40px">
												<td></td>
											</tr>
											<tr>
												<td valign="top">Link will be added after the message body automatically</td>
											</tr>
										</table>
									</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>