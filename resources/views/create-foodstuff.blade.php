@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <form action="{{ route('add-foodstuff') }}" method="post">
            @csrf

            <div class="row mb-3">
                <label for="name" class="col-md-4 col-form-label">Naziv namirnice</label>
                <div class="col-md-8">
                    <input type="text" name="name" class="form-control" placeholder="Unesite naziv namirnice">
                </div>
            </div>

            <div class="row mb-3">
                <label for="foodstuff_category_id" class="col-md-4 col-form-label">Kategorija namirnice</label>
                <div class="col-md-8">
                    <select name="foodstuff_category_id" class="form-select">
                        @foreach($foodstuffCategories as $foodstuffCategory)
                            <option value="{{ $foodstuffCategory->id }}">{{ $foodstuffCategory->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <label for="amount" class="col-md-4 col-form-label">Količina</label>
                <div class="col-md-8">
                    <input type="number" name="amount" class="form-control" placeholder="Unesite količinu">
                </div>
            </div>

            <div class="row mb-3">
                <label for="measurement_unit" class="col-md-4 col-form-label">Jedinica mere</label>
                <div class="col-md-8">
                    <input type="text" name="measurement_unit" class="form-control" placeholder="Unesite jedinicu mere">
                </div>
            </div>

            <div class="row mb-3">
                <label for="calories" class="col-md-4 col-form-label">Kalorije</label>
                <div class="col-md-8">
                    <input type="text" name="calories" class="form-control" placeholder="Unesite broj kalorija">
                </div>
            </div>

            <div class="row mb-3">
                <label for="proteins" class="col-md-4 col-form-label">Proteini</label>
                <div class="col-md-8">
                    <input type="text" name="proteins" class="form-control" placeholder="Unesite količinu proteina">
                </div>
            </div>

            <div class="row mb-3">
                <label for="fats" class="col-md-4 col-form-label">Masti</label>
                <div class="col-md-8">
                    <input type="text" name="fats" class="form-control" placeholder="Unesite količinu masti">
                </div>
            </div>

            <div class="row mb-3">
                <label for="carbohydrates" class="col-md-4 col-form-label">Ugljeni hidrati</label>
                <div class="col-md-8">
                    <input type="text" name="carbohydrates" class="form-control" placeholder="Unesite količinu ugljenih hidrata">
                </div>
            </div>

            <div class="row mb-3">
                <label for="min" class="col-md-4 col-form-label">Minimalna količina</label>
                <div class="col-md-8">
                    <input type="text" name="min" class="form-control" placeholder="Unesite minimalnu količinu">
                </div>
            </div>

            <div class="row mb-3">
                <label for="max" class="col-md-4 col-form-label">Maksimalna količina</label>
                <div class="col-md-8">
                    <input type="text" name="max" class="form-control" placeholder="Unesite maksimalnu količinu">
                </div>
            </div>

            <div class="row mb-3">
                <label for="max" class="col-md-4 col-form-label">Stepen promene</label>
                <div class="col-md-8">
                    <input type="text" name="step" class="form-control" placeholder="Stepen promene">
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
