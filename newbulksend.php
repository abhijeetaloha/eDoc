<script>
    /* * * * * * * * */
    /* * VARIABLES * */
    /* * * * * * * * */

    var currentPage = 0;
    var pageCount = 0;
    var docInd = 0;
    var templates = [];
    var filteredTemplates = [];
    var templateID = "";
    var useNewDoc = false;
    var uploadType = 0;
    var lastFilterTag = "";
    var uploadedRoles = [];
    var lastTempType = "";
    var currentOrder = ["col1", "col2"];
    var signerFields = ["Full Name", "Email"];
    var showingSlctSgnFromSrch = false;
    var srchSgnrDlgEl;
    var srchSgnInd = 0;
    var lastSearchName = "";
    var slcSgnLst;
    const MAX_SIGNER_LIMIT = 1000;
    /* * * * * * * * */
    /* * FUNCTIONS * */
    /* * * * * * * * */

    function doFieldSignerUpdate(ind) {
        if (signers[ind]) {
            signers[ind].name = get(`SgnrNM${ind}`).value.trim();
            signers[ind].email = get(`SgnrEM${ind}`).value.toLowerCase().trim();
            get(`SgnrEM${ind}`).value = signers[ind].email;
        }
    }

    function doFieldIndexUpdate(signerInd, indexInd) {
        const indexKeys = Object.keys(uploadedDocs[currentDoc].indexfields);
        if (signers[signerInd]) {
            signers[signerInd].indexfields[indexKeys[indexInd]] = get(`Sgnr${signerInd}${indexKeys[indexInd]}`).value.trim();
        }
    }

    function removeSigner(num) {
        if (num == "all") {
            signers = [];
        } else if (num == "intAll") {
            signers = [];
            showSnackbar("All signers removed successfully.");
        } else {
            signers.splice(num, 1);
            showSnackbar("Signer removed successfully.");
        }
        if (signers.length == 0) {
            addBlankSigner();
        }
    }

    function focusOnEmptySignerCell() {
        for (let i = 0; i < signers.length; i++) {
            if (signers[i].name == "") {
                get(`SgnrNM${i}`).focus();
                return true;
            } else if (signers[i].email == "") {
                get(`SgnrEM${i}`).focus();
                return true;
            }
        }
    }

    function addBlankSigner() {
        const indexes = {};
        signerFields.forEach(key => {
            indexes[key] = "";
        });
        signers.unshift({
            id: "",
            signsetid: "",
            color: "#FFFFFF",
            name: "",
            email: "",
            authcode: "",
            selected: true,
            tier: "0",
            role: "",
            verifyrequired: "0",
            idprovider: "None",
            phone: "",
            notificationtype: notificationTypes[0].name,
            notificationaccount: "",
            paymentamount: "",
            paymentmessage: "",
            extramessagetext: "",
            extramessageaction: "Before",
            indexfields: indexes
        });
        setSignersIDs();
    }

    function hasInvalidRoleNames() {
        for (let r = 0; r < uploadedRoles.length; r++) {
            if (uploadedRoles[r].role.length < 3) {
                return true;
            }
        }
        return false;
    }

    function filterTemplateListByTags() {
        const filter = get("FilterSel").value;
        lastFilterTag = filter;
        const tp = lastTempType;
        lastTempType = "";
        getTemplateTypeFilterList(useNewDoc, tp, filter);
    }

    function uploadCSVSigners() {
        if (get("csvupload").value == "") {
            return false;
        }
        if (get("csvupload").files[0].size > MAX_FILE_SIZE) {
            get("csvupload").value = "";
            showError("File too large");
            return false;
        }
        pleaseWait("Uploading your document... ");
        get("UploadForm").submit();
    }

    function getCSVDataForIndex(cd, ind) {
        if (currentOrder[ind] == "") {
            return "";
        }
        return eval(`CSVData[${cd}].${currentOrder[ind]}`) ?? "";
    }

    function applySignerColumns() {
        hideMsg("SelectColumnHeadersModal");
        const indexFields = signerFields.slice(2);

        for (let sn = 0; sn < CSVData.length; sn++) {
            let name = getCSVDataForIndex(sn, 0);
            let email = getCSVDataForIndex(sn, 1);

            // If it's the first row, check if it's a header
            if (sn === 0) {
                // Don't add the first row of the CSV data to the signers array, If the first row is a header
                let isHeader = get("isheader").checked;
                if (isHeader) {
                    continue;
                }
            }
            //Don't import duplicate signers
            if (signers.find(signer => signer.name === name && signer.email === email)) continue;

            const indexes = {};
            let col = 2;
            for (let i = 0; i < indexFields.length; i++) {
                const key = indexFields[i];
                value = getCSVDataForIndex(sn, col);
                indexes[key] = value;
                col = col + 1;
            }
            signers.push({
                id: "",
                signsetid: "",
                color: "#FFFFFF",
                name: getCSVDataForIndex(sn, 0),
                email: getCSVDataForIndex(sn, 1),
                authcode: "",
                selected: true,
                tier: "0",
                role: get("RoleSelect").value,
                verifyrequired: "0",
                idprovider: "None",
                phone: "",
                notificationtype: notificationTypes[0].name,
                notificationaccount: "",
                paymentamount: "",
                paymentmessage: "",
                extramessagetext: "",
                extramessageaction: "Before",
                indexfields: indexes

            });
        }
        cleanSigners(signers);
        setSignersIDs();
        drawSigners();
    }

    function reorderFields(ind) {
        const tarCol = get(`ColSelNum${ind}`).value;
        if (tarCol == "") {
            currentOrder[ind] = "";
        } else {
            const tarInd = currentOrder.indexOf(tarCol);
            if (tarInd != -1) {
                [currentOrder[ind], currentOrder[tarInd]] = [currentOrder[tarInd], currentOrder[ind]];
            } else {
                currentOrder[ind] = tarCol;
            }
        }
        setCookie('SORDER', JSON.stringify(currentOrder), 360)
        drawSelectColumnHeadersModal();
    }

    function doSignerSearch(event, ind) {
        if (event.clientX) {
            if (event.target.getBoundingClientRect().x) {
                const clcx = event.clientX;
                const inptx = event.target.getBoundingClientRect().x;
                if (clcx - inptx > 30) {
                    return true;
                }
            } else {
                if (event.clientX > event.target.getBoundingClientRect().left) {
                    const clcx = event.clientX;
                    const inptx = event.target.getBoundingClientRect().left;
                    if (clcx - inptx > 30) {
                        return true;
                    }
                } else {
                    if (event.clientX > 36) {
                        return true;
                    }
                }
            }
        }
        srchSgnInd = ind;
        const sgnName = get(`SgnrNM${ind}`).value;
        if (sgnName.length >= 3) {
            searchName(sgnName);
        }
    }

    function selectSrchSgnr(ind) {
        get(`SgnrNM${srchSgnInd}`).value = slcSgnLst[ind].fullname;
        get(`SgnrEM${srchSgnInd}`).value = slcSgnLst[ind].email;
        modalFactory('SelectSignersModal').hide();
        doFieldSignerUpdate(srchSgnInd);
    }

    function checkForSignerSearchReturn(theEvent, ind) {
        const keyCode = theEvent.keyCode || theEvent.which;
        if ((keyCode == 13)) {
            doSignerSearch(theEvent, ind);
        }
        return true;
    }

    function checkForNewSignerReturn(theEvent, ind) {
        const keyCode = theEvent.keyCode || theEvent.which;
        if ((keyCode == 13)) {
            doFieldSignerUpdate(ind);
            checkForBlankSigner();
        }
        return true;
    }

    function validateSigners() {
        //here
        if (signers.length == 0) {
            showError("There must be at least one signer.");
            return false;
        }
        const emailsCount = {};
        for (let i = 0; i < signers.length; i++) {
            const msg = validSignerName(signers[i].name);
            const emsg = validateEmail(signers[i].email);
            const {
                [signers[i].email]: count = 0
            } = emailsCount;
            if (msg != "") {
                showError(msg, get(`SgnrNM${i}`));
                return false;
            } else if (!emsg) {
                showError("Email must be valid.", get(`SgnrEM${i}`));
                return false;
            } else if (count > 0) {
                showError("Duplicate email found", get(`SgnrEM${i}`));
                return false;
            }
            emailsCount[signers[i].email] = count + 1;
        }
        drawPostBulkSendModal();
    }

    const drawAddSignersPage = async () => {
        drawSigners();
        if (signers.length == 0) {
            addBlankSigner();
            drawSigners();
        }
        await loadOptionsRow();
    }

    /* * * * * * * * * * */
    /* * DRAW FUNCTIONS* */
    /* * * * * * * * * * */

    function drawFieldSelect(ind) {
        let selValCol = "";

        for (let c = 0; c < currentOrder.length; c++) {
            if (ind == c) {
                if (currentOrder[c] != "") {
                    selValCol = currentOrder[c];
                }
            }
        }
        let page = `<select id="ColSelNum${ind}" onchange="reorderFields(${ind})" class="form-select"><option value="">`;
        for (let co = 0; co < Object.keys(CSVData[0]).length; co++) {
            page += `<option value="col${(co + 1).toString()}"`;
            if (selValCol == `col${(co + 1).toString()}`) {
                page += ' selected';
            }
            page += `>${eval(`CSVData[0].col${(co + 1).toString()}`)}`;
        }
        page += '</select>';
        return page;
    }

    function drawSigners() {
        let indexKeys = [];
        if (uploadedDocs.length > 0) {
            indexKeys = Object.keys(uploadedDocs[currentDoc].indexfields);
        }
        let page = '<div><table cellpadding="2" cellspacing="0">';
        const usingIndexFields = indexKeys.length > 0;
        if (usingIndexFields) {
            page += `<tr><td class="SortSignerCellHeader" align="center" colspan="2">Signer Details</td>
            <td class="SortSignerCellHeader" align="center" colspan="${indexKeys.length+1}">Index Fields</td></tr>`;
        }
        // For whatever reason, the table draws correctly when I don't add a td element here to compensate for the trashcan icon ¯\_(ツ)_/¯
        // page += '<td width="27"></td>';
        page += '<tr>';
        // Use borders around signer headers when the index fields header is present
        const signerHeadersClass = usingIndexFields ? "SortSignerCellHeader" : "SortSignerCellAction";
        // Don't use redundant prefix "Signer" in signer headers when the index fields header is present
        const signerNameHeaderTxt = usingIndexFields ? "Name" : "Signer Name";
        page += `<td class="${signerHeadersClass}" width="200" style="padding:12px 0;" align="center">${signerNameHeaderTxt}</td>`;
        const signerEmailHeaderTxt = usingIndexFields ? "Email" : "Signer Email";
        page += `<td class="${signerHeadersClass}" width="200" align="center">${signerEmailHeaderTxt}</td>`;
        for (let i = 0; i < indexKeys.length - 1; i++) {
            page += `<td class="SortSignerCellHeader" width="200" align="center">${indexKeys[i]}</td>`;
        }
        if (indexKeys.length > 0) {
            page += `<td class="SortSignerCellHeader" width="200" align="center" colspan="2">${indexKeys[indexKeys.length-1]}</td>`;
        }
        // page += '<td width="27"></td>';
        page += '</tr>';
        for (let i = 0; i < signers.length; i++) {
            const {
                indexfields = {}
            } = signers[i];
            page += `<tr><td class="SortSignerCell" style="min-width:200px" align="center">
            <div class="input-group">
            <span class="input-group-text"><i class="fal fa-magnifying-glass"></i></span>
            <input autocomplete="off" onkeypress="checkForSignerSearchReturn(event,${i});" onchange="doFieldSignerUpdate(${i});" onclick="doSignerSearch(event,${i});" name="SgnrNM${i}" id="SgnrNM${i}" value="${signers[i].name}" class="form-control Overflow">
            </div></td>
            <td class="SortSignerCell" style="min-width:200px" align="center"><input autocomplete="off" onkeypress="checkForNewSignerReturn(event,${i});" onchange="doFieldSignerUpdate(${i});" name="SgnrEM${i}" id="SgnrEM${i}" value="${signers[i].email}" class="form-control Overflow"></td>`;
            for (let j = 0; j < indexKeys.length; j++) {
                const {
                    [indexKeys[j]]: val = ""
                } = indexfields;
                page += `<td class="SortSignerCell" style="min-width:150px" align="center"><input autocomplete="off" onchange="doFieldIndexUpdate(${i},${j});" name="Sgnr${i}${indexKeys[j]}" id="Sgnr${i}${indexKeys[j]}" value="${val} "class="form-control Overflow"></td>`;
            }

            page += `<td title="Remove Signer" class="SortSignerCell cursor" id="RemoveSignerBtn${i}" onclick="removeSigner(${i});drawSigners();" width="27" align="center"><i class="fas fa-trash-can FAStandardIcon"></i></td>`;
            // page += '<td>&nbsp;</td>';
            page += '</tr>';
        }

        page += '</table></div>';
        get("EditBulkSignersDiv").innerHTML = page;
        focusOnEmptySignerCell();
    }

    /** @type {Array} */
    let validloadedBulkSendSignerGroups = {};
    let loadedBulkSendSignerGroups = false;
    const loadOptionsRow = async () => {
        page = '<table cellpadding="0" cellspacing="0" height="100%" width="448px" align="center">';
        page += '<tr>';
        page += '<td width="50px" class="SearchHeader">Role:</td><td width="200px"><div id="RoleSelectDiv">';
        // Provide a select element to allow changing roles we allow submitting bulk sends out to.
        // We don't want to allow submitting hidden roles
        const rolesToAllowSubmitting = uploadedRoles.filter(role => parseInt(role.rolehidden) !== 1);
        // Don't allow changing input if there's only one role we allow submitting to available.
        if (rolesToAllowSubmitting.length == 1) {
            page += `<input class="form-control" id="RoleSelect" type="text" value="${rolesToAllowSubmitting[0].role}" disabled>`;
        } else if (rolesToAllowSubmitting.length > 1) {
            page += '<select class="form-select" id="RoleSelect">';
            for (let r = 0; r < rolesToAllowSubmitting.length; r++) {
                page += `<option value="${rolesToAllowSubmitting[r].role}">${rolesToAllowSubmitting[r].role}</option>`;
            }
            page += '</select>';
        } else {
            console.error("No roles available to submit bulk sends to");
        }
        page += '</div></td><td>&nbsp;</td>';
        page += '<td class="SortSignerCellAction cursor" id="AddBlankSgnBtn" onclick="addBlankSigner();drawSigners();" title="Add Blank Signer" style="color:grey;"><i class="fad fa-user-plus FAAddSignerIcon"></i></td>';
        page += '<td class="SortSignerCellAction cursor" id="UploadCSVBtn" title="Upload CSV" onclick=get("csvupload").click();><i class="fas fa-upload FAStandardIcon" style="color:grey"></i></td>';
        const buildSelectGrpBtn = async () => {
            const GENERIC_LOADING_OPTIONS_ERROR_MESSAGE = "An error occurred while loading signer options"
            let userErrorMessage;

            const aquireSignerGroupsErrorTitle = "Select From Group unavailable; Error aquiring Signer Groups";
            const noSignerGroupsErrorTitle = "Select From Group unavailable; No Signer Groups available to select from";
            const successTitleToUse = "Select From Group";
            let titleToUse = successTitleToUse;

            let iconClassToUse = "FAAddSignerIcon";
            const disabledIconClass = "FADisabledIcon";

            let foundGroupWithNoMembers = false;
            pleaseWait("Loading Signer Options...");
            try {
                //Aquire signer group data to determine how we want to display the signer group button and for use in the select signer group modal

                //Don't reaquire data from the backend if we already did it
                if (loadedBulkSendSignerGroups) return;

                const loadSignerGroups = async () => {
                    try {
                        const {
                            result,
                            error,
                            systemerror,
                            signergroups
                        } = await getListOfSignerGroups();

                        if (!result) {
                            logoutOnExpiredSession(error);
                            const responseError = new Error(`response result false: ${JSON.stringify({ error, systemerror })}`);
                            console.error(responseError);
                            if (!userErrorMessage) userErrorMessage = GENERIC_LOADING_OPTIONS_ERROR_MESSAGE;
                            throw responseError;
                        }

                        // Set signergroups from response
                        if (!signergroups) {
                            const signerGroupsError = new Error("signergroups not returned from request");
                            console.error(signerGroupsError);
                            if (!userErrorMessage) userErrorMessage = GENERIC_LOADING_OPTIONS_ERROR_MESSAGE;
                            throw signerGroupsError;
                        }
                        validloadedBulkSendSignerGroups = signergroups;
                    } catch (error) {
                        //When we couldn't properly aquire the list of signer groups, prevent selecting the signer group button, turn it grey, and display a tooltip explaining why
                        titleToUse = aquireSignerGroupsErrorTitle;
                        throw error;
                    }
                };
                await loadSignerGroups();

                //Prevent selecting the signer group button when there are no valid available groups
                if (validloadedBulkSendSignerGroups.length == 0) {
                    titleToUse = noSignerGroupsErrorTitle;
                    iconClassToUse = disabledIconClass;
                    loadedBulkSendSignerGroups = true;
                    return;
                }

                // # TODO: Too taxing on the backend to verify signers this way. Create an action on the backend to achieve this.
                //Verify which groups we're able to use
                // const verifySignersInGroups = async () => {
                //     try {
                //         for (let i = 0; i < validloadedBulkSendSignerGroups.length; i++) {
                //             const {
                //                 id
                //             } = validloadedBulkSendSignerGroups[i];
                //             const {
                //                 result,
                //                 error,
                //                 systemerror,
                //                 signers
                //             } = await getSignerGroupFromID(id);

                //             if (!result) {
                //                 logoutOnExpiredSession(error);
                //                 const responseError = new Error(`response result false: ${JSON.stringify({ error, systemerror })}`);
                //                 console.error(responseError);
                //                 if (!userErrorMessage) userErrorMessage = GENERIC_LOADING_OPTIONS_ERROR_MESSAGE;
                //                 throw responseError;
                //             }

                //             // throw a non-severe error if signers wasn't returned from the response or was invalid
                //             if (!signers || !Array.isArray(signers)) {
                //                 console.error(`signers not returned from request or not an array: ${JSON.stringify(signers)}`);
                //                 continue;
                //             }

                //             //Invalidate groups by removing them from our list of valid groups
                //             //Groups are considered invalid for selecting when there are no members in them.
                //             if (signers.length == 0) {
                //                 foundGroupWithNoMembers = true;
                //                 validloadedBulkSendSignerGroups.splice(i, 1);
                //                 i--;
                //             }
                //         }
                //     } catch (error) {
                //         //When we couldn't properly aquire the list of signers for any of the groups, prevent selecting the signer group button, turn it grey, and display a tooltip explaining why
                //         titleToUse = aquireSignerGroupsErrorTitle;
                //         iconClassToUse = disabledIconClass;
                //         throw error;
                //     }
                // };
                // await verifySignersInGroups();

                // //Prevent selecting the signer group button when there are no valid available groups
                // if (validloadedBulkSendSignerGroups.length == 0) {
                //     titleToUse = noSignerGroupsErrorTitle;
                //     iconClassToUse = disabledIconClass;
                // }

                loadedBulkSendSignerGroups = true;
            } catch (error) {
                iconClassToUse = disabledIconClass;
                if (!userErrorMessage) userErrorMessage = GENERIC_LOADING_OPTIONS_ERROR_MESSAGE;
                showError(userErrorMessage);
            } finally {
                pleaseWait("");

                //Add disclaimer to modal
                const setDisclaimer = () => {
                    const disclaimerElement = document.querySelector("#SelectSignerGroupModalDisclaimer");
                    if (!disclaimerElement) {
                        console.error("disclaimerElement not found");
                        return;
                    }
                    disclaimerElement.innerHTML = "*Some groups may have no members to select from";
                };
                // if (foundGroupWithNoMembers) {
                setDisclaimer();
                // }

                let elementAttribute = titleToUse === successTitleToUse ? ` onclick="${drawSelectSignerGroupModal.name}();"` : "";
                page += `<td class="SortSignerCellAction cursor" id="SelectGrpBtn" title="${titleToUse}"${elementAttribute}><i class="fad fa-users ${iconClassToUse}"></i></td>`;
            }
        };
        await buildSelectGrpBtn();
        page += '</tr></table>';

        get("OptionsRow").innerHTML = page;
    }

    function drawPostBulkSendModal() {
        $('#SignersLength').html(signers.length);
        modalFactory('PostBulkSendModal').show();
    }

    function drawSelectSignerGroupModal() {
        let page = "";
        for (let t = 0; t < validloadedBulkSendSignerGroups.length; t++) {
            page += `<option value="${validloadedBulkSendSignerGroups[t].id}">${validloadedBulkSendSignerGroups[t].name}`;
        }
        $('#groupSelect').html(page);
        modalFactory('SelectSignerGroupModal').show();
    }

    /* * * * * * * * */
    /* * REST CALLS* */
    /* * * * * * * * */

    function getTemplateTypeFilterList(newDoc, type, filter) {
        if (typeof newDoc == "undefined") {
            newDoc = useNewDoc;
        }
        if (typeof type == "undefined") {
            type = lastTempType;
        }
        if (typeof filter == "undefined") {
            filter = "";
        }
        useNewDoc = newDoc;
        if ((filteredTemplates.length > 0) && (type == lastTempType)) {
            showPage("BulkSendSetup");
        } else {
            lastTempType = type;
            if (!tagsLoaded) {
                tagsCBFunction = "getTemplateTypeFilterList";
                searchTags("");
                return;
            }
        }
        filteredTemplates = [];
        let categories = [];
        if (filter != "") {
            categories.push(filter);
        }
        const temp = {
            session: SID,
            controlid: CID,
            categories: categories,
            resource: "TEMPLATES",
            type: type,
            action: "GETLIST"
        };
        pleaseWait("Getting list of templates");
        doRestCall("common/rest.php", temp, processgetTemplateTypeFilterListJSON);
    }

    function processgetTemplateTypeFilterListJSON(theJSON) {
        pleaseWait("");
        const theData = JSON.parse(theJSON);
        if (!theData.result) {
            logout(theData.error);
            return;
        }

        if (theData.templates.length > 0) {
            for (let t = 0; t < theData.templates.length; t++) {
                filteredTemplates.push({
                    id: theData.templates[t].id,
                    name: theData.templates[t].name,
                    type: theData.templates[t].type,
                    redirecturl: theData.templates[t].redirecturl,
                    message: theData.templates[t].message,
                    createdby: theData.templates[t].createdby
                });
            }
            showPage("BulkSendSetup");
        } else {
            if (lastFilterTag == "") {
                modalFactory("NoTemplatesModal").show();
            } else {
                lastFilterTag = "";
                showError('There are no templates found for that tag');
            }
        }
    }

    /**
     * Retrieve and return the data for the list of signer groups from the backend
     * @returns {Promise<Array>} List of signer groups
     */
    const getListOfSignerGroups = async () => {
        let data = {};
        try {
            const temp = {
                session: SID,
                controlid: CID,
                host: "172.23.1.151", // # TODO: Test if ommitting this option still works
                resource: "SIGNERS",
                action: "GETGROUPS"
            };
            const response = await fetch("common/rest.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(temp),
            });
            data = (await response.json()) ?? {};
        } catch (error) {} finally {
            return data;
        }
    };

    /**
     * Retrieve and return the data for a signer group from the backend
     * @param {string} id
     * @returns {Promise<Array>}
     */
    const getSignerGroupFromID = async (id) => {
        let data = {};
        try {
            const temp = {
                session: SID,
                controlid: CID,
                resource: "SIGNERS",
                action: "GETSIGNERSINGROUP",
                groupid: id
            }
            const response = await fetch("common/rest.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(temp),
            });
            data = (await response.json()) ?? {};
        } catch (error) {} finally {
            return data;
        }
    };

    function deleteSearchSigner(id) {
        modalFactory('SelectSignersModal').hide();
        const temp = {
            session: SID,
            controlid: CID,
            action: "DELETE",
            signerid: id,
            resource: "SIGNERS"
        };
        pleaseWait("Deleting signer...");
        doRestCall("common/rest.php", temp, processDeleteSignersJSON);
    }

    function processDeleteSignersJSON(theJSON) {
        pleaseWait("");
        const theData = JSON.parse(theJSON);
        if (!theData.result) {
            logout(theData.error);
            return;
        }

        showSnackbar("Signer deleted successfully");
        searchName(lastSearchName);
    }

    function searchName(name) {
        lastSearchName = name;
        const temp = {
            session: SID,
            controlid: CID,
            action: "SEARCH",
            fullname: name,
            resource: "SIGNERS",
            returnsie: false
        };
        pleaseWait(`Searching for signers matching: ${name}`);
        doRestCall("common/rest.php", temp, processSearchSignersJSON);
    }

    function processSearchSignersJSON(theJSON) {
        pleaseWait("");
        const theData = JSON.parse(theJSON);
        if (!theData.result) {
            logout(theData.error);
            return;
        }

        if (theData.signers.length == 1) {
            get(`SgnrNM${srchSgnInd}`).value = theData.signers[0].fullname;
            get(`SgnrEM${srchSgnInd}`).value = theData.signers[0].email;
            doFieldSignerUpdate(srchSgnInd);
        } else if (theData.signers.length > 1) {
            slcSgnLst = theData.signers;
            let page = "";
            for (let s = 0; s < theData.signers.length; s++) {
                let clsRow = "ReportRow";
                if (s % 2 == 0) {
                    clsRow = "ReportRowOdd";
                }
                page += `<div class="row ${clsRow} cursor pt-1" id="SelectSrchSgnrBtn${s}">`;
                page += `<div class="col-5" onclick="selectSrchSgnr(${s})">${theData.signers[s].fullname}</div>`;
                page += `<div class="col-5" onclick="selectSrchSgnr(${s})">${theData.signers[s].email}</div>`;
                page += `<div class="col-2 text-center" id="DelSgn${s}" onclick="deleteSearchSigner('${theData.signers[s].id}')"><i class="fas fa-trash-can FAStandardIcon"></i></div></div>`;
            }
            $('#SignerSelectHolder').html(page);
            modalFactory('SelectSignersModal').show();
        }
    }

    function loadTemplateFromID(id) {
        let eltm = "";
        if (id) {
            eltm = id;
        } else {
            eltm = currentBulkSend.otherid;
        }
        if (eltm == "None") {
            if (uploadType == 6) {
                uploadedDocs[docInd].indexdata = [];
                uploadedDocs[docInd].templateused = "";
                uploadedDocs[docInd].targettable = "";
                uploadedDocs[docInd].sigboxes = [];
                uploadedDocs[docInd].sharing = [];
                uploadedDocs[docInd].signers = [];
                uploadedDocs[docInd].roles = [];
                uploadedDocs[docInd].triggers = [];
                recreateRoleList();
                redrawDocList();
            }
            return;
        }
        templateID = eltm;
        for (let t = 0; t < templates.length; t++) {
            if (templates[t].id == templateID) {
                if (templates[t].type == "Requested") {
                    requestedDocs.push({
                        docid: "",
                        pkgid: "",
                        formname: templates[t].name,
                        message: templates[t].message,
                        signers: []
                    });
                    return true;
                }
            }
        }
        const temp = {
            session: SID,
            controlid: CID,
            resource: "TEMPLATES",
            pkgid: "",
            action: "EDIT",
            templateid: eltm,
            sending: true,
        };
        pleaseWait("Loading template");
        if (uploadType == 6) {
            uploadType = 1;
            hideMsgDialog();
            doRestCall("common/rest.php", temp, processLoadMatchingTemplateFromJSON);
        } else {
            doRestCall("common/rest.php", temp, processLoadTemplateFromIDJSON);

            if (useNewDoc) {
                get("fileupload").click();
            }
        }
    }

    function processLoadTemplateFromIDJSON(theJSON) {
        pleaseWait("");
        let theData = JSON.parse(theJSON);
        if (!theData.result) {
            logout(theData.error);
        } else {
            if (uploadType == 3) {
                loadRefTemplate(theData);
                return true;
            }
            let tempIndexData = [];
            const indexFields = theData.indexfields ?? [];
            for (var theName in indexFields) {
                tempIndexData.push({
                    name: theName,
                    value: indexFields[theName]
                });
            }
            uploadedDocs.push({
                pdf: tempDir + theData.graphic,
                templateused: getTemplateName(templateID),
                formname: theData.form,
                notificationname: theData.notificationname,
                notificationemail: theData.notificationemail,
                redirecturl: theData.redirecturl,
                targettable: theData.targettable,
                pagecount: 0,
                sigboxes: [],
                pkgid: 0,
                docid: templateID,
                sharing: theData.sharingids,
                sendcopy: 0,
                signers: [],
                triggers: [],
                images: [],
                indexdata: tempIndexData,
                uploadtype: uploadType,
                status: theData.status,
                indexfields: indexFields
            });
            const indexKeys = Object.keys(indexFields);
            for (let i = 0; i < indexKeys.length; i++) {
                if (indexKeys[i] !== "Signer Name") {
                    const indexNum = i + 3;
                    if (!signerFields.includes(indexKeys[i])) {
                        signerFields.push(indexKeys[i]);
                        currentOrder.push(`col${indexNum}`);
                    }
                }
            }
            uploadedRoles = theData.signsets;
            if (theData.signers) {
                for (let k = 0; k < theData.signsets.length; k++) {
                    for (let j = 0; j < theData.signers.length; j++) {
                        if (theData.signsets[k].role == theData.signers[j].role) {
                            theData.signsets[k].name = theData.signers[j].name;
                            theData.signsets[k].email = theData.signers[j].email;
                        }
                    }
                }
            }
            if (theData.redirecturl != "") {
                defaultRURL = theData.redirecturl;
            }
            if (theData.notificationname != "") {
                defaultNotificationName = theData.notificationname;
                defaultNotificationEmail = theData.notificationemail;
            }
            currentDoc = uploadedDocs.length - 1;
            createRoleList(theData.signsets);
            for (let s = 0; s < theData.sigboxes.length; s++) {
                const len = uploadedDocs[currentDoc].sigboxes.push({
                    x: 0,
                    y: 0,
                    w: 0,
                    h: 0,
                    xp: parseFloat(theData.sigboxes[s].left),
                    yp: parseFloat(theData.sigboxes[s].top),
                    wp: parseFloat(theData.sigboxes[s].width),
                    hp: parseFloat(theData.sigboxes[s].height),
                    maxt: 0,
                    maxl: 0,
                    maxx: 0,
                    maxy: 0,
                    id: theData.sigboxes[s].boxid,
                    type: parseInt(theData.sigboxes[s].esigntype),
                    page: parseInt(theData.sigboxes[s].pagenumber),
                    docnum: 0,
                    font: theData.sigboxes[s].font,
                    fontcolor: theData.sigboxes[s].fontcolor,
                    fontsize: parseInt(theData.sigboxes[s].fontsize),
                    fieldname: theData.sigboxes[s].fieldname,
                    fieldvalue: theData.sigboxes[s].fieldvalue,
                    fieldlabel: theData.sigboxes[s].fieldlabel,
                    defaultvalue: theData.sigboxes[s].defaultvalue,
                    required: theData.sigboxes[s].fieldrequired,
                    autofillfield: theData.sigboxes[s].autofillfield || '',
                    checkedvalue: theData.sigboxes[s].checkedvalue,
                    uncheckedvalue: theData.sigboxes[s].uncheckedvalue,
                    depfield: theData.sigboxes[s].depfield,
                    depfieldvalue: theData.sigboxes[s].depfieldvalue,
                    depoperator: theData.sigboxes[s].depoperator,
                    signer: new Signer
                });
                uploadedDocs[currentDoc].sigboxes[len - 1].signer.signsetid = theData.sigboxes[s].signsetid;
            }
            if (theData.pkgname) {
                packageName = theData.pkgname;
                get("PckNameEdt").value = packageName;
            }
            for (let i = 0; i < uploadedDocs[currentDoc].sigboxes.length; i++) {
                if (theData.fields) {
                    if (theData.fields[uploadedDocs[currentDoc].sigboxes[i].fieldname]) {
                        if (uploadedDocs[currentDoc].sigboxes[i].type == 3) {
                            uploadedDocs[currentDoc].sigboxes[i].defaultvalue = theData.fields[uploadedDocs[currentDoc].sigboxes[i].fieldname];
                            uploadedDocs[currentDoc].sigboxes[i].fieldvalue = theData.fields[uploadedDocs[currentDoc].sigboxes[i].fieldname];
                        } else if (uploadedDocs[currentDoc].sigboxes[i].type == 4 || uploadedDocs[currentDoc].sigboxes[i].type == 5) {
                            if (uploadedDocs[currentDoc].sigboxes[i].checkedvalue == theData.fields[uploadedDocs[currentDoc].sigboxes[i].fieldname]) {
                                uploadedDocs[currentDoc].sigboxes[i].defaultvalue = "1";
                                uploadedDocs[currentDoc].sigboxes[i].fieldvalue = "Checked";
                            }
                        }
                    }
                }
                for (let j = 0; j < uploadedRoles.length; j++) {
                    if (uploadedDocs[currentDoc].sigboxes[i].signer.signsetid == uploadedRoles[j].signsetid) {
                        if (uploadedRoles[j].signsetid !== "USERFIELD") {
                            uploadedDocs[currentDoc].sigboxes[i].signer = uploadedRoles[j];
                        }
                    }
                }
            }
            if (theData.fields) {
                for (let key in theData.fields) {
                    uploadedDocs[currentDoc].indexdata.push({
                        name: key,
                        value: theData.fields[key]
                    });
                }
            }
            if (theData.triggerdefs) {
                for (let t = 0; t < theData.triggerdefs.length; t++) {
                    uploadedDocs[currentDoc].triggers.push(getDocTriggerFromDef(theData.triggerdefs[t]));
                }
            }
            if (hasInvalidRoleNames()) {
                const page = '<div class="alert alert-danger alert-dismissible fade show" role="alert">This Template has invalid roles. Please select a different template.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                $("#alertHolder").append(page);
                return false;
            } else {
                showPage("AddSigners");
            }
        }
    }

    function processLoadMatchingTemplateFromJSON(theJSON) {
        pleaseWait("");
        let templateName = getTemplateNameFromMatches(templateID);
        if (templateName == "") {
            templateName = getTemplateName(templateID);
        }
        let theData = JSON.parse(theJSON);
        if (!theData.result) {
            logout(theData.error);
        } else {
            let tempIndexData = [];
            if (uploadedDocs[docInd].indexdata.length == 0) {
                for (let theName in theData.indexfields) {
                    tempIndexData.push({
                        name: theName,
                        value: theData.indexfields[theName]
                    });
                }
                uploadedDocs[docInd].indexdata = tempIndexData;
            }
            uploadedDocs[docInd].formname = theData.form;
            uploadedDocs[docInd].templateused = templateName;
            uploadedDocs[docInd].notificationname = theData.notificationname;
            uploadedDocs[docInd].notificationemail = theData.notificationemail;
            uploadedDocs[docInd].targettable = theData.targettable;
            uploadedDocs[docInd].sigboxes = [];
            uploadedDocs[docInd].sharing = theData.sharingids;
            uploadedDocs[docInd].signers = [];
            uploadedDocs[docInd].triggers = [];
            uploadedRoles = theData.signsets;
            if (theData.signers) {
                for (let k = 0; k < theData.signsets.length; k++) {
                    for (let j = 0; j < theData.signers.length; j++) {
                        if (theData.signsets[k].role == theData.signers[j].role) {
                            theData.signsets[k].name = theData.signers[j].name;
                            theData.signsets[k].email = theData.signers[j].email;
                        }
                    }
                }
            }
            if (theData.redirecturl != "") {
                defaultRURL = theData.redirecturl;
            }
            if (theData.notificationname != "") {
                defaultNotificationName = theData.notificationname;
                defaultNotificationEmail = theData.notificationemail;
            }
            createRoleList(theData.signsets);
            for (let s = 0; s < theData.sigboxes.length; s++) {
                const len = uploadedDocs[docInd].sigboxes.push({
                    x: 0,
                    y: 0,
                    w: 0,
                    h: 0,
                    xp: parseFloat(theData.sigboxes[s].left),
                    yp: parseFloat(theData.sigboxes[s].top),
                    wp: parseFloat(theData.sigboxes[s].width),
                    hp: parseFloat(theData.sigboxes[s].height),
                    maxt: 0,
                    maxl: 0,
                    maxx: 0,
                    maxy: 0,
                    id: theData.sigboxes[s].boxid,
                    type: parseInt(theData.sigboxes[s].esigntype),
                    page: parseInt(theData.sigboxes[s].pagenumber),
                    docnum: 0,
                    font: theData.sigboxes[s].font,
                    fontcolor: theData.sigboxes[s].fontcolor,
                    fontsize: parseInt(theData.sigboxes[s].fontsize),
                    fieldname: theData.sigboxes[s].fieldname,
                    fieldvalue: theData.sigboxes[s].fieldvalue,
                    fieldlabel: theData.sigboxes[s].fieldlabel,
                    defaultvalue: theData.sigboxes[s].defaultvalue,
                    required: theData.sigboxes[s].fieldrequired,
                    autofillfield: theData.sigboxes[s].autofillfield || '',
                    checkedvalue: theData.sigboxes[s].checkedvalue,
                    uncheckedvalue: theData.sigboxes[s].uncheckedvalue,
                    depfield: theData.sigboxes[s].depfield,
                    depfieldvalue: theData.sigboxes[s].depfieldvalue,
                    depoperator: theData.sigboxes[s].depoperator,
                    signer: new Signer
                });
                uploadedDocs[docInd].sigboxes[len - 1].signer.signsetid = theData.sigboxes[s].signsetid;
            }
            for (let i = 0; i < uploadedDocs[docInd].sigboxes.length; i++) {
                if (theData.fields) {
                    if (theData.fields[uploadedDocs[docInd].sigboxes[i].fieldname]) {
                        if (uploadedDocs[docInd].sigboxes[i].type == 3) {
                            uploadedDocs[docInd].sigboxes[i].defaultvalue = theData.fields[uploadedDocs[docInd].sigboxes[i].fieldname];
                            uploadedDocs[docInd].sigboxes[i].fieldvalue = theData.fields[uploadedDocs[docInd].sigboxes[i].fieldname];
                        } else if (uploadedDocs[docInd].sigboxes[i].type == 4 || uploadedDocs[docInd].sigboxes[i].type == 5) {
                            if (uploadedDocs[docInd].sigboxes[i].checkedvalue == theData.fields[uploadedDocs[docInd].sigboxes[i].fieldname]) {
                                uploadedDocs[docInd].sigboxes[i].defaultvalue = "1";
                                uploadedDocs[docInd].sigboxes[i].fieldvalue = "Checked";
                            }
                        }
                    }
                }
                for (let j = 0; j < uploadedRoles.length; j++) {
                    if (uploadedDocs[docInd].sigboxes[i].signer.signsetid == uploadedRoles[j].signsetid) {
                        if (uploadedRoles[j].signsetid !== "USERFIELD") {
                            uploadedDocs[docInd].sigboxes[i].signer = uploadedRoles[j];
                        }
                    }
                }
            }
            if (theData.fields) {
                for (let key in theData.fields) {
                    uploadedDocs[docInd].indexdata.push({
                        name: key,
                        value: theData.fields[key]
                    });
                }
            }
            if (theData.triggerdefs) {
                for (let t = 0; t < theData.triggerdefs.length; t++) {
                    uploadedDocs[docInd].triggers.push(getDocTriggerFromDef(theData.triggerdefs[t]));
                }
            }
            if (hasInvalidRoleNames()) {
                const page = '<div class="alert alert-danger alert-dismissible fade show" role="alert" width="50%">This Template has invalid roles. Please select a different template.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                $("#alertHolder").append(page);
                return false;
            } else {
                showPage("AddSigners");
            }
        }
    }

    function getAndDrawGroupFromID() {
        // # TODO: Move loading group members into loadOptionsRow
        function processgetAndDrawGroupFromIDJSON(theJSON) {
            pleaseWait("");
            const theData = JSON.parse(theJSON);
            if (!theData.result) {
                logout(theData.error);
            } else {
                for (let sn = 0; sn < theData.signers.length; sn++) {
                    signers.push({
                        id: "",
                        signsetid: "",
                        color: "#FFFFFF",
                        name: theData.signers[sn].name,
                        email: theData.signers[sn].email,
                        authcode: "",
                        selected: true,
                        tier: "0",
                        role: "",
                        verifyrequired: "0",
                        idprovider: "None",
                        phone: "",
                        notificationtype: notificationTypes[0].name,
                        notificationaccount: "",
                        paymentamount: "",
                        paymentmessage: "",
                        extramessagetext: "",
                        extramessageaction: "Before",
                        indexfields: {}
                    });
                }
                cleanSigners(signers);
                modalFactory('SelectSignerGroupModal').hide();
                setSignersIDs();
                drawSigners();
            }
        }
        const temp = {
            session: SID,
            controlid: CID,
            resource: "SIGNERS",
            action: "GETSIGNERSINGROUP",
            groupid: get("groupSelect").value
        }
        hideMsg('SelectSignersGroupModal');
        pleaseWait("Getting Signers...");
        doRestCall("common/rest.php", temp, processgetAndDrawGroupFromIDJSON);
    }

    function cleanSigners(signers) {
        // Remove all leading entries with empty 'name' and 'email'
        while (signers.length > 0 && signers[0].name === "" && signers[0].email === "") {
            signers.shift();
        }
    }

    function uploadPPMsg(msg, fileName, uniqueName) {
        pleaseWait("");
        if (msg != "") {
            setError(msg);
            return false;
        }
        const temp = {
            session: SID,
            controlid: CID,
            filename: fileName,
            name: uniqueName
        };
        pleaseWait("Reading csv...   ");
        doRestCall("uploadcsv.php", temp, processReadCSVJSON);
    }

    function processReadCSVJSON(theJSON) {
        pleaseWait("");
        get("csvupload").value = "";
        const theData = JSON.parse(theJSON);
        if (!theData.result) {
            alert(theData.error);
        } else {
            CSVData = theData.csv;
            drawSelectColumnHeadersModal();
        }
    }

    async function postBulkSend() {
        // Prepare signer data for bulk send
        pleaseWait("Saving Bulk Send");
        let successfullyCreatedBulkSend = false;
        try {
            let signersToSend = [];
            for (let i = 0; i < signers.length; i++) {
                const {
                    indexfields = {}, name, email
                } = signers[i];
                delete indexfields.Email;
                delete indexfields['Full Name'];
                signersToSend.push({
                    role: get("RoleSelect").value,
                    name,
                    email,
                    indexfields,
                });
            }
            // Split the signers into chunks of 1000 signers each for adding to the bulk send in follow-up requests
            const signerChunks = [];
            if (signersToSend.length > MAX_SIGNER_LIMIT) {
                while (signersToSend.length > 0) {
                    signerChunks.push(signersToSend.splice(0, MAX_SIGNER_LIMIT));
                }
            } else {
                signerChunks.push(signersToSend)
            }
            // Send a request to create the bulk send
            const bulkSendData = {
                templateid: currentBulkSend.otherid,
                signers: signerChunks[0], //include the first 1000 signers
                userid: currentBulkSend.id,
                packagename: currentBulkSend.name,
                daytimetorun: currentBulkSend.nexttest,
                subject: currentBulkSend.description,
                body: currentBulkSend.criteria,
                expireafter: currentBulkSend.lastexecuted
            };
            const {
                result,
                error,
                id,
            } = await doeServiceCall({
                url: 'api/bulksend/create.php',
                function: 'createBulkSend',
                ...bulkSendData
            });
            if (!result) {
                console.error(error);
                throw new Error(error);
            }

            // Send subsequent requests to add overflow signers
            const bulkSendId = id; // Store the bulk send id in a separate variable
            const promises = signerChunks.slice(1).map(signerChunk => {
                return (async () => {
                    const addSignersData = {
                        id: bulkSendId, // Use the stored bulk send id
                        signers: signerChunk,
                    }
                    const {
                        result,
                        error,
                    } = await doeServiceCall({
                        url: 'api/bulksend/create.php',
                        function: 'addSigners',
                        ...addSignersData
                    });
                    if (!result) {
                        console.error(error);
                        throw new Error(error);
                    }
                })();
            });
            const requestPromiseResults = await Promise.allSettled(promises);
            // Handle any rejected promises
            requestPromiseResults.forEach(result => {
                if (result.status === 'rejected') {
                    throw new Error(result.reason);
                }
            });

            successfullyCreatedBulkSend = true;
        } catch (error) {
            showError("An error occurred while attempting to submit the bulk send.");
        } finally {
            pleaseWait("");

            //Don't redirect if the bulk send was not successfully created
            if (!successfullyCreatedBulkSend) return;

            // Clear bulk send creating-related variables now that the bulk send has been successfully created
            templates = [];
            lastFilterTag = "";
            forgetBulkSend();
            // Redirect to the bulk send results page
            showPage("DocSearchResults");
            if (signers.length < MAX_SIGNER_LIMIT) {
                showSnackbar("Bulk send successfully started.");
            } else {
                drawInfoModal("Bulk send successfully started. It may take a few minutes to appear in the active table.");
            }
            signers = [];
        }
    }
