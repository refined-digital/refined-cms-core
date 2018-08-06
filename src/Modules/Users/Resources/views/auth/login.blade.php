@extends('core::layouts.auth')

@section('title', 'Login')

@section('template')
    <div class="auth auth__login">
        <div class="auth__right">
            <div class="login">

                @include('core::auth.logo')
                @include('core::includes.errors')

                <div class="form">
                    {!! html()->form('POST', route('login'))->open() !!}
                        <div class="form__row form__row--floating-labels{{ $errors->has('email') ? ' form__row--has-error' : '' }}">
                            {!! html()->email('email')->class('form__control')->id('form--email')->attribute('required', 'required') !!}
                            {!! html()->label('Email')->class('form__label')->for('form--email') !!}
                        </div>

                        <div class="form__row form__row--floating-labels{{ $errors->has('password') ? ' form__row--has-error' : '' }}">
                            {!! html()->password('password')->class('form__control')->id('form--password')->attribute('required', 'required') !!}
                            {!! html()->label('Password')->class('form__label')->for('form--password') !!}
                        </div>

                        <div class="form__row form__row--floating-labels form__row--remember">
                            <div class="form__checkbox">
                                {!! html()->checkbox('remember')->id('checkbox-test') !!}
                                {!! html()->label('Remember me')->for('checkbox-test') !!}
                            </div>
                            <div class="form__forgotten">
                                <a href="{{ route('password.request') }}">Forgot Password?</a>
                            </div>
                        </div>

                        <div class="form__row form__row--buttons">
                            {!! html()->submit('Login')->class('button') !!}
                        </div>
                    {!! html()->form()->close() !!}
                </div>
            </div><!-- / login -->
        </div><!-- / auth right -->
    </div><!-- / auth -->

@stop