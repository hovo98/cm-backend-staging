@extends('layouts.default')

@include('includes.tables')

@section('content')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800 mb-4">Blocked Users</h1>
    <p><a href="{{ route('users') }}" class="btn btn-primary btn-sm">Back to Users</a></p>
    @if (session('message'))
        <div class="alert alert-success" role="alert">
            {{ session('message') }}
        </div>
    @endif
    <div>
        <form id="searchform" class="search-form" name="searchform">
            <div class="form-group form-group--sm">
                <label>Search by email</label>
                <div class="form-group__input-search">
                    <input type="text" name="email" id="email" value="{{request()->get('email','')}}" class="form-control" />
                    <div class="form-group__input-search-xmark" id="clearBtnBlocked">
                        <i class="fas fa-fw fa-times"></i>
                    </div>
                </div>
            </div>
            <button class='btn btn-success' id="searchBtnUser">Search</button>
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
            <h6 class="m-0 font-weight-bold text-primary">Blocked Users</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="blockedTable" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Verified Email</th>
                        <th>Company name</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Verified Email</th>
                        <th>Company name</th>
                        <th></th>
                    </tr>
                    </tfoot>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->first_name}} {{ $user->last_name }}</td>
                            <td>{{ $user->role }}</td>
                            <td>{{ $user->email }}</td>
                            @if($user->email_verified_at)
                                <td>Yes</td>
                            @else
                                <td>No</td>
                            @endif
                            @if(empty($user->getCompany()['domain']))
                                <td>User doesn't belong to any Company</td>
                            @else
                                <td>{{$user->getCompany()['domain']}}</td>
                            @endif
                            <td>
                                <form action="{{ url('/users', ['id' => $user->id]) }}" method="post">
                                    <input class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" type="submit" value="Unblock User" />
                                    @method('post')
                                    @csrf
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="card-body__pagination">
                {{ $users->links('pagination::bootstrap-4') }}
                </div>
                Total: {{ $users->total() }}
            </div>
        </div>
    </div>
@endsection
