@extends('errors.layout')

@section('error_title', 'Server Error - 500')
@section('code', '500')
@section('message_title', 'Oops! Server Error')
@section('message_description')
    Something went wrong on our end! Our team has been notified and is working to fix the issue.
    Please try again in a few moments.
@endsection

@section('action_button')
    <a href="javascript:location.reload()" class="btn-error-back">
        <i class="bi bi-arrow-clockwise me-2"></i>Try Again
    </a>
@endsection
