<div class="form">
  {!! html()->form('POST', route('password.email'))->open() !!}
    <div class="form__row form__row--floating-labels{{ $errors->has('email') ? ' form__row--has-error' : '' }}">
      {!! html()->email('email')->class('form__control')->id('form--email')->attribute('required', 'required') !!}
      {!! html()->label('Email')->class('form__label')->for('form--email') !!}
    </div>

    <div class="form__row form__row--buttons">
      {!! html()->submit('Send Password Reset Link')->class('button') !!}
    </div>
  {!! html()->form()->close() !!}
</div>
