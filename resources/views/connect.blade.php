<HTML>

<head>
    <link rel="stylesheet" href="https://plugin.ziwo.io/ziwo.styles.css" media="print" onload="this.media='all'">
    <title>CRM Connection</title>
    <style>
        body {
            max-width: 300px !important;
        }

        ziwo-bottom-bar {
            display: none;
        }

        ziwo-bottom-bar .bottom-bar {
            display: flex;
            align-content: center;
            justify-content: center;
            align-items: center;

        }
    </style>
</head>

<body>

    <ziwo-root></ziwo-root>
    <script src="https://plugin.ziwo.io/ziwo.plugin.js" type="module"></script>
    <script src="https://plugin.ziwo.io/ziwo.polyfills.js" type="module"></script>

</body>
<script>
    let locationId = "JoqQ51Bl3LEmR42l6LrG";
    let contactId = null;
    let contactName = '';
    let contactEmail = '';
    let conversationId = null;
    let currentUser = {};
    let callStatus = {
        "CALL_REJECTED": 'failed',
        "ORIGINATOR_CANCEL": "canceled",
        "NORMAL_CLEARING": "completed"
    }

    function sendPostMessage(data, source = null) {
        if (!source) {
            source = window.parent;
        }
        source.postMessage(data, '*');
    }
    function generateNote(call) {
        function htm(det) {
            return "<br/>" + det;
        }
        let noteBody = "Call ID : " + call.callId;
        noteBody += htm("Date : " + new Date().toDateString());
        noteBody += htm("From : ");
        noteBody += htm("Direction : " + call.direction);
        return noteBody;
    }
    function sendCallToBackend(call, lcause = "") {
        if (call && call.callId) {
            let cause = call.cause ?? lcause;
            let status = callStatus[cause] ?? "completed";
            let inBound = call.direction == 'inbound';
            let callerNumber = inBound ? call.origin : call.participants[0];
            let phone = !inBound ? contactPhone : callerNumber.number ?? "";
            contactName = inBound ? '' : contactName;
            let noteBody = generateNote(call);
            if (phone != '') {
                let callPayload = {
                    call_id: call.callId,
                    location_id: locationId,
                    contact_id: contactId,
                    contact_phone: phone,
                    contact_name: contactName,
                    user_id: currentUser.id ?? "",
                    contact_type: "contact",
                    note_body: noteBody,
                    user_name: currentUser.name ?? "",
                    user_email: currentUser.email ?? "",
                    call_direction: call.direction,
                    call_status: status,
                    call_duration: "",
                    conversation_id: conversationId,
                    call_cause: cause,
                    attachment: call.recording ?? ""
                }
                makeAPICall("submit-call-response", callPayload);
            }
        }
    }

    function validateContact(contactId) {
        return contactId.split('&')[0] ?? ""
    }
    function outBoundCall(phone) {
        try {
            ZIWO.calls.startCall(phone);
            sendPostMessage({
                type: "outgoing_call",
            });

        } catch (error) {
            sendPostMessage({ type: "no_phone" });
        }
    }

    function initZIWO() {


        window.addEventListener('ziwo-call-all', (e) => {
            let detail = e.detail;

            if (detail.type == "ringing") {
                sendPostMessage({
                    type: "incoming_call",
                });
            }

            if (detail.type == "hangup") {
                if (["NORMAL_CLEARING", "CALL_REJECTED"].includes(detail.cause)) {
                    sendCallToBackend(detail.call ?? null, detail.cause);
                }
                setTimeout(function () {
                    sendPostMessage({
                        type: "call_end",
                    });
                }, 12500);

            }
            console.log('Triggered by any call event', e.detail)

        });

        window.addEventListener('message', function (e) {
            let data = e.data ?? e.detail ?? null;
            if (data) {
                if (data.action == 'call') {

                    if (data.locationId) {
                        locationId = data.locationId;
                    }
                    contactPhone = data.phone ?? '';
                    if (contactPhone != '') {
                        outBoundCall(contactPhone);
                    } else {
                        getContact(data.contactId ?? "").then(x => {
                            outBoundCall(x.phone);
                        }).catch(x => {
                            sendPostMessage({ type: "no_phone" });
                        });
                    }

                }
                else if (data.action == 'needData') {
                    contactId = data.contactId ?? "";
                    getContact(contactId).then(x => {
                        data.type = data.action;

                        sendPostMessage({ ...data, detail: x });
                    }).catch(p => {
                        sendPostMessage({ ...data, detail: null });
                    })
                } else if (data.action == 'userInfo') {
                    currentUser = data.user;
                    locationId = data.locationId;
                    connectZiwo(locationId).then(data => {
                        proceedAuth(data);
                    });
                } else if (data.action == 'locationInfo') {
                    locationId = data.locationId;
                } else if (data.action == 'dailer') {
                    openDialer();

                }
                else if (data.action == 'resizer') {
                    handleRemover();
                }
            }
        })

        sendPostMessage({ type: "REQUEST_LOCATION_ID" });
    }
    function openDialer() {
        waitElement('#tourMobPhone', 2000).then(x => {
            x.click();
        }).catch(p => {
            waitElement('#calls', 2000).then(x => {
                x.click();
            }).catch(p => {

            })
        })
    }
    function fetchContact(contact_id) {
        return new Promise((resolve, reject) => {
            CRMCall("contacts/" + contact_id).then(x => {
                if (x.contact) {
                    resolve(x.contact);
                }
            }).catch(p => {
                reject(p);
            })
        })
    }
    function checkConversation(contact_id) {
        let isConversation = contact_id.includes('conversation');
        contact_id = validateContact(contact_id);
        let conversation_id = '';
        if (isConversation) {
            conversation_id = contact_id;
        }
        return { conversation_id, isConversation, contact_id };
    }
    function getContact(contactId) {
        return new Promise((resolve, reject) => {

            let { conversation_id, isConversation, contact_id
            } = checkConversation(contactId);
            if (contact_id == '') {
                reject("");
                return;
            }
            if (!isConversation) {
                fetchContact(contact_id).then(x => {
                    resolve(x);
                })
            } else {
                CRMCall("conversations/" + contact_id).then(x => {
                    if (x.contactId) {
                        fetchContact(x.contactId).then(x => {
                            resolve(x);
                        });
                    }
                }).catch(p => {
                    reject(p);
                })
            }
        })
    }
    let apiCallCounter = 0;

    function crmAuthCheck() {

        return new Promise((resolve, reject) => {
            if (auth.location_token) {
                resolve();
            } else {
                // waitForResponse('get_token', {
                // }).then(data => {
                //     auth.location_token = data.token;
                //     resolve(x);
                // })
                reject();
            }
        })

    }
    function waitForResponse(action, data) {
        return new Promise((resolve, reject) => {
            let requestId = crypto.randomUUID();
            let eventLisener = function (e) {
                let data = e.data;
                if (data.requestId == requestId && data.type == action) {
                    window.removeEventListener('message', eventLisener);
                    resolve(data);
                }
            };
            window.addEventListener('message', eventLisener);
            sendtoCallerIframe(action, { ...data, requestId });
        });
    }
    function CRMCall(url, method = "GET", data = "", retries = 1) {
        return new Promise((resolve, reject) => {
            if (retries == 3) {
                reject();
                return;
            }
            crmAuthCheck().then(p => {
                var myHeaders = new Headers();
                myHeaders.append("Authorization", "Bearer " + auth.location_token);
                myHeaders.append("Content-Type", "application/json");
                myHeaders.append("version", "2021-07-28");
                var requestOptions = {
                    method: method,
                    headers: myHeaders,
                    redirect: "follow",
                };
                if (typeof data == "object") {
                    data = JSON.stringify(data);
                }
                if (data != "") {
                    requestOptions["body"] = data;
                }

                try {

                    let waitTime = apiCallCounter % 30 == 0 ? 500 : 0;
                    setTimeout(function () {
                        fetch("https://services.leadconnectorhq.com/" + url, requestOptions)
                            .then((response) => response.json())
                            .then((result) => {
                                resolve(result);
                            })
                            .catch((error) => reject(error));
                    }, waitTime);

                } catch (e) {
                    reject(e);
                }

            }).catch(x => {
                connectZiwo(locationId).then(p => {
                    if (p.location_token) {
                        CRMCall(url, method, data, retries + 1).then(p => {
                            resolve(p);
                        })
                    }
                })
            })
        });
    }


    function makeAPICall(url, data = "", method = "POST") {
        var myHeaders = new Headers();
        myHeaders.append("Accept", "application/json");
        myHeaders.append("Content-Type", "application/json");
        myHeaders.append("X-CSRF-Token", "{{csrf_token()}}");
        var requestOptions = {
            method: method,
            headers: myHeaders,
            redirect: "follow",
        };
        if (typeof data == "object") {
            data = JSON.stringify(data);
        }
        if (data != "") {
            requestOptions["body"] = data;
        }
        return new Promise((resolve, reject) => {
            try {
                fetch("https://ziwo.jdftest.xyz/ziwo/" + url, requestOptions)
                    .then((response) => response.json())
                    .then((result) => resolve(result))
                    .catch((error) => reject(error));
            } catch (e) {
                reject(e);
            }
        });
    }

    let auth = null;
    let route = '#/inbox/cdr';
    function waitElement(selector, timeout = 60000) {
        return new Promise(function (resolve, _) {
            var elm = document.querySelector(selector);
            if (elm) {
                resolve(elm);
                return;
            }
            let mutat = new MutationObserver(function (observer, mutation) {
                elm = document.querySelector(selector);
                if (elm) {
                    mutation.disconnect();
                    resolve(elm);
                }
            });
            mutat.observe(document, { subtree: true, childList: true });
            try {
                if (timeout > 0) {
                    setTimeout(function () {
                        mutat.disconnect();
                        _('not found');
                    }, timeout);
                }
            } catch (error) {
            }
        });
    }
    function proceedAuth(data) {
        localStorage.setItem('ZIWO_SESSION_DETAILS', JSON.stringify({
            "accountName": data.accountName ?? "asnanimedia",
            "token": data.token
        }));
        // let session=ZIWO.session.store.state().session;
        // session.accountName=data.accountName;
        // session.token=data.token;
        routeCheck();
        handleRemover();
    }

    function handleRemover() {
        ['ziwo-side-bar', 'ziwo-profile-card', 'mat-drawer-content', 'ziwo-icons-button[icon="hamburg"]'].forEach(p => {
            waitElement(p).then(x => {
                x.remove();
            }).catch(p => {

            });
        })

        waitElement('ziwo-bottom-bar').then(x => {
            x.querySelectorAll('button:not(#tourMobPhone)').forEach(x => {
                x.remove();
            })
        }).catch(p => {

        });
        openDialer();

    }

    window.onresize = function () {
        handleRemover();
    }
    function connectZiwo(location_id = "", forceChange = false) {
        return new Promise((resolve, reject) => {
            makeAPICall("get-token", { location_id, forceChange }).then(x => {
                if (x.data) {
                    let data = x.data ?? {};
                    auth = data;
                    resolve(data);
                } else if (x.error) {
                    notConnected();
                }
            }).catch(error => {
                notConnected()
            })
        })
    }

    function notConnected() {
        sendPostMessage({
            type: 'not_connected',
        })
    }

    function goToCDR() {
        history.replaceState(null, {}, `${location.pathname.replace('/', '')}${route}`);
        window.dispatchEvent(new Event('popstate'))
    }

    function routeCheck() {
        if (!['login', 'cdr'].some(t => location.href.includes(t))) {
            goToCDR();
        }
    }
    initZIWO();

    window.addEventListener('load', handleRemover);

</script>


</HTML>