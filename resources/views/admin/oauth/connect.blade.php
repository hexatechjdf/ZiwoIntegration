<div id="mainbody p-5">
    <div class="row pr-5 pl-5 pt-2 text-center">
        <div class="error-crm text-danger w-100 text-center"></div>
    </div>
    <div class="row pr-5 pl-5 pt-1">
        <div class="col-md-12">

            <div class="row section lastlogin" id="last_login">

                <div class="col-md-12 d-flex flex-column align-items-center justify-content-center ">
                    <h3 class="text-center mt-1">Already login</h3>
                    <h5 id="last_login_email"></h5>
                    <div class="d-flex justify-content-center ">
                        <button class="btn btn-primary px-4 m-1 reconnect">Connect now</button>
                        <button class="btn btn-danger px-4 m-1 last_logout" data-prev="logincrm">Logout</button>
                    </div>
                </div>
            </div>

            <div class="row section first " id="logincrm">
                <div class="col-md-6 offset-md-3">
                    <h3 class="text-center">Login To CRM</h3>
                    @if ($type == 'agency')
                        <p>====================</p>
                    @endif
                    <form id="myform">

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control" id="email"
                                aria-describedby="emailHelp" />
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" class="form-control" id="oauth_password" />
                        </div>
                        <button type="submit" class="btn btn-primary mt-3 text-center" id="submitdata">
                            Submit
                        </button>
                    </form>
                </div>
            </div>

            <div class="row section" id="multiusers">
            </div>

            <div class="row section" id="otpcode">
                <div class="">
                    <button class="btn btn-danger px-4 backbutton" data-prev="selectchannel">Back</button>
                    <h5 class="text-center mt-5">Please enter the OTP <br> to verify</h5>
                    <form class="text-center">
                        <div id="otp" class="inputs d-flex flex-row justify-content-center mt-2">
                            <input class="m-2 text-center form-control w-full rounded" type="number" id="otpcodevalue"
                                maxlength="6" />
                        </div>
                        <div class="form-group d-flex flex-column justify-content-center">
                            <div class="mt-4">
                                <button class="btn btn-primary px-4 validate" id="sendotp">Validate</button>
                            </div>
                            <div class="mt-4">
                                <p class="text-link resend text-default" onclick="resendOTP('1')">Click here if not
                                    received</p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row section" id="selectchannel">
                <button class="col-md-2 btn btn-danger px-4 backbutton channelsect" data-prev="logincrm">Back</button>
                <div class="text-center">
                    <h3 class="text-center mt-3">OTP Channel</h3>
                    <p>How you would like to receive otp for the verification?</p>
                    <form id="otpform" class="">
                        <div class="form-group channel cphone">
                            <label for="phone">Phone
                                <input type="radio" name="channel" id="phone" value="phone" selected />
                                <span id="cphone"></span>
                            </label>
                        </div>
                        <div class="form-group channel cemail">
                            <label for="ceemail">Email
                                <input type="radio" name="channel" id="ceemail" value="email" />
                                <span id="cemail"></span>
                            </label>
                            <input type="hidden" value="" id="token" />
                            <input type="hidden" value="" id="selected-cahnnel" />
                        </div>

                </div>
                </form>
                <div class="form-group">
                    <button type="button" class="mt-5 btn btn-primary" id="sendotptype">
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        var baseURLloc = 'https://services.leadconnectorhq.com/';
        var locPublic = baseURLloc + 'integrations/public/';
        var sessionKey = '';
        $(document).ready(function() {
            $(".section:not(.first)").hide();

            let already_login = localStorage.getItem('login_email') || "";
            if (already_login != "") {
                $('#last_login_email').html(already_login);
                sessionKey = localStorage.getItem('sessionKey') || "";
                if (sessionKey != "") {
                    $(".section").hide();
                    $('#last_login').show();
                }
            }
        });
        (function(e) {
            function tokenDone(url) {
                console.log(url);
                fetch(url + `&sessionKey=${sessionKey}&onlyjson=1${company_prefix}`).then((response) => response.json())
                    .then((data) => {
                        console.log(data);
                        if (data.data == 'Connected') {
                            //   var parentWindow = window.parent;
                            //   parentWindow.postMessage('connectedcrm', '*');
                            location.reload();
                        } else {
                            showError(data);
                        }
                    });
            }

            function getById(id) {
                return document.getElementById(id);
            }

            function getDefaultEP() {
                return {
                    email: getById('email').value,
                    password: getById('oauth_password').value,

                };
            }

            function getHeaders(data) {

                var myHeaders = new Headers();
                myHeaders.append("Authorization", "Bearer " + data?.apiKey);
                sessionKey = data?.apiKey;
                return myHeaders;
            }

            function getCompany(data) {

                return new Promise((resolve, reject) => {
                    var requestOptions = {
                        method: 'GET',
                        headers: getHeaders(data),
                    };
                    fetch(locPublic + 'company', requestOptions).then(t => t.json()).then(x => {
                        if (x?.company) {
                            company_prefix =
                                `&company_id=${x.company.id}&company_name=${x.company.name}`;
                            resolve(x);
                        } else if (x?.message) {
                            showError(x.message);
                        }
                        reject(x);
                    }).catch(err => {});
                });
            }

            function getLocations(data) {

                return new Promise((resolve, reject) => {

                    var requestOptions = {
                        method: 'GET',
                        headers: getHeaders(data),
                    };
                    fetch(locPublic + 'locations?limit=1000&skip=0', requestOptions).then(t => t.json()).then(
                        x => {
                            if (x?.locations) {
                                resolve(x.locations);
                            } else if (x?.message) {
                                showError(x.message);
                            }
                            reject(x);
                        }).catch(err => {});
                });
            }
            var company_prefix = '';

            function handleOauth(data, company_id = "", type = "Location") {
                //  oauth.write oauth.readonly
                let keyloc = type == 'Company' ? 'company_id' : "location_id";
                let url =
                    `https://services.leadconnectorhq.com/oauth/authorize?client_id={{\CRM::getDefault("crm_client_id",'')}}&${keyloc}=${company_id}&response_type=code&redirect_uri={{env('CRM_REDIRECT_URL','http://127.0.0.1:9000/authorization/crm/oauth/callback')}}&scope={{ \CRM::$scopes }}&userType=${type}`;
                var requestOptions = {
                    method: 'POST',
                    headers: getHeaders(data),
                };
                @if ($type == 'agency')
                    requestOptions['body'] = JSON.stringify({
                        "approveAllLocations": true
                    });
                    requestOptions.headers.append("Content-Type", "application/json;charset=UTF-8");
                @endif
                fetch(url, requestOptions)
                    .then(response => response.json())
                    .then(result => {
                        if (result?.redirectUrl) {
                            tokenDone(result.redirectUrl);
                            return;
                        }
                        if (result?.message) {
                            try {
                                if (result.message.includes('access to this location')) {
                                    showError(
                                        'Not a valid location : please login with credentials where you have access to the location {{ $id }}, please close the window to login again'
                                    );
                                    return;
                                }
                            } catch (err) {}
                            showError(result.message);
                        }
                        if (result?.error_description) {
                            showError(result.error_description);
                        }
                    })
                    .catch(error => console.log('error', error));
                console.log("Successfully");
            }

            function showError(msg = '') {
                var errorcrm = document.querySelector(".error-crm");
                errorcrm.textContent = msg;
            }
            var companyId = '';

            function makeCall(data = {}) {
                let cred = getDefaultEP();
                if (cred?.email) {
                    localStorage.setItem('login_email', cred.email);
                }

                showError();
                if (Object.keys(data).length > 0) {
                    cred = {
                        ...cred,
                        ...data
                    };
                }
                if (companyId != '') {
                    cred['companyId'] = companyId;
                }
                fetch(mainurl, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(cred)
                    })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data?.message) {

                            showError(data.message);
                            return;
                        }
                        let backbtnchannel = $('.backbutton.channelsect');
                        $(".section").hide();

                        if (data?.multipleUsers) {
                            backbtnchannel.attr('data-prev', 'multiusers');
                            var html = `<button class="col-md-2 btn btn-danger px-4 backbutton " data-prev="logincrm">Back</button> 
                            <div class="text-center">
                        
                                    <h3 class="text-center mt-3">Select Account to Connect</h3>
                                    <form id="otpform" class="mt-4 d-flex justify-content-evenly">${data.multipleUsers
                                    .map((e, i) => {
                                        return `<div class="form-group">
                                                               <label for="user${e.companyId}">
                                                                   
                                                                    <input type="radio" name="user" id="user${e.companyId}" value="${e.companyId}">
                                                                      ${e.agencyName}</label></div>`;
                                    })
                                    .join("")}
                                       
                                    </form> <div class="form-group mt-5">
                                            <button type="button" class="btn btn-primary" id="selectuser" >Submit</button>
                                        </div>
                                </div>`;
                            let multiusers = document.querySelector("#multiusers");
                            $("#multiusers").show();
                            if (multiusers) {
                                multiusers.innerHTML = html;
                            }
                        }
                        if (data?.phone || data?.email) {
                            if (backbtnchannel.data('prev') != "multiusers") {
                                backbtnchannel.attr('data-prev', 'logincrm');
                            }
                            document.querySelectorAll(".channel").forEach((t) => {
                                t.style.display = "none";
                            });
                            if (data?.phone) {
                                document.querySelector(".cphone.channel").style.display =
                                    "block";
                                getById("cphone").textContent = data.phone;
                            }
                            if (data?.email) {
                                document.querySelector(".cemail.channel").style.display =
                                    "block";
                                getById("cemail").textContent = data.email;
                            }
                            $("#selectchannel").show();
                        }
                        if (data?.token && !data?.apiKey) {
                            document.querySelector("#token").value = data.token;
                            let otpcode = document.querySelector("#otpcode");
                            $("#otpcode").show();
                        }
                        if (data?.apiKey) {
                            localStorage.setItem('sessionKey', data?.apiKey);
                            doConnection(data);
                        }
                    })
                    .catch((error) => console.error(error));
            }
            var mainurl = "https://services.leadconnectorhq.com/oauth/2/login/email";
            $("#submitdata").off("click");
            $("#submitdata").on("click", function(e) {
                e.preventDefault();
                makeCall();
            });

            $("body").off("click", ".reconnect");
            $("body").on("click", ".reconnect", function(e) {
                e.preventDefault();
                $(".section").hide();
                doConnection({
                    apiKey: sessionKey
                });
            });

            $("body").off("click", ".last_logout");
            $("body").on("click", ".last_logout", function(e) {
                e.preventDefault();
                $(".section").hide();
                let sect = $(this).data('prev');
                if (sect == 'logincrm') {
                    companyId = '';
                }
                localStorage.setItem('login_email', '');
                localStorage.setItem('sessionKey', '');
                $(`#${sect}`).show();
            });

            $("body").off("click", ".backbutton");
            $("body").on("click", ".backbutton", function(e) {
                e.preventDefault();
                $(".section").hide();
                let sect = $(this).data('prev');
                if (sect == 'logincrm') {
                    companyId = '';
                }
                $(`#${sect}`).show();
            });

            $("body").off("click", "#selectuser");
            $("body").on("click", "#selectuser", function(e) {
                e.preventDefault();
                companyId = document.querySelector(
                    'input[name="user"]:checked'
                ).value;
                makeCall();
            });

            function doConnection(data) {
                let type = '{{ $type }}';
                showError('Please wait... connecting');

                if (type == 'agency') {
                    getCompany(data).then(x => {
                        handleOauth(data, x.company.id, 'Company');
                    }).catch(err => {

                    });
                } else {
                    company_prefix = '';
                    handleOauth(data, '{{ $id }}', 'Location');
                }
            }

            function getOTPChannel() {
                return document.querySelector(
                    'input[name="channel"]:checked'
                ).value;
            }

            function resendOTP(isresend = '0') {
                const otpChannel = getOTPChannel();

                makeCall({
                    otpChannel
                });
                if (isresend == 1) {
                    let button = document.querySelector('.resend');
                    showError('New Code Sent');
                    button.style.display = 'none';
                    setTimeout(function(act) {
                        showError('');
                        button.style.display = act;
                    }, 15000, 'block');
                }
            }
            $("body").off("click", "#sendotptype");
            $("body").on("click", "#sendotptype", function(e) {
                e.preventDefault();
                resendOTP();

            });
            $("body").off("click", "#sendotp");
            $("body").on("click", "#sendotp", function(e) {
                e.preventDefault();
                const token = getById("token").value;
                const otpChannel = getOTPChannel();
                let otp = getById('otpcodevalue').value;
                makeCall({
                    otpChannel,
                    token,
                    otp
                });
            });
        })(this);
    </script>
</div>
