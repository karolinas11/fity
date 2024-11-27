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
                        <option value="increase" {{ $user->increase == 'increase' ? 'selected' : '' }}>Uvećanje telesne mase</option>
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
                        <option value="1.0" {{ $user->activity == 1.0 ? 'selected' : '' }}>Bez aktivnosti</option>
                        <option value="1.15" {{ $user->activity == 1.15 ? 'selected' : '' }}>Malo aktivnosti</option>
                        <option value="1.3" {{ $user->activity == 1.3 ? 'selected' : '' }}>Srednje aktivnosti</option>
                        <option value="1.5" {{ $user->activity == 1.5 ? 'selected' : '' }}>Teške aktivnosti</option>
                        <option value="1.75" {{ $user->activity == 1.75 ? 'selected' : '' }}>Jako teške aktivnosti</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <h3>Pol</h3>

                    <select name="gender" id="gender" class="form-select">
                        <option value="m" {{ $user->gender == 'm' ? 'selected' : '' }}>Muški</option>
                        <option value="f"  {{ $user->gender == 'f' ? 'selected' : '' }}>Ženski</option>
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
                    <div class="row">
                        <div class="col-md-1">
                            <h3>Dan - {{ $day['day'] }}</h3>
                        </div>
                        @foreach($day['meals'] as $meal)
                            <div class="col-md-2">
                                <h4>{{ \App\Models\Recipe::find($meal['same_meal_id'])->name }}</h4>
                                <div class="divider"></div>
                                <p>Kalorije - {{ $meal['calories'] }}</p>
                                <p>Proteini - {{ $meal['protein'] }}g</p>
                                <p>Masti - {{ $meal['fat'] }}g</p>
                                <p>Ugljeni hidrati - {{ $meal['carbs'] }}g</p>
                                <div class="divider"></div>


                                @foreach($meal['holders'] as $holder)
                                    @php
                                        $foodstuff = \App\Models\Foodstuff::find($holder['id']);
                                    @endphp

                                    @if($foodstuff)
                                        <p>
                                            {{ $foodstuff->name }} -
                                            {{ $holder['amount'] }}g

                                            @if($holder['p'] == 1)
                                                - p
                                            @endif

                                            @if($holder['f'] == 1)
                                                - m
                                            @endif

                                            @if($holder['c'] == 1)
                                                - u
                                            @endif
                                        </p>
                                    @else
                                        <p>Namirnica nije pronađena za ID {{ $holder['id'] }}.</p>
                                    @endif
                                @endforeach
                                @foreach($meal['foodstuffs'] as $foodstuff)
                                    <p>{{ $foodstuff['name'] }} - {{ $foodstuff['amount']}}g   </p>
                                @endforeach
                            </div>
                        @endforeach
                        <div class="col-md-1">
                            <h6>Kalorije - {{ $day['total_calories'] }}g</h6>
                            <h6>Proteini - {{ $day['total_protein'] }}g</h6>
                            <h6>Masti - {{ $day['total_fat'] }}g</h6>
                            <h4>Razlika</h4>
                            <h6>Kalorije  {{ $day['total_calories'] - $target['calories'] }}g</h6>
                            <h6>Proteini  {{ $day['total_protein'] - $target['proteins'] }}g</h6>
                            <h6>Masti  {{ $day['total_fat']  -  $target['fats'] }}g</h6>
                        </div>
                    </div>
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
                activity: document.getElementById('activity').value
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
                        document.getElementById('calories').textContent = data.target.calories;
                        document.getElementById('proteins').textContent = data.target.proteins + 'g';
                        document.getElementById('fats').textContent = data.target.fats + 'g';
                        console.log('Makro podaci:', data.target);
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
