@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12 text-center">
                <h1 class="mb-4">Fity testno okruženje</h1>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6 mb-4">
                <a href="{{ route('show-add-recipe') }}" class="btn btn-primary btn-lg w-100">
                    Dodaj recept
                </a>
            </div>
            <div class="col-md-6 mb-4">
                <a href="{{ route('show-add-user') }}" class="btn btn-success btn-lg w-100">
                    Dodaj korisnika
                </a>
            </div>
            <div class="col-md-6 mb-4">
                <a href="{{ route('show-add-foodstuff') }}" class="btn btn-info btn-lg w-100">
                    Dodaj namirnicu
                </a>
            </div>
            <div class="col-md-6 mb-4">
                <a href="{{ route('show-add-foodstuff-category') }}" class="btn btn-warning btn-lg w-100">
                    Dodaj kategoriju namirnice
                </a>
            </div>
            <div class="col-md-6 mb-4">
                <a href="{{ route('show-recipes-list') }}" class="btn btn-secondary btn-lg w-100">
                    Lista recepata
                </a>
            </div>
            <div class="col-md-6 mb-4">
                <a href="{{ route('show-foodstuffs-list') }}" class="btn btn-secondary btn-lg w-100">
                    Lista namirnica
                </a>
            </div>
            <div class="col-md-6 mb-4">
                <a href="{{ route('show-users-list') }}" class="btn btn-secondary btn-lg w-100">
                    Lista korisnika
                </a>
            </div>
        </div>
    </div>
@endsection
