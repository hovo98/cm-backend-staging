@extends('layouts.default')
<style>

#wrapper #content-wrapper {
    background-color: #EFF8F7 !important;
}
 .switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

/* Hide default HTML checkbox */
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #eaecf4;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #055d64;
}

input:focus + .slider {
  box-shadow: 0 0 1px #033b40;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}
/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
.heading {
    font-size: 20px !important;
    font-weight: 700 !important;
    color: #424242 !important;
}
.card {
    border: none !important;
    max-height: 70vh;
    overflow: scroll;
}

@media (min-width: 1024px) {

    .card {
    scrollbar-width: thin;
    scrollbar-color: $thumb-color $track-color;
}

.card::-webkit-scrollbar {
    width: 0.4vw;
    height: 8px;
}

.card::-webkit-scrollbar-thumb {
    background-color: #0c8e8b;
    border-radius: 0;
    border: 1px solid transparent;
}

.card::-webkit-scrollbar-track {
    background-color: transparent;
}
}

.card-body {
    border: none !important;
}
.table th {
    border: none !important;
}

.table thead th {
    vertical-align: bottom;
    border-bottom: 1px solid #e3e6f0;
}

.table {
    font-size: 14px;
}

.text-dark {
    cursor: pointer;
}

.all-messages-header-wrapper th {
    font-size: 12px;
}

.all-messages-header-elements td {
    padding: 24px 0.75rem !important;
}

.messages-title {
    margin-top: 40px;
}

@media (max-width: 1389px) {
    .table {
        width: 1100px !important;
    }
}

</style>
@section('content')

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <div class="container col-12">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 heading messages-title">All Messages</h1>
    </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive border-0">
                    <table class="table" id="dataTable" width="100%" cellspacing="0">
                        <thead class="border-0">
                        <tr class="all-messages-header-wrapper">
                            <th>BROKER</th>
                            <th>LENDER</th>
                            <th>DEAL ID</th>
                            <th>LAST MESSAGED</th>
                            <th>LAST MESSAGE PREVIEW</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($rooms as $room)
                            <tr class="text-dark all-messages-header-elements" onClick="openThread({{ $room->id }})">
                                <td> <b class="mr-2">{{ $room->broker->first_name }} {{ $room->broker->last_name }}</b>  {{ $room->broker->email }}</td>
                                <td> <b class="mr-2">{{ $room->lender->first_name }} {{ $room->lender->last_name }}</b>  {{ $room->lender->email }}</td>
                                <td><b>{{ $room->deal_id }}</b></td>
                                <td><b>{{ $room->lastMessageTime() }}</b></td>
                                <td><i>{{ strlen($room->lastMessage()) > 30 ? substr($room->lastMessage(),0,50)."..." : $room->lastMessage() }}</i></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @if ($rooms)
                    <div class="card-body__pagination-wrapper">
                        <div class="card-body__pagination">
                        {{ $rooms->links('pagination::bootstrap-4') }}
                        </div>
                        Total: {{ $rooms->total() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <script>
        function openThread(id) {
            window.location.href="/threads/" + id;
        }
    </script>

@endsection
