<script>
	/* * * * * * * * */
	/* * VARIABLES * */
	/* * * * * * * * */

	let edtDocID = "";
	let templateListEvalFunction = "";
	let pkgnmcol = "300";
	let stscol = "200";
	let templateName = "";
	let sentTPattern = /(\d+)./;
	let sentOPattern = /Index=(\d+)/;
	let namePattern = /PackageName=(.+)Subject=/;
	let altNamePattern = /PackageName=(.+)/;
	let sentTotal = 0;
	let sentOut = 0;
	let bulkHistory = [];

	/* * * * * * * * */
	/* * FUNCTIONS * */
	/* * * * * * * * */

	function getTemplateNameFromMatches(id) {
		const template = matchingTemplates.find(template => template.id === id);
		return template ? template.name : "";
	}

	function getTemplateName(id) {
		const template = templates.find(template => template.id === id);
		return template ? template.name : "";
	}

	function subPage(page) {
		if (page === "History") {
			doBulkHistorySearch("LAST", 0);
			doBulkSearch("LAST", 0);
		}
	}

	/* * * * * * * * * * */
	/* * DRAW FUNCTIONS* */
	/* * * * * * * * * * */

	function loadHistorySubTable() {
		get("BulkSearchResultsCell").innerHTML = "";
		if (bulkHistory.length === 0) {
			get("BulkSearchResultsCell").innerHTML = '<div  id="BulkSearchResultstable">No Bulk Send History Found</div>';
			return;
		}

		let page = `
        <table cellpadding="0" cellspacing="0" width="800px" id="BulkSearchResultstable" border="1">
            <tr>
                <td>
                    <table cellpadding="0" cellspacing="0" width="100%" border="1">
                        <tr height="50px">
                            <td class="ColHeader" id="NameColSrtStsBtn" onclick="doBulkHistorySearch('Idx3', 0);" align="center" width="425">
                                <table cellpadding="0" cellspacing="0" width="98%">
                                    <tr>
                                        <td width="10px">&nbsp;</td>
                                        <td align="left" class="searchtext">Job Name</td>
                                        <td class="grey" id="NameArw" align="right"><i class="fas fa-sort"></i></td>
                                        <td width="10px">&nbsp;</td>
                                    </tr>
                                </table>
                            </td>
                            <td class="ColHeader" id="PkgColSrtStsBtn" onclick="doBulkHistorySearch('Action', 0);" align="center" width="250">
                                <table cellpadding="0" cellspacing="0" width="98%">
                                    <tr>
                                        <td width="10px">&nbsp;</td>
                                        <td align="left" class="searchtext">Status</td>
                                        <td class="grey" id="CompArw" align="right"><i class="fas fa-sort"></i></td>
                                        <td width="10px">&nbsp;</td>
                                    </tr>
                                </table>
                            </td>
                            <td class="ColHeader" id="BulkColSrtDateBtn" onclick="doBulkHistorySearch('Recorded', 0);" align="center" width="250">
                                <table cellpadding="0" cellspacing="0" width="98%">
                                    <tr>
                                        <td width="10px">&nbsp;</td>
                                        <td align="left" class="searchtext">Completed On</td>
                                        <td class="grey" id="DateArw" align="right"><i class="fas fa-sort"></i></td>
                                        <td width="10px">&nbsp;</td>
                                    </tr>
                                </table>
                            </td>
                            <td width="120px">
                                <table width="100%">
                                    <tr>
                                        ${histStart !== 0 ? `
                                            <td width="10px">&nbsp;</td>
                                            <td align="left" id="backbtn">
                                                <div class="ediBtn navNext pageBtn" id="PkgColSrtBackBtn" onclick="doBulkHistorySearch('LAST', -1);">Back</div>
                                            </td>
                                        ` : ''}
                                        ${bulkHistory.length === limit ? `
                                            <td align="right" id="ediBtn navNext">
                                                <div class="ediBtn navBack pageBtn" id="PkgColSrtediBtn navNext" onclick="doBulkHistorySearch('LAST', 1);">Next</div>
                                            </td>
                                            <td width="10px">&nbsp;</td>
                                        ` : ''}
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
    `;

		for (let p = 0; p < bulkHistory.length; p++) {
			page += `
            <tr>
                <td id="DocResultRow${p}">
                    <table class="topbrd PckgRow" cellpadding="0" cellspacing="0" width="100%" height="40px">
                        <tr>
                            <td class="ColTextTen" align="left" width="${pkgnmcol}" id="PkgRsNm${p}">${bulkHistory[p].name}</td>
                            <td class="ColTextTwenty" align="left" width="${stscol}">${bulkHistory[p].action}</td>
                            <td align="left" width="${stscol}">${bulkHistory[p].recorded === "NULL" ? 'Not Sent' : bulkHistory[p].recorded}</td>
                            <td width="40px" class="cursor" align="center" onclick="getBulkSendReport(${p});">
                                <i title="Package Statuses" class="fas fa-file-waveform FAStandardIcon"></i>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        `;
		}

		page += '</table>';
		get("BulkSearchResultsCell").innerHTML = page;

		let histLstOrdByEl = '';
		switch (histOrderBy) {
			case "Recorded":
				histLstOrdByEl = "DateArw";
				break;
			case "Action":
				histLstOrdByEl = "CompArw";
				break;
			case "Idx3":
				histLstOrdByEl = "NameArw";
				break;
		}

		const histLstOrdByHtml = histLastPckgOrderBy === histOrderBy ? "▲" : "▼";

		get("NameArw").innerHTML = '<i class="fas fa-sort"></i>';
		get("CompArw").innerHTML = '<i class="fas fa-sort"></i>';
		get("DateArw").innerHTML = '<i class="fas fa-sort"></i>';
		if (histLstOrdByEl !== "") {
			get(histLstOrdByEl).innerHTML = histLstOrdByHtml;
		}
	}

	function drawPackageSearchResults() {
		get("ActiveBulkResultsCell").innerHTML = "";
		if (packages.length === 0) return false;

		let page = `<table cellpadding="0" cellspacing="0" width="800px" id="BulkSearchResultstable" border=1>
						<tr>
							<td>
								<table cellpadding="0" cellspacing="0" width="100%" border="1">
								<tr>
									<td align="center" width="425">
										<table cellpadding="0" cellspacing="0" width="98%" height="50px">
											<tr>
												<td width="10px">&nbsp;</td>
												<td align="left">Job Name</td>
												<td width="10px">&nbsp;</td>
											</tr>
										</table>
									</td>
									<td class="ColHeader" id="PkgColSrtStsBtn" onclick='doBulkSearch("Action",0)' align="center" width="250">
										<table cellpadding="0" cellspacing="0" width="98%">
											<tr>
												<td width="10px">&nbsp;</td>
												<td align="left" class="searchtext">
													Sent/Total
												</td>
												<td class="grey" id="CompArw" align="right">
													<i class="fas fa-sort"></i>
												</td>
												<td width="10px">&nbsp;</td>
											</tr>
										</table>
									</td>
									<td class="ColHeader" id="BulkColSrtDateBtn" onclick=doBulkSearch("Last_Executed",0); align="center" width="250">
										<table cellpadding="0" cellspacing="0" width="98%">
											<tr>
												<td width="10px">&nbsp;</td>
												<td align="left" class="searchtext">
													Date
												</td>
												<td class="grey" id="DateArw" align="right">
													<i class="fas fa-sort"></i>
												</td>
												<td width="10px">&nbsp;</td>
											</tr>
										</table>
									</td>
									<td width="120px">
										<table width="100%">
											<tr>`;
		if (searchStart != 0) {
			page += `<td width="10px">&nbsp;</td>
						<td align="left" id="backbtn">
							<div class="ediBtn navNext pageBtn" id="PkgColSrtBackBtn" onclick=doBulkSearch("LAST",-1);>
								Back
							</div>
						</td>`;
		}
		if (packages.length == limit) {
			page += `<td align="right" id="ediBtn navNext">
						<div class="ediBtn navBack pageBtn" id="PkgColSrtediBtn navNext" onclick=doBulkSearch("LAST",1);>
							Next
						</div>
					<td>
					<td width="10px">&nbsp;</td>`;
		}
		page += `</tr></table></td></tr></table></td></tr>`;
		for (let p = 0; p < packages.length; p++) {
			tempName = packages[p].triggeraction.match(namePattern);
			if (tempName) jobName = packages[p].triggeraction.match(namePattern)[1];
			else jobName = packages[p].triggeraction.match(altNamePattern)[1];
			sentTotal = packages[p].triggeraction.match(sentTPattern)[1];
			sentOut = packages[p].triggeraction.match(sentOPattern)[1];
			page += `<tr>
						<td id="DocResultRow${p}">
							<table class="topbrd PckgRow" cellpadding="0" cellspacing="0" width="100%" style="height:40px">
								<tr>
									<td class="ColTextTen" align="left" width="${pkgnmcol}" id="PkgRsNm${p}">
										${jobName}
									</td>
									<td class="ColTextTwenty" align="left" width="${stscol}">
										${sentOut}/${sentTotal}
									</td>`;
			if (packages[p].lastexecuted == "NULL") page += `<td align="left" width="${stscol}">Not Sent</td>`;
			else page += `<td align="left" width="${stscol}">${packages[p].lastexecuted}</td>`;
			page += `<td class="cursor" onclick={edtDocID="${packages[p].id}";drawStopBulkSendModal();} align="center">
						<span class="fa-layers fa-fw fa-lg w-100">
							<i title="Stop Bulk Send" class="fas fa-octagon FAStandardIcon"></i>
								<span class="fa-layers-text fa-inverse w-100 fa-xs">Stop</span>
						</span>
					</td></tr></table></td></tr>`;
		}
		page += `</table>`;
		get("ActiveBulkResultsCell").innerHTML = page;

		if (theOrderBy === "Last_Executed") lstOrdByEl = "DateArw";
		else if (theOrderBy === "Action") lstOrdByEl = "CompArw";
		else lstOrdByEl = "";

		if (lastPckgOrderBy === theOrderBy) LstOrdByHTML = "▲";
		else LstOrdByHTML = "▼";

		get("CompArw").innerHTML = '<i class="fas fa-sort"></i>';
		get("DateArw").innerHTML = '<i class="fas fa-sort"></i>';
		if (lstOrdByEl !== "") {
			get(lstOrdByEl).innerHTML = LstOrdByHTML;
		}
	}

	function drawStopBulkSendModal() {
		$('#StopBulkConfirm').off();
		$('#StopBulkConfirm').click(function() {
			deleteBulkSend(edtDocID)
		});
		modalFactory('StopBulkSendModal').show();
	}

	/* * * * * * * * */
	/* * REST CALLS* */
	/* * * * * * * * */

	function getTemplateList(evalFunction) {
		templates = [];
		templateListEvalFunction = evalFunction;
		pleaseWait("Getting list of templates");
		doRestCall("common/rest.php", {
			session: SID,
			controlid: CID,
			resource: "TEMPLATES",
			action: "GETLIST",
			type: "Signing"
		}, processGetTemplateListJSON);
	}

	function processGetTemplateListJSON(theJSON) {
		pleaseWait("");
		var theData = JSON.parse(theJSON);
		if (!theData.result) {
			logout(theData.error);
		} else {
			if (theData.templates.length > 0) {
				for (var t = 0; t < theData.templates.length; t++) {
					templates.push({
						id: theData.templates[t].id,
						name: theData.templates[t].name,
						redirecturl: theData.templates[t].redirecturl
					});
				}
			}
		}
		if (templateListEvalFunction != "") {
			eval(templateListEvalFunction + "('',0)");
			templateListEvalFunction = "";
		}
	}

	function deleteBulkSend(id) {
		pleaseWait("Deleting Bulk Send... ");
		doRestCall("common/rest.php", {
			session: SID,
			controlid: CID,
			resource: "TRIGGERS",
			action: "DELETE",
			id,
		}, processDeleteTemplateAction);
	}

	function processDeleteTemplateAction(theJSON) {
		pleaseWait("");
		let theData = JSON.parse(theJSON);
		if (!theData.result) return showError("Bulk send already completed.");

		showPage("DocSearchResults");
		showSnackbar("Bulk send successfully stopped.");
	}

	function getBulkSendReport(ind) {
		doRestCall("common/rest.php", {
			session: SID,
			controlid: CID,
			resource: "REPORTS/ONDEMANDREPORTS",
			action: "RUNREPORT",
			BulkID: bulkHistory[ind].id,
			Year: bulkHistory[ind].recorded.substr(6),
			reportname: "Bulk Send"
		}, processGetBulkSendReportAction);
	}

	function processGetBulkSendReportAction(theJSON) {
		pleaseWait("");
		let theData = JSON.parse(theJSON);
		if (!theData.result) return logout(theData.error);
		window.open(tempDir + theData.file, "_blank");
	}
