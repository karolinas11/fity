@extends('layouts.app')

@section('content')
    <form action="{{ route('add-foodstuff') }}" method="post">
        @csrf
        <div>
            <label for="name">Naziv namirnice</label>
            <input type="text" name="name">
        </div>

        <div>
            <label>Kategorija namirnice</label>
            <select name="foodstuff_category_id">
                @foreach($foodstuffCategories as $foodstuffCategory)
                    <option value="{{ $foodstuffCategory->id }}">{{ $foodstuffCategory->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="amount">Količina</label>
            <input type="number" name="amount">
        </div>

        <div>
            <label for="measurement_unit">Jedinica mere</label>
            <input type="text" name="measurement_unit">
        </div>

        <div>
            <label for="calories">Kalorije</label>
            <input type="number" name="calories">
        </div>

        <div>
            <label for="proteins">Proteini</label>
            <input type="number" name="proteins">
        </div>

        <div>
            <label for="fats">Masti</label>
            <input type="number" name="fats">
        </div>

        <div>
            <label for="carbohydrates">Ugljeni hidrati</label>
            <input type="number" name="carbohydrates">
        </div>

        <div>
            <button type="submit">Pošalji</button>
        </div>
    </form>

@endsection
