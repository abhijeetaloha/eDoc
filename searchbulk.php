<script>
	/* * * * * * * * */
	/* * VARIABLES * */
	/* * * * * * * * */

	var lstOrdByEl,
		lastPckgOrderBy,
		lastOBTxt = "";
	var histLstOrdByEl,
		histLastPckgOrderBy,
		histLastOBTxt = "";
	var theOrderBy = "";
	var histOrderBy = "";
	var limit = 10;
	var searchStart = 0;
	var histStart = 0;
	var histStDt = "";
	var stDt = "";
	var histEnDt = "";
	var enDt = "";
	/* * * * * * * * */
	/* * FUNCTIONS * */
	/* * * * * * * * */

	function checkTagsLoaded() {
		if (tagsLoaded) {
			drawTagsFilter();
		} else {
			setTimeout(function() {
				checkTagsLoaded();
			}, 200);
		}
	}

	function checkUsersLoaded() {
		get("DocSrchBtn").disabled = false;
		drawSelectUsers();
	}

	function checkForSignSetSearch(theEvent) {
		var keyCode = theEvent.keyCode || theEvent.which;
		if (keyCode == 13) {
			searchStart = 0;
			doBulkSearch("", 0);
		}
		return true;
	}

	const setupSearchPage = async () => {
		await getUsers({
			usePleaseWait: true
		});
		tagsCBFunction = "drawTagsFilter";
		searchTags("");
		checkUsersLoaded();
	}

	/* * * * * * * * * * */
	/* * DRAW FUNCTIONS* */
	/* * * * * * * * * * */

	function drawSelectUsers() {
		var page = "";
		if (users.length > 1) {
			page =
				page +
				'<select id="UserName" class="form-select" width="271px" name="UserName">';
			page += '<option value="">All</option>';
			userSorted = users.slice(0);
			userSorted.sort(function(a, b) {
				var nameA = a.fullname.toUpperCase();
				var nameB = b.fullname.toUpperCase();
				if (nameA < nameB) {
					return -1;
				}
				if (nameA > nameB) {
					return 1;
				}
				return 0;
			});
			for (var u = 0; u < userSorted.length; u++) {
				let selHTML = "";
				if (sessionUser == userSorted[u].name) {
					selHTML = " selected";
				}
				page =
					page +
					'<option value="' +
					userSorted[u].name +
					'"' +
					selHTML +
					">" +
					userSorted[u].fullname;
			}
			page += "</select>";
		} else {
			page =
				page +
				'<input type="hidden" name="UserName" id="UserName" value="' +
				users[0].name +
				'">' +
				users[0].fullname;
		}
		get("UserListCell").innerHTML = page;
	}

	function drawTagsFilter() {
		let page = `<select id="TagName" class="form-select" width="250px" name="TagName">
						<option value="">All</option>`;
		for (let tag of Tags) {
			page += `<option value="${tag.id}">${tag.name}`;
		}
		page += `</select>`;
		get("TagsListCell").innerHTML = page;
	}

	/* * * * * * * * */
	/* * REST CALLS* */
	/* * * * * * * * */

	function doBulkSearch(orderBy, page) {
		if (templates.length == 0) return getTemplateList("doBulkSearch");
		packages = [];
		theOrderBy = orderBy;
		let theStart = 0;
		let obt = "";

		if (page === 1) theStart = searchStart + (limit - 1);
		else if (page === -1) theStart = Math.max(0, searchStart - (limit - 1));
		else theStart = searchStart;

		if (orderBy !== "LAST") {
			obt = orderBy;
			if (lastPckgOrderBy === orderBy && orderBy !== "") {
				obt += " DESC";
				orderBy = "";
			}
			lastPckgOrderBy = orderBy;
			lastOBTxt = obt;
		} else obt = lastOBTxt;

		if (obt === "") {
			lastOBTxt = "Action DESC";
			obt = `Action DESC LIMIT ${theStart},${limit}`;
		} else obt += ` LIMIT ${theStart},${limit}`;

		searchStart = theStart;
		pleaseWait("Searching...");
		get("DocSrchBtn").disabled = true;
		doRestCall("common/rest.php", {
			session: SID,
			controlid: CID,
			resource: "TRIGGERS",
			action: "GetListByType",
			type: "MASS_TMP_SEND",
			orderBy: obt,
		}, processSearchDocJSON);
	}

	function processSearchDocJSON(theJSON) {
		pleaseWait("");
		get("DocSrchBtn").disabled = false;
		packages = [];
		var theData = JSON.parse(theJSON);
		if (!theData.result) return logout(theData.error);

		for (var p = 0; p < theData.triggers.length; p++) {
			var newblksnd = new BulkSend();
			newblksnd.criteria = theData.triggers[p].criteria;
			newblksnd.description = theData.triggers[p].description;
			newblksnd.id = theData.triggers[p].id;
			newblksnd.lastexecuted = theData.triggers[p].lastexecuted;
			newblksnd.name = theData.triggers[p].name;
			newblksnd.nexttest = theData.triggers[p].nexttest;
			newblksnd.otherid = theData.triggers[p].otherid;
			newblksnd.period = theData.triggers[p].period;
			newblksnd.tags = theData.triggers[p].tags;
			newblksnd.triggeraction = theData.triggers[p].triggeraction;
			packages.push(newblksnd);
		}
		drawPackageSearchResults();
	}

	function doBulkHistorySearch(orderBy, page) {
		bulkHistory = [];
		histOrderBy = orderBy;
		var type = "MASS_TMP_SEND";
		var theStart = 0;
		if (page == 1) {
			theStart = histStart + (limit - 1);
		} else if (page == -1) {
			theStart = Math.max(0, histStart - (limit - 1));
		} else {
			theStart = histStart;
		}
		var obt = "";
		if (orderBy != "LAST") {
			obt = orderBy;
			if (histLastPckgOrderBy == orderBy && orderBy != "") {
				obt = obt + " DESC";
				orderBy = "";
			}
			histLastPckgOrderBy = orderBy;
			histLastOBTxt = obt;
		} else {
			obt = histLastOBTxt;
		}
		if (obt == "") {
			histLastOBTxt = "Recorded DESC";
			obt = "Recorded DESC LIMIT " + theStart + "," + limit;
		} else {
			obt = obt + " LIMIT " + theStart + "," + limit;
		}
		histStart = theStart;
		var temp = {
			session: SID,
			controlid: CID,
			resource: "BULKSENDS",
			action: "GETHISTORY",
			orderby: obt,
		};
		pleaseWait("Searching...   ");
		doRestCall("common/rest.php", temp, processSearchHistoryJSON);
	}

	function processSearchHistoryJSON(theJSON) {
		pleaseWait("");
		bulkHistory = [];
		const theData = JSON.parse(theJSON);
		if (!theData.result) return logout(theData.error);

		bulkHistory = theData.bulksends.map((item) => {
			const {
				name,
				id,
				recorded,
				action
			} = item;
			return {
				name,
				id,
				recorded,
				action
			};
		});
		loadHistorySubTable();
	}
