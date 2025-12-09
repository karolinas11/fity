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
    </style>
</head>
<body>

<h1>Plan ishrane za korisnika #{{ $user->id }}</h1>

@foreach($data['daily_plans'] as $day)
    @if($day['exists'])

        <h2>Dan {{ $day['day'] }}</h2>

        @foreach($day['meals'] as $meal)

            <p class="meal-title">
                {{ \App\Models\Recipe::find($meal['same_meal_id'])->type }}:
                {{ \App\Models\Recipe::find($meal['same_meal_id'])->name }}
            </p>

            @foreach($meal['holder_quantities'] as $foodId => $grams)
                @php
                    $food = \App\Models\Foodstuff::find($foodId);
                @endphp

                @if($food)
                    <p>{{ $grams }}g - {{ $food->name }}</p>
                @endif
            @endforeach

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
