@extends('errors.layout')

@section('error_title', 'Under Maintenance - 503')
@section('code', '503')
@section('message_title', 'Under Maintenance')
@section('message_description')
    We are currently performing scheduled maintenance.
    We'll be back shortly. Thank you for your patience!
@endsection

@section('action_button')
    <a href="javascript:location.reload()" class="btn-error-back">
        <i class="bi bi-arrow-clockwise me-2"></i>Check Again
    </a>
@endsection
