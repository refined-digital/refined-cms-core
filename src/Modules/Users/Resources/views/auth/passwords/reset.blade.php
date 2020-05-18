@extends('core::layouts.auth')

@section('title', 'Reset Password')

@section('template')
<div class="auth auth__login">
  <div class="auth__right">
    <div class="login">
      @include('core::auth.logo')
      @include('core::includes.errors')

      <h3 class="login__header">Reset your password</h3>
      @include('users::auth.includes.reset')
    </div><!-- / login -->
  </div><!-- / auth right -->
</div><!-- / auth -->
@stop
