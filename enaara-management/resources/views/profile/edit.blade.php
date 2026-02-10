@extends('layouts.app')

@section('title', __('Profile'))
@section('page-title', __('Profile'))

@section('content')
    <div class="container-fluid py-4">
        <div class="row g-4">
            <div class="col-12 col-lg-8 col-xl-6">
                <div class="bg-white rounded shadow-sm p-4 mb-4">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div class="bg-white rounded shadow-sm p-4 mb-4">
                    @include('profile.partials.update-password-form')
                </div>

                <div class="bg-white rounded shadow-sm p-4">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
@endsection