</script>
<table cellpadding="0" cellspacing="0" width="100%" id="ResultsBulkTable">
	<tr>
		<td class="navrow" align="center" height="40px" width="100%">
			<table height="40px" width="100%">
				<tr>
					<td width="20px">&nbsp;</td>
					<td width="80px">
						<div id="DocResBackCell" title="Return to main menu" class="ediBtn navBack" onclick="goToLanding();">Menu</div>
					</td>
					<td>&nbsp;</td>
					<td width="10px">&nbsp;</td>
					<td align="center" width="140px">
						<div id="NewBulkSendBtn" title="New Bulk Send" onclick="uploadType=2;getTemplateTypeFilterList(false,'Signing','');" ; class="ediBtn navNext" style="width:140px">New Bulk Send</div>
					</td>
					<td width="20px">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr id="SignSearchRow" height="100%">
		<td height="100%" valign="top" align="center">
			<table cellpadding="5" cellspacing="0" height="100%">
				<tr>
					<td valign="top" align="center">
						<table cellpadding="2" cellspacing="0" width="100%">
							<tr>
								<td align="center" valign="top" id="ActiveBulkResultsCell">&nbsp;</td>
							</tr>
							<tr>
								<td align="center" valign="top" id="BulkSearchResultsCell">&nbsp;</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>