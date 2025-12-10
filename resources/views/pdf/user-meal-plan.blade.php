<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Meal Plan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { margin-top: 30px; }
        h3 { margin-bottom: 5px; }
        .meal-title { font-weight: bold; font-size: 14px; margin-top: 10px; }
        .meal-description { font-style: italic; margin-bottom: 10px; }
    </style>
</head>
<body>

<h1>Plan ishrane za korisnika #{{ $user->id }}</h1>

@foreach($data['daily_plans'] as $day)
    @if($day['exists'])

        <h2>Dan {{ $day['day'] }}</h2>

        @php
            // Broj pojavljivanja obroka type=2
            $type2Count = 0;
        @endphp

        @foreach($day['meals'] as $meal)

            @php
                $recipe = \App\Models\Recipe::find($meal['same_meal_id']);

                // Prevod type -> naziv obroka
                $typeText = match($recipe->type) {
                    1 => 'Doručak',
                    2 => ($type2Count++ == 0 ? 'Ručak' : 'Večera'),
                    3 => 'Užina',
                    default => 'Obrok'
                };
            @endphp

            <p class="meal-title">
                {{ $typeText }}: {{ $recipe->name }}
            </p>

            {{-- OPIS RECEPTA --}}
            @if(!empty($recipe->description))
                <p class="meal-description">
                    {!! nl2br(e($recipe->description)) !!}
                </p>
            @endif

            {{-- Holder quantities --}}
            @foreach($meal['holder_quantities'] as $foodId => $grams)
                @php
                    $food = \App\Models\Foodstuff::find($foodId);
                @endphp

                @if($food)
                    <p>{{ $grams }}g - {{ $food->name }}</p>
                @endif
            @endforeach

            {{-- Standard foodstuffs --}}
            @foreach($meal['foodstuffs'] as $foodstuff)
                @if($foodstuff->proteins_holder == 0 &&
                     $foodstuff->fats_holder == 0 &&
                     $foodstuff->carbohydrates_holder == 0)
                    <p>
                        {{ \App\Models\Foodstuff::find($foodstuff->foodstuff_id)->name }}
                        - {{ $foodstuff->amount }}g
                    </p>
                @endif
            @endforeach

            <br>

        @endforeach

    @endif
@endforeach

</body>
</html>
