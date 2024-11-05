@extends('layouts.app')

@section('content')
    <div class="container mt-5">

        <form action="{{ route('add-user') }}" method="post">
            @csrf

            <div class="row mb-3">
                <label for="goal" class="col-md-4 col-form-label">Šta želiš da postigneš?</label>
                <div class="col-md-8">
                    <select name="goal" id="goal" class="form-select">
                        <option value="reduction">Redukcija telesne mase</option>
                        <option value="stable">Održavanje telesne mase</option>
                        <option value="increase">Uvećanje telesne mase</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <label for="height" class="col-md-4 col-form-label">Visina (cm)</label>
                <div class="col-md-8">
                    <input type="number" name="height" class="form-control" placeholder="Unesite visinu">
                </div>
            </div>

            <div class="row mb-3">
                <label for="weight" class="col-md-4 col-form-label">Težina (kg)</label>
                <div class="col-md-8">
                    <input type="number" name="weight" class="form-control" placeholder="Unesite težinu">
                </div>
            </div>

            <div class="row mb-3">
                <label for="age" class="col-md-4 col-form-label">Godine</label>
                <div class="col-md-8">
                    <input type="number" name="age" class="form-control" placeholder="Unesite godine">
                </div>
            </div>

            <div class="row mb-3">
                <label for="gender" class="col-md-4 col-form-label">Pol</label>
                <div class="col-md-8">
                    <select name="gender" id="gender" class="form-select">
                        <option value="m">Muški</option>
                        <option value="f">Ženski</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <label for="activity" class="col-md-4 col-form-label">Odaberi svoj nivo kretanja i aktivnosti</label>
                <div class="col-md-8">
                    <select name="activity" id="activity" class="form-select">
                        <option value="1.0">Bez aktivnosti</option>
                        <option value="1.15">Malo aktivnosti</option>
                        <option value="1.3">Srednje aktivnosti</option>
                        <option value="1.5">Teške aktivnosti</option>
                        <option value="1.75">Jako teške aktivnosti</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <label for="insulin_resistance" class="col-md-4 col-form-label">Da li imaš insulinsku rezistenciju?</label>
                <div class="col-md-8">
                    <select name="insulin_resistance" id="insulin_resistance" class="form-select">
                        <option value="yes">Da</option>
                        <option value="no">Ne</option>
                        <option value="not-sure">Nisam siguran/a</option>
                    </select>
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
