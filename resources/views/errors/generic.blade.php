@extends('errors.layout')

@section('error_title', 'Error - ' . $exception->getStatusCode())
@section('code', $exception->getStatusCode())
@section('message_title', $exception->getMessage() ?: 'An error occurred')
@section('message_description')
    We're sorry, but something went wrong.
    Please try again or contact our support team if the problem persists.
@endsection
