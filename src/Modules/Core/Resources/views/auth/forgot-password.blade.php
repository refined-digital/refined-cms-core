@extends('core::layouts.auth')

@section('title', 'Reset Password')

@section('template')
<div class="auth auth__login"{!! isset($backgroundImage) && $backgroundImage ? ' style="background-image:url('.$backgroundImage.')"' : '' !!}>
  <div class="auth__right">
    <div class="login">
      @include('core::auth.includes.logo')
      @include('core::includes.errors')

      <h3 class="login__header">Forgotten Password</h3>
      @include('core::auth.includes.email')
    </div><!-- / login -->
  </div><!-- / auth right -->
</div><!-- / auth -->
@stop
