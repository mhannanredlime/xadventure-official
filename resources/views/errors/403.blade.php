@extends('errors.layout')

@section('error_title', 'Access Forbidden - 403')
@section('code', '403')
@section('message_title', 'Access Forbidden')
@section('message_description')
    You don't have permission to access this area.
    If you believe this is a mistake, please contact the administrator.
@endsection