</script>
<table cellpadding="0" cellpadding="0" width="100%" id="SearchDocTable" class="hidden">
	<tr>
		<td class="navrow" height="40px">
			<table align="right" cellpadding="2" cellspacing="0" height="40" width="100%">
				<tr>
					<td width="20px">&nbsp;</td>
					<td width="80px"><button id="DocSrchBackBtnCell" type="button" class="ediBtn navBack" onclick="goToLanding();" id="">Menu</button></td>
					<td>&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr id="SignSearchRow" height="100%">
		<td height="100%" valign="top" align="center">
			<table cellpadding="5" cellspacing="0" height="100%" width="550px">
				<tr>
					<td align="center">
						<table cellpadding="2" cellspacing="0" width="100%" height="420px">
							<tbody>
								<tr>
									<td colspan="2" height="10px">&nbsp;</td>
								</tr>
								<tr>
									<td align="left" class="SearchHeader" colspan="2" height="30px">Search for Templates:</td>
								</tr>
								<tr>
									<td class="searchtext">Created By:</td>
									<td id="UserListCell" align="center"></td>
								</tr>
								<tr>
									<td class="searchtext">Template Name:</td>
									<td align="center"><input class="form-control" width="250px" name="DSearchBox" id="DSearchBox" value="" placeholder="Type template name" autocomplete="OFF" onkeypress="checkForSignSetSearch(event);" type="text"></td>
								</tr>
								<tr>
									<td class="searchtext">Filter by Tag:</td>
									<td id="TagsListCell" align="center"></td>
								</tr>
								<tr>
									<td class="searchtext">Template Type:</td>
									<td align="center"><select input class="form-select" width="250px" name="TypeBox" id="TypeBox" onkeypress="checkForSignSetSearch(event);">
											<option value="">All
											<option value="Signing">Signing
											<option value="Reference">Reference
											<option value="Requested">Requested
										</select></td>
								</tr>
								<tr>
									<td class="searchtext">Created on Date: </td>
									<td align="center"><input type="date" width="250px" name="StartDate" id="StartDate" placeholder="Start created on date" class="date form-control" value="" AUTOCOMPLETE="OFF"></td>
								</tr>
								<tr>
									<td class="searchtext">End Date: </td>
									<td align="center"><input type="date" width="250px" name="EndDate" id="EndDate" placeholder="End date" class="date form-control" value="" AUTOCOMPLETE="OFF"></td>
								</tr>
								<tr>
									<td align="center" colspan="2"><button type="button" title="Search for Bulk Send" onclick='searchStart=0;doBulkSearch("",0);' id="DocSrchBtn" class="ediBtn navNext" disabled>Search</button></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>