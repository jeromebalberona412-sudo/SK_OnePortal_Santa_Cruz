@extends('homepage::layout')

@section('styles')
    @parent
    @vite([
        'app/Modules/Program_Accomplishments/assets/css/barangay-accomplishments.css',
    ])
@endsection

@section('scripts')
    @parent
    @vite([
        'app/Modules/Program_Accomplishments/assets/js/barangay-accomplishments.js',
    ])
@endsection

