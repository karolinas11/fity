@extends('layouts.app')

@section('content')
    <form action="{{ route('add-foodstuff-category') }}" method="post">
        @csrf
        <div>
            <label for="name">Naziv kategorije:</label>
            <input type="text" name="name">
        </div>

        <div>
            <button type="submit">Pošalji</button>
        </div>
    </form>
@endsection
