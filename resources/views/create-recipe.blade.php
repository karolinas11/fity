@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h2 class="mb-4">Dodaj recept</h2>
        <form id="recipe-form">
            @csrf
            <div class="form-group mb-3">
                <label for="name">Naziv recepta</label>
                <input type="text" name="name" class="form-control" placeholder="Unesite naziv recepta">
            </div>

            <div class="form-group mb-3">
                <label for="description">Upustvo i opis</label>
                <textarea name="description" class="form-control" placeholder="Unesite upustvo i opis"></textarea>
            </div>

            <div class="form-group mb-3">
                <label for="short_description">Kratki opis</label>
                <textarea name="short_description" class="form-control" placeholder="Unesite kratki opis"></textarea>
            </div>

            <div class="form-group mb-3">
                <label for="type">Tip obroka</label>
                <select name="type" class="form-select">
                    <option value="1">Doručak</option>
                    <option value="2">Ručak</option>
                    <option value="2">Večera</option>
                    <option value="3">Užina</option>
                </select>
            </div>

            <div class="col-md-12">
                <label>Insulinska rezistencija</label>
                <input type="checkbox" name="insulin" value="1">
            </div>

            <div class="foodstuffs mb-4">
                <div class="single-foodstuff row mb-3">
                    <div class="col-md-6">
                        <label>Namirnica</label>
                        <select name="foodstuff_id" class="form-select">
                            @foreach($foodstuffs as $foodstuff)
                                <option value="{{ $foodstuff->id }}">{{ $foodstuff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="amount">Količina (g)</label>
                        <input type="number" name="amount" class="form-control" placeholder="Unesite količinu">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-foodstuff">Ukloni</button>
                    </div>

                    <div class="col-md-12">
                        <label>Nosilac proteina</label>
                        <input type="checkbox" name="proteins_holder" value="1">
                    </div>
                    <div class="col-md-12">
                        <label>Nosilac masti</label>
                        <input type="checkbox" name="fats_holder" value="1">
                    </div>
                    <div class="col-md-12">
                        <label>Nosilac ugljenih hidrata</label>
                        <input type="checkbox" name="carbohydrates_holder" value="1">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-success" id="add-foodstuff">Dodaj namirnicu</button>
            </div>

            <button type="button" class="mt-5 btn btn-primary" id="submit-recipe">Dodaj recept</button>

        </form>
    </div>
@endsection

@section('scriptsBottom')
    <script>
        let addFoodstuffBtn = document.getElementById('add-foodstuff');
        let foodstuffContainer = document.querySelector('.foodstuffs');

        addFoodstuffBtn.addEventListener('click', function() {
            let newFoodstuff = document.createElement('div');
            newFoodstuff.classList.add('single-foodstuff', 'row', 'mb-3');
            newFoodstuff.innerHTML = `
                <div class="col-md-6">
                    <label>Namirnica</label>
                    <select name="foodstuff_id" class="form-select">
                        @foreach($foodstuffs as $foodstuff)
                            <option value="{{ $foodstuff->id }}">{{ $foodstuff->name }}</option>
                        @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="amount">Količina (g)</label>
                        <input type="number" name="amount" class="form-control" placeholder="Unesite količinu">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-foodstuff">Ukloni</button>
                    </div>
                    <div class="col-md-12">
                        <label>Nosilac proteina</label>
                        <input type="checkbox" name="proteins_holder" value="1">
                    </div>
                    <div class="col-md-12">
                        <label>Nosilac masti</label>
                        <input type="checkbox" name="fats_holder" value="1">
                    </div>
                    <div class="col-md-12">
                        <label>Nosilac ugljenih hidrata</label>
                        <input type="checkbox" name="carbohydrates_holder" value="1">
                    </div>
            `;
            foodstuffContainer.appendChild(newFoodstuff);

            attachRemoveHandler();
        });

        function attachRemoveHandler() {
            let removeButtons = document.querySelectorAll('.remove-foodstuff');
            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    button.closest('.single-foodstuff').remove();
                });
            });
        }

        attachRemoveHandler();

        let submitBtn = document.getElementById('submit-recipe');
        submitBtn.addEventListener('click', function() {
            let foodstuffs = document.querySelectorAll('.single-foodstuff');
            let foodstuffData = [];

            foodstuffs.forEach(foodstuff => {
                let foodstuffId = foodstuff.querySelector('select[name="foodstuff_id"]').value;
                let amount = foodstuff.querySelector('input[name="amount"]').value;
                let proteinsHolder = foodstuff.querySelector('input[name="proteins_holder"]').checked ? 1 : 0;
                let fatsHolder = foodstuff.querySelector('input[name="fats_holder"]').checked ? 1 : 0;
                let carbohydratesHolder = foodstuff.querySelector('input[name="carbohydrates_holder"]').checked ? 1 : 0;

                foodstuffData.push({
                    foodstuff_id: foodstuffId,
                    amount: amount,
                    proteins_holder: proteinsHolder,
                    fats_holder: fatsHolder,
                    carbohydrates_holder: carbohydratesHolder,
                });
            });

            jQuery.ajax({
                url: "{{ route('add-recipe') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    name: document.querySelector('input[name="name"]').value,
                    description: document.querySelector('textarea[name="description"]').value,
                    short_description: document.querySelector('textarea[name="short_description"]').value,
                    type: document.querySelector('select[name="type"]').value,
                    insulin: document.querySelector('input[name="insulin"]').checked ? 1: 0,
                    foodstuffs: foodstuffData
                },
                success: function(result) {
                    alert('Recept uspešno dodat!');
                    window.location.href = window.origin + '/add-recipe-form/';
                }
            });
        });

    </script>
@endsection
