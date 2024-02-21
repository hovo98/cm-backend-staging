@extends('layouts.default')

@section('content')
<?php $company = $company ?? ''; ?>
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            @if($company)
                Edit Company
            @else
                New Company
            @endif
        </h1>
    </div>
    @if (session('message'))
        <div class="alert alert-success" role="alert">
            {{ session('message') }}
        </div>
    @endif
    @if (session('message-failed'))
        <div class="alert alert-danger" role="alert">
            {{ session('message-failed') }}
        </div>
    @endif
    <div class="card shadow">
        <!-- Card Header -->
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Company Info</h6>
        </div>

        <div class="card-body">
                <form class="company" method="POST" action="{{  $company ? url('/company', ['id' => $company->id]) : route('company') }}">
                @csrf

                <input type="hidden" name="type" value="company">

                <div class="form-group row">
                    <label for="company-name" class="col-sm-1 col-form-label mt-2 text-right">Name:</label>
                    <div class="col-sm-9">
                        <input id="company-name" name="company_name" class="form-control form-control-company" type="text" value="{{$company ? $company->company_name : ''}}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="domain" class="col-sm-1 col-form-label mt-2 text-right">Domain:</label>
                    <div class="col-sm-9">
                        <input id="domain" name="domain" class="form-control form-control-company" type="text" value="{{$company ? $company->domain : ''}}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="company-address" class="col-sm-1 col-form-label mt-2 text-right">Address:</label>
                    <div class="col-sm-9">
                        <input id="company-address" name="company_address" class="form-control form-control-company" type="text" value="{{$company ? $company->company_address : ''}}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="company-city" class="col-sm-1 col-form-label mt-2 text-right">City:</label>
                    <div class="col-sm-9">
                        <input id="company-city" name="company_city" class="form-control form-control-company" type="text" value="{{$company ? $company->company_city : ''}}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="company-state" class="col-sm-1 col-form-label mt-2 text-right">State:</label>
                    <div class="col-sm-9">
                        <input id="company-state" name="company_state" class="form-control form-control-company" type="text" value="{{$company ? $company->company_state : ''}}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="company-zip-code" class="col-sm-1 col-form-label mt-2 text-right">Zip code:</label>
                    <div class="col-sm-9">
                        <input id="company-zip-code" name="company_zip_code" class="form-control form-control-company" type="text" value="{{$company ? $company->company_zip_code : ''}}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="company-phone" class="col-sm-1 col-form-label mt-2 text-right">Phone:</label>
                    <div class="col-sm-9">
                        <input id="company-phone" name="company_phone" class="form-control form-control-company" type="text" value="{{$company ? $company->company_phone : ''}}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="company_status" class="col-sm-1 col-form-label mt-2 text-right" >Approve:</label>
                    <div class="col-sm-9">
                        <select class="custom-select custom-select-sm form-control form-control-company" name="company_status">
                            @if($company)
                                @foreach($company->getCompanyStatusInt() as $status)
                                    <option {{ $status['status'] === $company->getCompanyStatus() ? 'selected' : '' }} value="{{$status['value']}}">{{$status['status']}}</option>
                                @endforeach
                            @else
                                <option value="1">Approved</option>
                                <option value="2">Pending</option>
                                <option value="3">Decline</option>
                            @endif
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-company mb-4">
                    @if($company)
                        {{ __('Update Company') }}
                    @else
                        {{ __('Save Company') }}
                    @endif
                </button>
            </form>
        </div>
    </div>
@endsection
