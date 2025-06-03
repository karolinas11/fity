@extends('layouts.app')

@section('content')

    <style>
        .divider {
            width: 100px;
            margin: 20px auto;
            height: 1px;
            background: black;
        }
    </style>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12 text-center">
                <h1 class="mb-4">Korisnik #{{ $user->id }}</h1>
            </div>

                 <input type="hidden" name="user_id" id="user_id" value="{{ $user->id }}">
            <div class="row text-center">
                <div class="col-md-4">
                    <h3>Cilj</h3>
                    <select name="goal" id="goal" class="form-select">
                        <option value="reduction" {{ $user->goal == 'reduction' ? 'selected' : '' }}>Redukcija telesne mase</option>
                        <option value="stable" {{ $user->goal == 'stable' ? 'selected' : '' }}>Održavanje telesne mase</option>
                        <option value="increase" {{ $user->goal == 'increase' ? 'selected' : '' }}>Uvećanje telesne mase</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <h3>Visina(cm)</h3>
                    <input type="text" name="height" id="height" class="form-control" value="{{ $user->height }}" placeholder="Visina u cm">

                </div>
                <div class="col-md-4">
                    <h3>Težina(kg)</h3>
                    <input type="text" name="weight" id="weight" class="form-control" value="{{ $user->weight }}">
                </div>
                <div class="col-md-4">
                    <h3>Godine</h3>

                    <input type="text" name="age" id="age" class="form-control" value="{{ $user->age }}">
                </div>
                <div class="col-md-4">
                    <h3>Aktivnost</h3>

                    <select name="activity" id="activity" class="form-select">
                        <option value="1.2" {{ $user->activity == 1.2 ? 'selected' : '' }}>Bez aktivnosti</option>
                        <option value="1.375" {{ $user->activity == 1.375 ? 'selected' : '' }}>Malo aktivnosti</option>
                        <option value="1.55" {{ $user->activity == 1.55 ? 'selected' : '' }}>Srednje aktivnosti</option>
                        <option value="1.725" {{ $user->activity == 1.725 ? 'selected' : '' }}>Teške aktivnosti</option>
                        <option value="1.95" {{ $user->activity == 1.95 ? 'selected' : '' }}>Jako teške aktivnosti</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <h3>Pol</h3>

                    <select name="gender" id="gender" class="form-select">
                        <option value="m" {{ $user->gender == 'm' ? 'selected' : '' }}>Muški</option>
                        <option value="f"  {{ $user->gender == 'f' ? 'selected' : '' }}>Ženski</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <h3>Tolerancija kalorija</h3>
                    <input type="number" name="tolerance_calories" id="tolerance_calories" class="form-control" value="{{ $user->tolerance_calories }}">
                </div>

                <div class="col-md-4">
                    <h3>Tolerancija proteina</h3>
                    <input type="number" name="tolerance_proteins" id="tolerance_proteins" class="form-control" value="{{ $user->tolerance_proteins }}">
                </div>

                <div class="col-md-4">
                    <h3>Tolerancija masti</h3>
                    <input type="number" name="tolerance_fats" id="tolerance_fats" class="form-control" value="{{$user->tolerance_fats}}">
                </div>

                <div class="col-md-3">
                    <h3>Broj obroka:</h3>
                    <select name="meals_num" id="meals_num" class="form-control">
                        <option value="3" {{$user->meals_num == '3' ? 'selected' : ''}}>3</option>
                        <option value="4" {{$user->meals_num == '4' ? 'selected' : ''}}>4</option>
                        <option value="5" {{$user->meals_num == '5' ? 'selected' : ''}}>5</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <h3>Broj dana</h3>
                    <input type="number" name="days" id="days" class="form-control" value="{{$user->days}}">
                </div>
                <div class="col-md-3">
                    <h3>Idealna težina</h3>
                    <p>{{ $target['weight'] }}</p>
                </div>
                <div class="col-md-3">
                    <h3>Algoritam</h3>
                    <select name="macros_type" id="macros_type" class="form-control">
                        <option value="1" {{ $user->macros_type == 1 ? 'selected' : ''}}>1</option>
                        <option value="2" {{ $user->macros_type == 2 ? 'selected' : ''}}>2</option>
                    </select>
                </div>
            </div>
                <div class="col-md-12 text-center mt-4">
                    <button type="submit" id="editUserButton" class="btn btn-primary">Sačuvaj izmene</button>
                </div>

            <div class="col-md-12 text-center mt-5">
                <h2 class="mb-4">Dnevni unos</h2>
            </div>
            <div class="row text-center">
                <div class="col-md-4">
                    <h3>Kalorije</h3>
                    <p id="calories">{{ $target['calories'] }}</p>
                </div>
                <div class="col-md-4">
                    <h3>Proteini</h3>
                    <p id="proteins">{{ $target['proteins'] }}g</p>
                </div>
                <div class="col-md-4">
                    <h3>Masti</h3>
                    <p id="fats">{{ $target['fats'] }}g</p>
                </div>
            </div>
            <div class="col-md-12 text-center mt-5">
                <h2 class="mb-4">Recepti</h2>
                @foreach($data['daily_plans'] as $day)
                    @if($day['exists'])
                        <div class="row">
                            <div class="col-md-1">
                                <h3>Dan - {{ $day['day'] }}</h3>
                            </div>
                            @foreach($day['meals'] as $meal)
                                <div class="col-md-2">
                                    <h4>{{ \App\Models\Recipe::find($meal['same_meal_id'])->name }}</h4>
                                    <div class="divider"></div>
                                    <p>Kalorije - {{ $meal['calories'] }}</p>
                                    <p>Proteini - {{ $meal['proteins'] }}g</p>
                                    <p>Masti - {{ $meal['fats'] }}g</p>
{{--                                    <p>Ugljeni hidrati - {{ $meal['carbs'] }}g</p>--}}
                                    <div class="divider"></div>


                                    @foreach($meal['holder_quantities'] as $key => $holder)
                                        @php
                                            $foodstuff = \App\Models\Foodstuff::find($key);
                                        @endphp

                                        @if($foodstuff)
                                            <p>
                                                {{ $foodstuff->name }} -
                                                {{ $holder }}g
                                                @php
                                                    $holderFoodstuff = \App\Models\RecipeFoodstuff::where('foodstuff_id', '=', $key)
                                                        ->where('recipe_id', '=', $meal['same_meal_id'])
                                                        ->get()
                                                        ->first();
                                                @endphp

                                                @if($holderFoodstuff->proteins_holder == 1)
                                                    - p
                                                @endif
                                                @if($holderFoodstuff->fats_holder == 1)
                                                    - m
                                                @endif
                                                @if($holderFoodstuff->carbohydrates_holder == 1)
                                                    - uh
                                                @endif
                                            </p>
                                        @else
                                            <p>Namirnica nije pronađena za ID {{ $key }}.</p>
                                        @endif
                                    @endforeach
                                    @foreach($meal['foodstuffs'] as $foodstuff)
                                        @if($foodstuff->proteins_holder == 0 && $foodstuff->fats_holder == 0 && $foodstuff->carbohydrates_holder == 0)
                                            <p>{{ \App\Models\Foodstuff::where('id', $foodstuff['foodstuff_id'])->get()[0]->name }} - {{ $foodstuff['amount']}}g</p>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                            <div class="col-md-1">
                                <h6>Kalorije - {{ $day['calories'] }}</h6>
                                <h6>Proteini - {{ $day['proteins'] }}g</h6>
                                <h6>Masti - {{ $day['fats'] }}g</h6>
                                <h4>Razlika</h4>
                                <h6>Kalorije  {{ $day['calories'] - $target['calories'] }}</h6>
                                <h6>Proteini  {{ $day['proteins'] - $target['proteins'] }}g</h6>
                                <h6>Masti  {{ $day['fats']  -  $target['fats'] }}g</h6>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

