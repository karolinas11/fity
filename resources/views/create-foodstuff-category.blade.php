@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <form action="{{ route('add-foodstuff-category') }}" method="post">
            @csrf
            <div class="row mb-3">
                <label for="name" class="col-md-4 col-form-label">Naziv kategorije</label>
                <div class="col-md-8">
                    <input type="text" name="name" class="form-control" placeholder="Unesite naziv kategorije">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-primary">Pošalji</button>
                </div>
            </div>
        </form>
    </div>
@endsection
