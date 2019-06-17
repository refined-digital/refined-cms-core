@extends('core::errors.front-end._base')
@section('title', 'Page Expired'))
<?php $page = pages()->getErrorPageVariables('Page Expired', $exception->getStatusCode()); ?>
@section('message')
    The page has expired due to inactivity.
    <br/><br/>
    Please refresh and try again.
@stop