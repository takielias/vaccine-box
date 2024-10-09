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
