@extends('core::errors.front-end._base')
<?php $page = pages()->getErrorPageVariables('Unauthorised action'); ?>
@section('message', 'You are not authorised to access this area')