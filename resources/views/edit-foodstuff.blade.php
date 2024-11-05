@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <form action="{{ route('edit-foodstuff', $foodstuff->id) }}" method="post">
            @csrf

            <div class="row mb-3">
                <label for="name" class="col-md-4 col-form-label">Naziv namirnice</label>
                <div class="col-md-8">
                    <input type="text" name="name" value="{{ $foodstuff->name }}" class="form-control" placeholder="Unesite naziv namirnice">
                </div>
            </div>

            <div class="row mb-3">
                <label for="foodstuff_category_id" class="col-md-4 col-form-label">Kategorija namirnice</label>
                <div class="col-md-8">
                    <select name="foodstuff_category_id" class="form-select">
                        @foreach($foodstuffCategories as $foodstuffCategory)
                            <option {{ $foodstuffCategory->id == $foodstuff->foodstuff_category_id ? 'selected' : '' }} value="{{ $foodstuffCategory->id }}">{{ $foodstuffCategory->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <label for="amount" class="col-md-4 col-form-label">Količina</label>
                <div class="col-md-8">
                    <input type="number" value="{{ $foodstuff->amount }}" name="amount" class="form-control" placeholder="Unesite količinu">
                </div>
            </div>

            <div class="row mb-3">
                <label for="measurement_unit" class="col-md-4 col-form-label">Jedinica mere</label>
                <div class="col-md-8">
                    <input type="text" value="{{ $foodstuff->measurement_unit }}" name="measurement_unit" class="form-control" placeholder="Unesite jedinicu mere">
                </div>
            </div>

            <div class="row mb-3">
                <label for="calories" class="col-md-4 col-form-label">Kalorije</label>
                <div class="col-md-8">
                    <input type="text" value="{{ $foodstuff->calories }}" name="calories" class="form-control" placeholder="Unesite broj kalorija">
                </div>
            </div>

            <div class="row mb-3">
                <label for="proteins" class="col-md-4 col-form-label">Proteini</label>
                <div class="col-md-8">
                    <input type="text" value="{{ $foodstuff->proteins }}" name="proteins" class="form-control" placeholder="Unesite količinu proteina">
                </div>
            </div>

            <div class="row mb-3">
                <label for="fats" class="col-md-4 col-form-label">Masti</label>
                <div class="col-md-8">
                    <input type="text" value="{{ $foodstuff->fats }}" name="fats" class="form-control" placeholder="Unesite količinu masti">
                </div>
            </div>

            <div class="row mb-3">
                <label for="carbohydrates" class="col-md-4 col-form-label">Ugljeni hidrati</label>
                <div class="col-md-8">
                    <input type="text" value="{{ $foodstuff->carbohydrates }}" name="carbohydrates" class="form-control" placeholder="Unesite količinu ugljenih hidrata">
                </div>
            </div>

            <div class="row mb-3">
                <label for="min" class="col-md-4 col-form-label">Minimalna količina</label>
                <div class="col-md-8">
                    <input type="text" value="{{ $foodstuff->min }}" name="min" class="form-control" placeholder="Unesite minimalnu količinu">
                </div>
            </div>

            <div class="row mb-3">
                <label for="max" class="col-md-4 col-form-label">Maksimalna količina</label>
                <div class="col-md-8">
                    <input type="text" value="{{ $foodstuff->max }}" name="max" class="form-control" placeholder="Unesite maksimalnu količinu">
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
