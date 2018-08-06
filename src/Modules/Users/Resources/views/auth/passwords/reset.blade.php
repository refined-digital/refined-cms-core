@extends('core::layouts.auth')

@section('title', 'Reset Password')

@section('template')
    <div class="auth auth__login">
        <div class="auth__right">
            <div class="login">

                @include('core::auth.logo')
                @include('core::includes.errors')

                <h3 class="login__header">Reset your password</h3>

                <div class="form">
                    {!! html()->form('POST', route('password.request'))->open() !!}
                        {!! html()->hidden('token', $token) !!}
                        <div class="form__row form__row--floating-labels{{ $errors->has('email') ? ' form__row--has-error' : '' }}">
                            {!! html()->email('email')->class('form__control')->id('form--email')->attribute('required', 'required') !!}
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
            </div><!-- / login -->
        </div><!-- / auth right -->
    </div><!-- / auth -->

@stop