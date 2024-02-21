@extends('layouts.default')

@include('includes.tables')

@section('content')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800 mb-4">Users Table</h1>
    @if (session('message'))
        <div class="alert alert-success" role="alert">
            {{ session('message') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif
    <div class="col-sm-9 pt-4 mb-4">
        <a class="btn btn-primary btn-sm" href="{{ route('users-blocked') }}">Blocked Users</a>
        <a class="btn btn-primary btn-sm" href="{{ route('export-lenders') }}">Export Lender's Deal Preferences</a>
    </div>
    <div>
        <form id="searchform" class="search-form" name="searchform">
            <div class="form-group form-group--sm">
                <label>Search by email</label>
                <div class="form-group__input-search">
                    <input type="text" name="email" id="email" value="{{request()->get('email','')}}" class="form-control" />
                    <div class="form-group__input-search-xmark" id="clearBtnUser">
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
            <h6 class="m-0 font-weight-bold text-primary">Users</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="userTable" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Beta user</th>
                        <th>Verified Email</th>
                        <th>Company domain</th>
                        <th>Invited By</th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Beta user</th>
                        <th>Verified Email</th>
                        <th>Company domain</th>
                        <th></th>
                        <th></th>
                    </tr>
                    </tfoot>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->first_name}} {{ $user->last_name }}</td>
                            <td>{{ $user->role }}</td>
                            <td>{{ $user->email }}</td>
                            <td style="min-width: 160px; text-align: center;">
                                <form action="{{ url('/users/approve', ['id' => $user->id]) }}" method="POST">
                                    <button class="d-none d-sm-inline-block btn {{  ($user &&  $user->beta_user ? ' btn-primary btn-sm' : ' btn-danger shadow-sm') }}" type="submit">{{  ($user &&  $user->beta_user ? 'Approved' : 'Not Approved') }}</button>
                                    @method('post')
                                    @csrf
                                </form>
                            </td>
                            @if($user->email_verified_at)
                                <td>Yes</td>
                            @else
                                <td>No</td>
                            @endif
                            @if(empty($user->getCompany()['domain']))
                                <td>User doesn't belong to any Company</td>
                            @else
                                <td><a href="{{ url('/company', ['id' => $user->getCompany()['id']]) }}">{{$user->getCompany()['domain']}}</a></td>
                            @endif

                            <td>
                                @if($user->referrer_id)
                                    <a href="/users?email={{ $user->invitedBy->email}}">
                                        {{ $user->invitedBy->first_name }}  {{ $user->invitedBy->last_name }}
                                    </a>
                                @else
                                    <p>N/A</p>
                                @endif
                            </td>
                            <td>
                                <button class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm js-delete-user" data-id="{{$user->id}}">Delete user</button>
                                <!-- <form action="{{ url('/users', ['id' => $user->id, 'flag' => 'delete']) }}" method="post">
                                    <input class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm js-delete-user" type="submit" value="Delete User" />
                                    @method('delete')
                                    @csrf
                                </form> -->
                            </td>
                            <td>
                                <form action="{{ url('/users', ['id' => $user->id, 'flag' => 'block']) }}" method="post">
                                    <input class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm" type="submit" value="Block User" />
                                    @method('delete')
                                    @csrf
                                </form>
                            </td>
                            <td>
                                @if($user->isBroker() && !$user->subscribed())
                                    <button class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm js-sub-user" data-id="{{$user->id}}">Gift Sub</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if ($users)
                    <div class="card-body__pagination">
                    {{ $users->links('pagination::bootstrap-4') }}
                    </div>
                    Total: {{ $users->total() }}
                @endif
            </div>
        </div>
    </div>

    <div class="delete-user__popup">
        <div class="delete-user__popup-content-wrapper">
            <h1 class="delete-user__popup-content-title">Are you sure you want to delete this user?</h1>
            <p class="delete-user__popup-content-text">Once it’s deleted, it’s gone forever. Block the user if you don't want to completely delete it. </p>
            <div class="delete-user__popup-content-buttons">
                <form action="" method="post" id="delete-user">
                    <input class="d-sm-inline-block btn btn-sm btn-danger shadow-sm delete-user" type="submit" value="Delete" />
                    @method('delete')
                    @csrf
                </form>

                <button class="d-sm-inline-block btn btn-sm btn-danger shadow-sm white delete-user js-close-popup">Cancel</button>
            </div>
        </div>
    </div>
    <div class="sub-user__popup">
        <div class="delete-user__popup-content-wrapper">
            <div class="delete-user__popup-content-buttons">
                <form action="" method="post" id="sub-user">
                    @csrf
                    <div class="form-group row">
                        <div class="col-sm-5">
                        <label for="duration" class="col-sm-5 col-form-label">Duration:</label>
                        </div>
                        <div class="col-sm-7">
                            <select name="duration" id="duration" class="form-control form-control-user">
                                <option value="7"> 7 Days</option>
                                <option value="7"> 15 Days</option>
                                <option value="7"> 30 Days</option>
                            </select>
                        </div>
                    </div>


                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label for="plan" class="col-sm-5 col-form-label">Plan:</label>
                        </div>
                        <div class="col-sm-9">
                            <select name="plan" id="plan" class="form-control form-control-user">
                                @foreach($plans as $key => $value)
                                    <option value="{{$key}}"> {{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-6">
                            <input class="inline-block btn btn-block btn-info shadow-sm sub-user" type="submit" value="Submit" />
                        </div>
                        <div class="col-sm-6">
                            <button class="inline-block btn btn-block btn-danger shadow-sm close-sub-modal" type="button">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
