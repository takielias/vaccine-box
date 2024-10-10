@extends('tablar::page')

@section('title', 'Registration for Vaccine')

@section('content')
    <!-- Page header -->
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        {{ __('Vaccine Registration') }}
                    </h2>
                </div>
                <!-- Page title actions -->
                <div class="col-12 col-md-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{route('vaccination-status')}}" class="btn btn-primary d-none d-sm-inline-block">
                            <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-status-change" width="44" height="44" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M6 18m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                <path d="M18 18m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                <path d="M6 12v-2a6 6 0 1 1 12 0v2" />
                                <path d="M15 9l3 3l3 -3" />
                            </svg>
                            Vaccination Status
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
            @alert
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Patient Information</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{route('proceed-vaccine-registration')}}"
                                  enctype="multipart/form-data">
                                @csrf
                                @include('vaccination.form')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        label .text-danger {
            color: red;
            margin-left: 2px;
        }
    </style>
@stop

@section('js')
    <script type="module">
        flatpickr("#birth-date", {
            dateFormat: "Y-m-d",
            maxDate: "{{ now()->subYears(18)->format('Y-m-d') }}"
        });
    </script>
@stop