@endsection
@section('scriptsBottom')
    <script>

        document.getElementById('editUserButton').addEventListener('click', function() {
            //console.log('Selected gender:', document.getElementById('gender').value);
            var userData = {
                user_id: document.getElementById('user_id').value,
                goal: document.getElementById('goal').value,
                height: document.getElementById('height').value,
                weight: document.getElementById('weight').value,
                age: document.getElementById('age').value,
                gender: document.getElementById('gender').value,
                activity: document.getElementById('activity').value,
                tolerance_proteins: document.getElementById('tolerance_proteins').value,
                tolerance_calories: document.getElementById('tolerance_calories').value,
                tolerance_fats: document.getElementById('tolerance_fats').value,
                meals_num: document.getElementById('meals_num').value,
                days: document.getElementById('days').value,
                macros_type: document.getElementById('macros_type').value
            };

            // AJAX poziv za slanje podataka na server
            fetch(`/user/edit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(userData)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Ispisivanje rezultata u konzolu i rifres podataka
                        location.reload();
                        // document.getElementById('calories').textContent = data.target.calories;
                        // document.getElementById('proteins').textContent = data.target.proteins + 'g';
                        // document.getElementById('fats').textContent = data.target.fats + 'g';
                        // console.log('Makro podaci:', data.target);
                    } else {
                        console.log('Greška:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Greška prilikom slanja AJAX zahteva:', error);
                });
        });
    </script>
@endsection
