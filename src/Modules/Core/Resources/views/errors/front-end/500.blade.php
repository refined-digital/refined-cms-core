@extends('core::errors.front-end._base')
@section('title', 'Error')
<?php $page = pages()->getErrorPageVariables('Whoops', $exception->getStatusCode()); ?>
@section('message', 'Whoops, looks like something went wrong.')
