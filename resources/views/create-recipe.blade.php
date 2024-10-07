@extends('layouts.app')

@section('content')
    <form action="{{ route('add-recipe') }}" method="post">
        @csrf
        <div>
            <label for="name">Naziv recepta</label>
            <input type="text" name="name">
        </div>

        <div>
            <label for="description">Upustvo i opis</label>
            <input type="text" name="description">
        </div>

        <div>
            <label for="short_description">Kratki opis</label>
            <input type="text" name="short_description">
        </div>

        <div>
            <label>Namirnica</label>
            <select name="foodstuff_id">
                @foreach($foodstuffs as $foodstuff)
                    <option value="{{ $foodstuff->id }}">{{ $foodstuff->name }}</option>
                @endforeach
            </select>

            <label for="amount">Količina</label>
            <input type="number" name="amount">
        </div>

        <div>
            <button type="submit">Pošalji</button>
        </div>
    </form>

@endsection

