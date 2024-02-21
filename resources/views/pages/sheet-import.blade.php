@extends('layouts.default')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Sheet Import</h1>
    </div>
    @if (session('message'))
        <div class="alert alert-success" role="alert">
            {{ session('message') }}
        </div>
    @endif
    <div class="card shadow">

        <div class="card-body">
            <div class="form-group">
                <input id="sheet-url" name="sheet-url" type="text" class="form-control form-control-user js-url-input" placeholder="Copy sheet URL here..." value="https://docs.google.com/spreadsheets/d/1tI7Dk0NJNSlSD2qBJDDsLOXZHcmU-Iy6kls74FsXCYQ/edit?pli=1#gid=0">
            </div>
            <div class="form-group">
                <button class="btn btn-primary btn-user js-import-button">Start Import</button>
            </div>
            <div class="js-import-progress-wrapper" style="display: none">
                <div class="mb-1">Import progress:</div>
                <div class="row no-gutters align-items-center">
                    <div class="col-auto">
                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800 js-percentage">0%</div>
                    </div>
                    <div class="col">
                        <div class="progress mr-2">
                            <div class="progress-bar js-progress-bar" role="progressbar" style="width: 0%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
                <div class="js-results-wrapper mb-1" style="display: none">
                    Created: <span class="js-created"></span><br>
                    Updated: <span class="js-updated"></span><br>
                    Failed to import: <span class="js-problem"></span>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('footer-scripts')
    <script>
        (function($){
            var $input = $('.js-url-input');
            var $button = $('.js-import-button');
            var $progressWrapper = $('.js-import-progress-wrapper');
            var $progressBar = $('.js-progress-bar');
            var $percentage = $('.js-percentage');
            var $resultWrapper = $('.js-results-wrapper');
            var $created = $('.js-created');
            var $updated = $('.js-updated');
            var $problem = $('.js-problem');
            var data = [];
            var chunkSize = 200;
            var chunksCount = 0;
            var importing = false;
            var updated = 0;
            var created = 0;
            var problem = 0;
            function validate() {
                if (importing) {
                    return;
                }
                if ($input.val() === '') {
                    $button.attr('disabled', 'disabled');
                } else {
                    $button.attr('disabled', false);
                }
            }
            $input.on('change keydown', function() { validate(); });
            $button.on('mouseover focus', function() { validate(); });
            $button.on('click', function() { getData(); });
            function getData() {
                if (importing) {
                    return;
                }
                $.ajax({
                    type: 'POST',
                    beforeSend: function(xhr){xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}')},
                    url: "{{ route('sheet-import-get-data') }}",
                    data: {
                        url: $input.val()
                    },
                    dataType: 'json',
                    success: function (res) {
                        if (res.length) {
                            data = res;
                            chunksCount = Math.ceil(data.length / chunkSize);
                            $progressWrapper.fadeIn();
                            $button.attr('disabled', 'disabled');
                            $button.text('Importing...');
                            importing = true;
                            processChunk(0);
                        }
                    }
                })
            }
            function processChunk(chunkIteration) {
                var start = chunkIteration === 0 ? 0 : chunkIteration * chunkSize;
                var end = (chunkIteration + 1) * chunkSize;
                var chunk = data.slice(start, end);
                $.ajax({
                    type: 'POST',
                    beforeSend: function(xhr){xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}')},
                    url: "{{ route('sheet-import-chunk') }}",
                    data: {
                        data: JSON.stringify(chunk)
                    },
                    dataType: 'json',
                    success: function (res) {
                        updated += res.exists;
                        created += res.success;
                        problem += res.problem;
                        $resultWrapper.fadeIn();
                        $created.text(created);
                        $updated.text(updated);
                        $problem.text(problem);
                        if (chunkIteration === chunksCount) {
                            $progressBar.css('width', '100%');
                            $percentage.text('100%');
                            $button.attr('disabled', false);
                            $button.text('Start Import');
                            importing = false;
                            return;
                        }
                        var percentage = Math.round(100 / chunksCount * chunkIteration);
                        $progressBar.css('width', `${percentage}%` );
                        $percentage.text(`${percentage}%`);
                        processChunk(++ chunkIteration);
                    }
                })
            }
        })($);
    </script>
@endsection
