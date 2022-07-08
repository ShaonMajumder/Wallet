@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif


                    <h2 class="mb-4">
                        Import Export Excel & CSV File
                    </h2>
                    <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            {{-- <label class="custom-file-label" for="customFile">Choose file</label> --}}
                            <input type="file" name="file" class="custom-file-input" id="customFile">
                        </div>

                        <button class="btn btn-primary">Import Users</button>
                        <a class="btn btn-success" href="{{ route('export-users') }}">Export Users</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection