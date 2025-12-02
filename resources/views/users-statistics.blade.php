@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12 text-center">
                <h1 class="mb-4">Statistika korisnika od 24.11.2025.</h1>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6 mb-4">
                <h3>Broj korisnika</h3>
                <p>{{ $total }}</p>
            </div>
            <div class="col-md-6 mb-4">
                <h3>Pretplaćeni korisnici</h3>
                <p>{{ $subscribed }}</p>
            </div>
{{--            <div class="col-md-4 mb-4">--}}
{{--                <h3>Fantomski korisnici</h3>--}}
{{--                <p>{{ $phantom }}</p>--}}
{{--            </div>--}}
        </div>
    </div>
@endsection
