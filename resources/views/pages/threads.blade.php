@extends('layouts.default')
<style>
#wrapper #content-wrapper {
    background-color: #eff7f7 !important;
}

.heading {
    font-size: 20px !important;
    font-weight: 700 !important;
    color: #424242 !important;
}
.card {
    border: none !important;
    background-color: #fbf8f3 !important;
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

.back {
    color: #055d64;
    text-decoration: underline;
    font-weight: 700;
    cursor: pointer;
}

.avatar {
    width: 60px;
}

.avatar-broker {
    height: 40px;
    width: 40px;
    border-radius: 50%;
    background: linear-gradient(-45deg, #FAAD43, #f9d19b );
    color: #ffff;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-lender {
    height: 40px;
    width: 40px;
    border-radius: 50%;
    background: linear-gradient(-45deg, #033b40, #179d9b );
    color: #ffff;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-line {
    position: relative;
}

.avatar-line::after {
    position: absolute;
    content: '';
    display: block;
    width: 2px;
    height: 46px;
    background: #ddd;
    top: 50px;
    left: 19px;
}

.chat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 20px 15px 20px;
    position: relative;
}

.chat-header .border-right-alt {
    position: relative;
    text-align: left;
    border-right: 2px solid #ceccce;
}

/* .chat-header .border-right-alt::after {
    content: '';
    position: absolute;
    right: -38px;
    top: 0;
    width: 2px;
    height: 100%;
    background-color: #ceccce;
    border: none !important;
} */

.chat-header-item {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    flex: 1;
    padding-right: 10px;
}

.margin-left {
    margin-left: 20px;
}

.chat-header-name {
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 8px;
}

.chat-preview-date {
    font-size: 12px;
    margin-left: 10px;
}

.card--chat {
    max-height: 68vh;
    overflow-x: scroll;
    margin-bottom: 20px;
}

.fa-long-arrow-alt-left {
    margin-right: 7px
}

.message-text, .email-text {
    color: #000;
}

.email-text {
    font-weight: 300;
}

.message-name {
    font-size: 17px;
}

.messsage-not-sent {
    color: #e2401c;
    font-weight: 700;
}

@media (max-width: 430px) {
    .messsage-not-sent {
        display: block;
    }
}

@media (min-width: 1024px) {

    .card--chat {
        scrollbar-width: thin;
	    scrollbar-color: $thumb-color $track-color;
    }

    .card--chat::-webkit-scrollbar {
        width: 0.4vw;
        height: 8px;
    }

    .card--chat::-webkit-scrollbar-thumb {
        background-color: #0c8e8b;
        border-radius: 0;
        border: 1px solid transparent;
    }

    .card--chat::-webkit-scrollbar-track {
        background-color: transparent;
    }
}


@media (max-width: 990px) {
    .chat-header {
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
    }

    .chat-header  .border-right-alt {
        border-right: none;
    }

    .chat-header-item {
        margin-bottom: 20px;
    }

    .margin-left {
        margin-left: 0px;
    }

    .justify-content-between {
        flex-direction: column-reverse;
    }

    .chat-preview-date {
        margin-left: 0px;
    }

    .chat-preview-name {
        font-size: 12px;
    }
}

</style>
@section('content')

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <div class="col-xl-9 col-lg-12">
    <!-- Page Heading -->
    <div class="d-flex flex-column">
        <h1 class="h3 mb-0 heading mb-4">Message Thread</h1>
        <div class=" mb-4">
            <a href="/messages"><h6 class="mb-0 back"><i class="fas fa-long-arrow-alt-left"></i> Back to All Messages</h6></a>
        </div>
    </div>
        <div class="card">
                <div class="chat-header">
                    <div class="chat-header-item border-right-alt">
                        <span class="chat-header-name">BROKER</span><span><b class="mr-2 text-dark">{{ $room->broker->first_name }} {{ $room->broker->last_name }}</b>  <span class="email-text">{{ $room->broker->email }}</span></span>
                    </div>
                    <div class="chat-header-item border-right-alt margin-left">
                        <span class="chat-header-name">LENDER</span><span><b class="mr-2 text-dark">{{ $room->lender->first_name }} {{ $room->lender->last_name }}</b>  <span class="email-text">{{ $room->lender->email }}</span></span>
                    </div>
                    <div class="chat-header-item margin-left">
                        <span class="chat-header-name">DEAL ID</span><span><b class="text-dark">{{ $room->deal_id }} </b></span>
                    </div>
                </div>
        </div>
        <div class="card card--chat mt-4" style="background-color: #fff !important">
            <div class="card-body">
                @foreach($messages as $message)
                <div class="d-flex mb-5">
                    <div class="avatar">
                        <div class="{{ $message->user->role === "broker" ? 'avatar-broker' : 'avatar-lender'}} {{ $loop->last ? '' : 'avatar-line'}}">
                            {{ $message->user->role === "broker" ? 'B' : 'L'}}
                        </div>
                    </div>
                    <div class="d-flex flex-column w-100">
                        <div class="d-flex justify-content-between mb-2">
                            <div class="chat-preview-name">
                               <b class="text-dark mr-3"> <span class="message-name">{{ $message->user->first_name }} {{ $message->user->last_name }}</span> </b> {{ $message->user->email }}
                            </div>
                            <div class="chat-preview-date">
                               {{ $message->formatted_date }}
                            </div>
                        </div>
                        <div class="message-text">
                            {{ $message->message }}
                            <?php if($message->forbidden_msg): ?>
                                 <span class="messsage-not-sent">- Message not sent</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

@endsection