</script>
<iframe class="hidden" id="upload_target" name="upload_target" style="width:0;height:0;border:0px solid #fff;"></iframe>
<form id="UploadForm" action="uploadcsv.php" method="post" enctype="multipart/form-data" target="upload_target" class="hidden">
    <input id="SID" name="SID" value="<?= $SID ?>" type="hidden">
    <input class="hidden" name="csvupload" id="csvupload" onchange="uploadCSVSigners();" accept=".csv" type="file">
</form>
<table cellpadding="0" cellspacing="0" width="100%" class="hidden" height="100%" id="BulkAddTable">
    <tr>
        <td class="navrow" align="center" height="40px" width="100%">
            <table height="40px" width="100%">
                <tr>
                    <td width="20px">&nbsp;</td>
                    <td width="80px">
                        <div id="DocResBackCell" title="Return to main menu" class="ediBtn navBack" onclick='showPage("BulkSendSetupBack");'>Back</div>
                    </td>
                    <td width="20px">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td width="10px">&nbsp;</td>
                    <td align="center" width="140px">
                        <div id="RemoveAllBtn" title="Remove All Signers" onclick="modalFactory('GroupsCancelModal').show()" class="ediBtn navDelete" style="width:140px">Remove All</div>
                    </td>
                    <td width="10px">&nbsp;</td>
                    <td align="center" width="80px">
                        <div id="SendBtn" title="Send" onclick="validateSigners();" class="ediBtn navNext">Send</div>
                    </td>
                    <td width="20px">&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr height="10px">
        <td>&nbsp;</td>
    </tr>
    <tr id="SelectionRow" height="40px">
        <td valign="top" align="left">
            <div id="OptionsRow"></div>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
    </tr>

    <tr id="SignSearchRow" height="100%">
        <td height="100%" valign="top" align="center">
            <table cellspacing="0" height="100%" width="100%">
                <tr>
                    <td valign="top" align="center">
                        <table width="100%" height="100%">
                            <tr>
                                <td align="center" valign="top">
                                    <div id="EditBulkSignersDiv" style="max-width: 95vw; overflow-x: auto;">&nbsp;</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>