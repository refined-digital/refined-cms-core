@if(session('status'))
	<div class="note{{ session()->has('fail') ? ' note--error' : ' note--success' }}" role="alert">{!! session('status') !!}</div>
@endif

@if (count($errors) > 0)
    <div class="alert alert--error">
        <header class="alert__header">
    	    <h4>You have some errors in your form.</h4>
        </header>
        <div class="alert__body">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif