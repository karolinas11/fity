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
            <div class="row text-center">
                <div class="col-md-4">
                    <h3>Cilj</h3>
                    <p>
                        @switch($user->goal)
                            @case('reduction')
                                Redukcija telesne mase
                            @break
                            @case('stable')
                                Održavanje telesne mase
                            @break
                            @case('increase')
                                Uvećanje telesne mase
                            @break
                        @endswitch
                    </p>
                </div>
                <div class="col-md-4">
                    <h3>Visina</h3>
                    <p>{{ $user->height }}cm</p>
                </div>
                <div class="col-md-4">
                    <h3>Težina</h3>
                    <p>{{ $user->weight }}kg</p>
                </div>
                <div class="col-md-4">
                    <h3>Godine</h3>
                    <p>{{ $user->age }}</p>
                </div>
                <div class="col-md-4">
                    <h3>Aktivnost</h3>
                    <p>
                        @switch($user->activity)
                            @case(1.0)
                                Bez aktivnosti
                            @break
                            @case(1.15)
                                Malo aktivnosti
                            @break
                            @case(1.3)
                                Srednje aktivnosti
                            @break
                            @case(1.5)
                                Teške aktivnosti
                            @break
                            @case(1.75)
                                Jako teške aktivnosti
                            @break
                       @endswitch
                    </p>
                </div>
                <div class="col-md-4">
                    <h3>Pol</h3>
                    <p>
                        @if($user->gender == 'm')
                            Muški
                        @else
                            Ženski
                        @endif
                    </p>
                </div>
            </div>
            <div class="col-md-12 text-center mt-5">
                <h2 class="mb-4">Dnevni unos</h2>
            </div>
            <div class="row text-center">
                <div class="col-md-4">
                    <h3>Kalorije</h3>
                    <p>{{ $target['calories'] }}</p>
                </div>
                <div class="col-md-4">
                    <h3>Proteini</h3>
                    <p>{{ $target['proteins'] }}g</p>
                </div>
                <div class="col-md-4">
                    <h3>Masti</h3>
                    <p>{{ $target['fats'] }}g</p>
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
                                @foreach($meal['holders'] as $key => $holder)
                                    <p>{{ \App\Models\Foodstuff::find($key)->name }} - {{ $holder }}g</p>
                                @endforeach
                                @foreach($meal['foodstuffs'] as $foodstuff)
                                    <p>{{ $foodstuff['name'] }} - {{ $foodstuff['amount'] }}g</p>
                                @endforeach
                            </div>
                        @endforeach
                        <div class="col-md-1">
                            <h6>Kalorije - {{ $day['total_calories'] }}</h6>
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
