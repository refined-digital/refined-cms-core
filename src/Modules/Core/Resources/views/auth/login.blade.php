@extends('core::layouts.auth')

@section('title', 'Login')

@section('template')
<div class="auth auth__login"{!! isset($backgroundImage) && $backgroundImage ? ' style="background-image:url('.$backgroundImage.')"' : '' !!}>
  <div class="auth__right">
    <div class="login">
      @include('core::auth.includes.logo')
      @include('core::includes.errors')

      @include('core::auth.includes.login-form')
    </div><!-- / login -->
  </div><!-- / auth right -->
</div><!-- / auth -->
@stop
