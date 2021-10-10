@extends('core::errors.front-end._base')
@section('title', 'Service Unavailable')
<?php $page = pages()->getErrorPageVariables('Service Unavailable', $exception->getStatusCode()); ?>
@section('message', 'Be right back.')
