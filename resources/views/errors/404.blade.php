@extends('errors.layout')

@section('error_title', 'Page Not Found - 404')
@section('code', '404')
@section('message_title', 'Oops! Page Not Found')
@section('message_description')
    The adventure you're looking for seems to have taken a detour!
    The page you're trying to reach doesn't exist or has been moved to a different location.
@endsection
