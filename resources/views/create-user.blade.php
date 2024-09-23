@extends('layouts.app')

@section('content')
    <form action="{{ route('add-user') }}" method="post">
        @csrf
        <div>
            <label for="goal">Šta želiš da postigneš?</label>
            <select name="goal" id="goal">
                <option value="reduction">Redukcija telesne mase</option>
                <option value="stable">Održavanje telesne mase</option>
                <option value="increase">Uvećanje telesne mase</option>
            </select>
        </div>

        <div>
            <label for="height">Visina(cm)</label>
            <input type="number" name="height">
        </div>

        <div>
            <label for="weight">Težina(kg)</label>
            <input type="number" name="weight">
        </div>

        <div>
            <label for="age">Godine</label>
            <input type="number" name="age">
        </div>

        <div>
            <label for="gender">Pol</label>
            <select name="gender" id="gender">
                <option value="m">Muški</option>
                <option value="f">Ženski</option>
            </select>
        </div>

        <div>
            <label for="activity">Odaberi svoj nivo kretanja i aktivnosti</label>
            <select name="activity" id="activity">
                <option value="none">Nimalo aktivni</option>
                <option value="low">Slabo aktivni</option>
                <option value="high">Vrlo aktivni</option>
                <option value="extremely">Ekstremno aktivni</option>
            </select>
        </div>

        <div>
            <label for="insulin_resistance">Da li imaš insulinsku rezistenciju?</label>
            <select name="insulin_resistance" id="insulin_resistance">
                <option value="yes">Da</option>
                <option value="no">Ne</option>
                <option value="not-sure">Nisam siguran/a</option>
            </select>
        </div>

        <div>
            <button type="submit">Pošalji</button>
        </div>
    </form>

@endsection
