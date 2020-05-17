@extends('core::layouts.auth')

@section('title', 'Login')

@section('template')
<div class="auth auth__login">
  <div class="auth__right">
    <div class="login">
      @include('core::auth.logo')
      @include('core::includes.errors')

      @include('users::auth.includes.login-form')
    </div><!-- / login -->
  </div><!-- / auth right -->
</div><!-- / auth -->
@stop
