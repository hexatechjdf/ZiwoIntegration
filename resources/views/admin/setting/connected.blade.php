@php
    $token = $token ?? null;
    $error = $error ?? '';
    $message = $error == '' ? 'Connected Successfully' : $error;
@endphp
<script>
    let error = '{{ $error }}';
    let parentHandler = window.parent;
    if(window.self==parentHandler){
        parentHandler = window.opener;
    }
    parentHandler.postMessage({
        type: (error == '' ? 'connected' : 'error'),
        message: (error == '' ? 'Connected Successfully' : error),
        user: {
            id: '{{ $token->spotify_auth_id ?? '' }}',
            name: '{{ $token->name ?? '' }}',
            email: '{{ $token->email ?? '' }}',
        }
    }, '*');

    @if ($token && $token->user_type == 'user')
        // let queryparams =
        //     `spotify_user_id={{ $token->spotify_auth_id }}&bundle_slug={{ $bundle_slug }}&location_id={{ $location_id }}`;
        let controller = new AbortController();
        fetch(`{{ route('add.bundle.queue') }}`, {
            signal: controller.signal,
            method:'post',
            headers:{
                'Content-Type':'application/json'
            },
            body: JSON.stringify({
                'spotify_user_id':'{{ $token->spotify_auth_id }}',
                'bundle_slug':'{{ $bundle_slug }}',
                'location_id':'{{ $location_id }}',
                '_token':'{{csrf_token()}}'
            })
        }).then(x => {
            //console.log(x);
        }).catch(x => {
            //console.log(x);
        });


    @endif
</script>
<h1>{{ $message }} - Auto Close</h1>
