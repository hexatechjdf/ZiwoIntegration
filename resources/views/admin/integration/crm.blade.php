<div class="col-md-6">
    <div class="card card-default">
        <div class="card-header"><span class="header-title">CRM Connection</span></div>
        <div class="card-body">
            <a href="{{CRM::directConnect()}}" class=" connect_crm btn btn-success text-center  my-3"> Connect To Agency/CRM </a>
            <br/>
            <div class="alert">
                <div class="crm_detail pt-2 " style="padding-top:10px"></div>
                <div class="locations_fetch" hidden>
                    <a href="javascript:void(0)" onclick="fetchLocs()" class="form-control fetch_locs btn btn-gradient-danger"> Fetch Location's </a>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    fetch('{{route('crm.fetchDetail')}}').then(x => x.json()).then(t => {
        if (!!t?.status == true) {
            document.querySelector('.crm_detail').innerHTML = t.detail;
            if(t?.type=='Company'){
                document.querySelector('.locations_fetch').removeAttribute('hidden');
            }
        }
        document.querySelector('.connect_crm').innerHTML = t.message;
    })

    function fetchLocs() {
        let btn = document.querySelector('.fetch_locs');

        let oldbtntext = btn.innerText;
        let total_count = 0;

        function process(page = 1) {
            btn.innerText = `Fetching... Page : ${page}`;
            fetch('{{route('crm.fetchLocations')}}?page=' + page).then(x => x.json()).then(t => {
                if (!!t?.status == true) {
                    total_count += t.detail.length;
                    if (!!t?.loadMore == true) {
                        setTimeout(function() {
                            process(page + 1);
                        }, 2000);
                    } else {
                        btn.innerText = `All fetched - ${total_count}`;
                        setTimeout(function() {
                            btn.innerText = oldbtntext;
                        }, 1500);
                    }

                } else {
                    alert(t?.message);
                }

            })

        }
        process(1);



    }

</script>
