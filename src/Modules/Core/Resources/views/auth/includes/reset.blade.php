<div class="form">
  {!! html()->form('POST', route('password.store'))->open() !!}
    {!! html()->hidden('token', $request->route('token')) !!}
    <div class="form__row form__row--floating-labels{{ $errors->has('email') ? ' form__row--has-error' : '' }}">
      {!! html()->email('email')->class('form__control')->id('form--email')->attribute('required', 'required')->value(old('email', $request->email)) !!}
      {!! html()->label('Email')->class('form__label')->for('form--email') !!}
    </div>

    <div class="form__row form__row--floating-labels{{ $errors->has('password') ? ' form__row--has-error' : '' }}">
      {!! html()->password('password')->class('form__control')->id('form--password')->attribute('required', 'required') !!}
      {!! html()->label('Password')->class('form__label')->for('form--password') !!}
    </div>

    <div class="form__row form__row--floating-labels{{ $errors->has('password') ? ' form__row--has-error' : '' }}">
      {!! html()->password('password_confirmation')->class('form__control')->id('form--password_confirmation')->attribute('required', 'required') !!}
      {!! html()->label('Confirm Password')->class('form__label')->for('form--password_confirmation') !!}
    </div>

    <div class="form__row form__row--buttons">
      {!! html()->submit('Reset Password')->class('button') !!}
    </div>
  {!! html()->form()->close() !!}
</div>
