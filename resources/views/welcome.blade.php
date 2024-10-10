@extends('tablar::page')

@section('content')
    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="container my-5">
                        <div class="row p-4 pb-0 pe-lg-0 pt-lg-5 align-items-center rounded-3 border shadow-lg">
                            <div class="col-lg-7 p-3 p-lg-5 pt-lg-3">
                                <h3 class="display-5">X Vaccine</h3>
                                <p class="lead">Protect Yourself, Protect Your Community</p>
                                <div class="hr-text"><h3 class="text-red">It's Free & Safe</h3></div>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-start mb-4 mb-lg-3">
                                    <a class="btn btn-outline-indigo px-4 gap-3"
                                       href="{{{route('vaccine-registration')}}}">

                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-user-plus">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                            <path d="M16 19h6" />
                                            <path d="M19 16v6" />
                                            <path d="M6 21v-2a4 4 0 0 1 4 -4h4" />
                                        </svg>

                                        Registration
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row row-deck row-cards">
                <div class="container">
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')

@stop
