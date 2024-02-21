@extends('layouts.default')

@include('includes.tables')

@section('content')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Companies Table</h1>
    <div class="d-flex pt-4 mb-4">
        <a class="btn btn-primary btn-sm mr-3" href="{{ route('company') }}">Add New Company</a>
    </div>
    @if (session('message'))
        <div class="alert alert-success" role="alert">
            {{ session('message') }}
        </div>
    @endif
    <div>
        <form id="searchform" class="search-form" name="searchform">
            <div class="form-group form-group--sm">
                <label>Search by domain</label>
                <div class="form-group__input-search">
                    <input type="text" name="domain" id="domain" value="{{request()->get('domain','')}}" class="form-control" />
                    <div class="form-group__input-search-xmark" id="clearBtnCompany">
                        <i class="fas fa-fw fa-times"></i>
                    </div>
                </div>
            </div>
            <button class='btn btn-success' id="searchBtnCompanies">Search</button>
        </form>

        <form id="entriesform" class="entries-form paggination-sellect-form" name="entriesform">
            Show
            <div class="paggination-sellect">
                <select class="custom-select" name="entries" id="entries" value="{{request()->get('entries', '')}}">
                    <option value="10" {{ request()->get('entries', '') == 10 ? $selected = 'selected' : $selected = '' }} {{$selected}}>10</option>
                    <option value="25" {{ request()->get('entries', '') == 25 ? $selected = 'selected' : $selected = '' }} {{$selected}}>25</option>
                    <option value="50" {{ request()->get('entries', '') == 50 ? $selected = 'selected' : $selected = '' }} {{$selected}}>50</option>
                    <option value="100" {{ request()->get('entries', '') == 100 ? $selected = 'selected' : $selected = '' }} {{$selected}}>100</option>
                </select>
            </div>
            entries
        </form>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Companies</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="companiesTable" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Domain</th>
                        <th>City</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th>Name</th>
                        <th>Domain</th>
                        <th>City</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    </tfoot>
                    <tbody>
                    @foreach($companies as $company)
                        <tr>
                            <td>{{ $company->company_name}}</td>
                            <td><a href="/company/{{ $company->id}}">{{ $company->domain}}</a></td>
                            <td>{{ $company->company_city }}</td>
                            <td>
                                {{$company->getCompanyStatus()}}
                            </td>
                            <td>
                                <form action="{{ url('/companies/approve', ['id' => $company->id]) }}" method="POST" class="d-inline-block company-change-status" id="company-change-status">
                                    @csrf
                                    <select class="custom-select custom-select-sm form-control form-control-company-status company-status" name="company_status">
                                        @foreach($company->getCompanyStatusInt() as $status)
                                            <option {{ $status['status'] === $company->getCompanyStatus() ? 'selected' : '' }} value="{{$status['value']}}">{{$status['status']}}</option>
                                        @endforeach
                                    </select>
                                </form>
                                <a href="/company/{{ $company->id}}" class="btn btn-sm btn-info shadow-sm mr-1 mb-1 d-inline-block">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if ($companies)
                    <div class="card-body__pagination">
                    {{ $companies->links('pagination::bootstrap-4') }}
                    </div>
                    Total: {{ $companies->total() }}
                @endif
            </div>
        </div>
    </div>
@endsection
