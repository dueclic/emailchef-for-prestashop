var PS_Emailchef = (function () {

    var namespace = 'ps_emailchef';
    var i18n = {};

    function prefixed_setting(suffix) {
        return namespace + "_" + suffix;
    }

    function showElement(selectorOrElement, displayType = 'block') {
        const element = typeof selectorOrElement === 'string' ? document.querySelector(selectorOrElement) : selectorOrElement;
        if (element) {
            element.style.display = displayType;
        }
    }

    function hideElement(selectorOrElement) {
        const element = typeof selectorOrElement === 'string' ? document.querySelector(selectorOrElement) : selectorOrElement;
        if (element) {
            element.style.display = 'none';
        }
    }

    function showElements(selector, displayType = 'block') {
        document.querySelectorAll(selector).forEach(el => el.style.display = displayType);
    }

    function hideElements(selector) {
        document.querySelectorAll(selector).forEach(el => el.style.display = 'none');
    }

    function toggleElement(selectorOrElement, displayType = 'block') {
        const element = typeof selectorOrElement === 'string' ? document.querySelector(selectorOrElement) : selectorOrElement;
        if (element) {
            if (window.getComputedStyle(element).display === 'none') {
                element.style.display = displayType;
            } else {
                element.style.display = 'none';
            }
        }
    }

    function setButtonDisabledState(selector, disabled) {
        document.querySelectorAll(selector).forEach(button => button.disabled = disabled);
    }

    async function makeAjaxRequest(url, data) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseData = await response.json();
            return responseData;

        } catch (error) {
            console.error('Fetch Error:', error);
            return { type: 'error', msg: error.message || 'Network or parsing error' };
        }
    }

    function loginPage() {
        var showPasswordButton = document.getElementById('showPassword');
        var hidePasswordButton = document.getElementById('hidePassword');
        var consumerSecretInput = document.getElementById("consumer_secret");

        if (showPasswordButton && hidePasswordButton && consumerSecretInput) {
            showPasswordButton.addEventListener('click', () => {
                consumerSecretInput.setAttribute('type', 'text');
                showPasswordButton.style.display = 'none';
                hidePasswordButton.style.display = 'flex';
            });

            hidePasswordButton.addEventListener('click', () => {
                consumerSecretInput.setAttribute('type', 'password');
                showPasswordButton.style.display = 'flex';
                hidePasswordButton.style.display = 'none';
            });
        }
    }

    async function addList(listName, listDesc) {

        hideElements(".status-list");
        hideElements(".status-list-cf");

        const containerSelector = ".ecps-new-list-container";
        const containerElement = document.querySelector(containerSelector);
        if (!containerElement) return;

        setButtonDisabledState(containerSelector + " button", true);

        var ajax_data = {
            action: 'emailchefaddlist',
            list_name: listName,
            list_desc: listDesc
        };

        var ajax_url = containerElement.dataset.ajaxUrl;
        var selList = document.getElementById(prefixed_setting('list'));

        //hideElements(".status-list");
        showElements(".check-list"/*, 'inline-block'*/);

        const response = await makeAjaxRequest(ajax_url, ajax_data);

        hideElements(".check-list");

        if (response.type == 'error') {
            hideElements(".status-list");
            const errorStatusList = document.getElementById("error_status_list_data");
            if (errorStatusList) {
                const reasonElement = errorStatusList.querySelector(".reason");
                if (reasonElement) {
                    reasonElement.textContent = response.msg;
                }
                showElement(errorStatusList);
            }
            setButtonDisabledState(containerSelector + " button", false);
            return;
        }

        const newSaveButton = containerElement.querySelector("#" + prefixed_setting('new_save'));
        if (newSaveButton) {
            newSaveButton.textContent = i18n.create_list;
        }

        const successStatusList = document.getElementById("success_status_list_data");
        if (successStatusList) {
            showElement(successStatusList);
            //setTimeout(() => hideElement(successStatusList), 3000);
        }

        if (response.list_id !== undefined && selList) {
            const newOption = document.createElement('option');
            newOption.textContent = listName;
            newOption.value = response.list_id;
            selList.appendChild(newOption);
            selList.value = response.list_id;
        }

        await createCustomFields(response.list_id);

    }

    async function createCustomFields(listId) {

        const containerSelector = ".ecps-new-list-container";
        const containerElement = document.querySelector(containerSelector);
        if (!containerElement) return;

        var ajax_data = {
            action: 'emailchefaddcustomfields',
            list_id: listId
        };

        var ajax_url = containerElement.dataset.ajaxUrl;

        hideElements(".status-list-cf");
        showElements(".check-list-cf" /*, 'inline-block'*/);

        const response = await makeAjaxRequest(ajax_url, ajax_data);

        hideElements(".check-list-cf");

        if (response.type == 'error') {
            hideElements(".status-list-cf");
            const errorStatusListCf = document.getElementById("error_status_list_data_cf");
            if (errorStatusListCf) {
                const reasonElement = errorStatusListCf.querySelector(".reason");
                if (reasonElement) {
                    reasonElement.textContent = response.msg;
                }
                showElement(errorStatusListCf);
            }
            setButtonDisabledState(containerSelector + " button", false);
            return;
        }

        const successStatusListCf = document.getElementById("success_status_list_data_cf");
        if (successStatusListCf) {
            showElement(successStatusListCf);
            //setTimeout(() => hideElement(successStatusListCf), 3000);
        }

        //hideElement(containerElement);

        setButtonDisabledState(containerSelector + " button", false);

    }

    async function manualSync(suppressAlerts = false) {

        var syncButton = document.getElementById(prefixed_setting('sync_now'));
        if (!syncButton) return;

        syncButton.disabled = true;

        var ajax_data = {
            action: 'emailchefsync'
        };

        var ajax_url = syncButton.dataset.ajaxUrl;

        const response = await makeAjaxRequest(ajax_url, ajax_data);

        syncButton.disabled = false;

        if (response.status === 'success' && !suppressAlerts) {
            alert(response.msg);
        }
    }

    async function disconnectAccount() {

        var disconnectButton = document.getElementById("emailchef-disconnect");
        if (!disconnectButton) return;

        disconnectButton.disabled = true;

        var ajax_data = {
            action: 'emailchefdisconnect'
        };

        var ajax_url = disconnectButton.dataset.ajaxUrl;

        const response = await makeAjaxRequest(ajax_url, ajax_data);

        if (response) {
            window.location.reload();
        } else {
            disconnectButton.disabled = false;
        }
    }

    function settings(_i18n, doManualSync = false) {

        i18n = _i18n;

        const createListButton = document.getElementById(prefixed_setting('create_list'));
        const newListContainer = document.querySelector(".ecps-new-list-container");
        const undoSaveButton = document.querySelector(".ecps-new-list-container #" + prefixed_setting('undo_save'));
        const newSaveButton = document.querySelector(".ecps-new-list-container #" + prefixed_setting('new_save'));
        const syncNowButton = document.getElementById(prefixed_setting('sync_now'));
        const disconnectButton = document.getElementById('emailchef-disconnect');
        const newListNameInput = document.getElementById(prefixed_setting("new_name"));
        const newListDescInput = document.getElementById(prefixed_setting("new_description"));

        if (createListButton && newListContainer) {
            createListButton.addEventListener("click", function (evt) {
                evt.preventDefault();
                toggleElement(newListContainer);
            });
        }

        if (undoSaveButton && newListContainer) {
            undoSaveButton.addEventListener("click", function (evt) {
                evt.preventDefault();
                hideElement(newListContainer);
            });
        }

        if (newSaveButton && newListNameInput && newListDescInput) {
            newSaveButton.addEventListener("click", function (evt) {
                evt.preventDefault();
                addList(
                    newListNameInput.value,
                    newListDescInput.value
                );
            });
        }

        if (syncNowButton) {
            syncNowButton.addEventListener("click", function (evt) {
                evt.preventDefault();
                manualSync();
            });
        }

        if (disconnectButton) {
            disconnectButton.addEventListener("click", function (evt) {
                evt.preventDefault();
                if (confirm(i18n.are_you_sure_disconnect)) {
                    disconnectAccount();
                }
            });
        }

        if (doManualSync) {
            manualSync(true);
        }
    }

    return {
        loginPage: loginPage,
        settings: settings
    };

})();
