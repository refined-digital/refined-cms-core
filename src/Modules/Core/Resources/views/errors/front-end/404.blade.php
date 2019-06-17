@extends('core::errors.front-end._base')
<?php $page = pages()->getErrorPageVariables('Page Not Found', $exception->getStatusCode()); ?>
@section('message', 'Sorry, the page you are looking for could not be found.')