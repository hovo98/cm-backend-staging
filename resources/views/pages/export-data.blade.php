@extends('layouts.default')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">All Data Export @if($authorization['success'])<em class="text-success small">Authorized</em>@endif</h1>
    </div>
    @if (session('message'))
        <div class="alert alert-success" role="alert">
            {{ session('message') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    @unless($authorization['success'])
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Google Sheet Authorization</h6>
            </div>
            <div class="card-body">

                <p><b>Status:</b> {!! $authorization['success'] ? '<span class="text-success">Authorized</span>' : '<span class="text-danger">Not Authorized</span>' !!}</p>

                @isset($authorization['error'])
                    <p><b>Error:</b> {!! $authorization['error'] !!}</p>
                @endisset

                @isset($authorization['authUrl'])
                    <p>To authorize app for Google Sheet sync, please follow this link:</p>
                    <a class="btn btn-primary btn-user" href="{{ $authorization['authUrl'] }}">Authorize</a>
                @endif
            </div>
        </div>
    @endunless

    @if($authorization['success'])
        <div class="card shadow mt-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Export Data</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <p>Exporting will take up to 15 minutes, depending on the amount of data.</p>
                    <p>Once the export has started, please do not navigate away from this page, as this will interrupt the export.</p>
                    <button id="js-export-button" class="btn btn-info btn-user">Start Exporting</button>
                    <a href="{{ $sheetUrl }}" class="btn btn-success btn-user js-export-button" target="_blank">Open sheet</a>
                </div>
                <div id="js-export-progress-wrapper" style="display: none">
                    <div class="mb-1">
                        <p><b>Sheets:</b></p>
                        <div id="js-totals"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif


@endsection

@section('footer-scripts')
    <script>
        let buttonDom = document.getElementById('js-export-button');
        let exportedDom = document.getElementById('js-exported');
        let totalsDom = document.getElementById('js-totals');
        let progressDom = document.getElementById('js-export-progress-wrapper');

        let importing = false;
        let totals = {};

        buttonDom.addEventListener('click', async function() {
            if (importing) {
                return;
            }

            setExportStarted();

            const response = await fetch('{{ route('export-data-start') }}', {
                cache: 'no-cache',
                headers: {
                    'Content-Type': 'application/json'
                }
            }).then(res => res.json());

            // Create HTML elements
            updateTotals(response);
            processChunk();

            progressDom.removeAttribute('style');
        });


        function setExportStarted() {
            buttonDom.setAttribute('disabled', 'disabled');
            buttonDom.innerText = 'Please wait...';
            importing = true;
            window.onbeforeunload = function() {
                return "Closing will cancel the unfinished export. Are you sure you want to cancel it?"
            }
        }

        function setExportFinished() {
            buttonDom.removeAttribute('disabled');
            buttonDom.innerText = 'Start Exporting';
            importing = false;
            window.onbeforeunload = null;
        }

        function updateTotals(manifest) {
            totalsDom.innerHTML = '';

            const ul = document.createElement('ul');

            for (const [key, sheet] of Object.entries(manifest.sheets)) {
                const li = document.createElement('li');
                li.innerHTML = `${sheet.title}: <b>${sheet.exported}</b>/${sheet.total}`;
                if (sheet.exported === sheet.total) li.classList = ['text-success'];
                ul.appendChild(li);
            }

            totalsDom.appendChild(ul);

            const p = document.createElement('p');
            p.innerHTML = `Total rows exported: <b>${manifest.exported}</b>/${manifest.total}`;
            if (manifest.exported === manifest.total) p.classList = ['text-success'];

            totalsDom.appendChild(p);

            if (manifest.error) {
                const err = document.createElement('div');
                err.classList = ['alert', 'alert-danger'];
                err.innerText = manifest.error;

                totalsDom.prepend(err);
            }
        }

        async function processChunk() {
            const response = await fetch('{{ route('export-data-process-chunk') }}', {
                cache: 'no-cache',
                headers: {
                    'Content-Type': 'application/json'
                }
            }).then(res => res.json());

            updateTotals(response);

            if (response.completed) {
                setExportFinished();
            } else {
                processChunk();
            }
        }
    </script>
@endsection
