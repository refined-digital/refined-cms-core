@extends('core::errors.front-end._base')
@section('title', 'Error'))
<?php $page = pages()->getErrorPageVariables('Too many requests', $exception->getStatusCode()); ?>
@section('message', 'Too many requests.')