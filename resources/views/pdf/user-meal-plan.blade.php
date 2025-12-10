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
            // Priprema grupa prema type
            $breakfast = [];
            $type2 = [];
            $snacks = [];

            foreach($day['meals'] as $m) {
                $recipe = \App\Models\Recipe::find($m['same_meal_id']);
                if ($recipe->type == 1) {
                    $breakfast[] = $m;
                } elseif ($recipe->type == 2) {
                    $type2[] = $m;
                } elseif ($recipe->type == 3) {
                    $snacks[] = $m;
                }
            }

            // Konačan željeni redosled
            $sortedMeals = [];

            // 1. Doručak
            foreach ($breakfast as $x) $sortedMeals[] = $x;

            // 2. Ručak 1
            if (isset($type2[0])) $sortedMeals[] = $type2[0];

            // 3. Užina 1
            if (isset($snacks[0])) $sortedMeals[] = $snacks[0];

            // 4. Ručak 2
            if (isset($type2[1])) $sortedMeals[] = $type2[1];

            // 5. Užina 2
            if (isset($snacks[1])) $sortedMeals[] = $snacks[1];

            // 6. Večera (ako postoji treći type 2)
            if (isset($type2[2])) $sortedMeals[] = $type2[2];

            // Brojač za naziv Ručak/Večera
            $type2Count = 0;
        @endphp


        @foreach($sortedMeals as $meal)

            @php
                $recipe = \App\Models\Recipe::find($meal['same_meal_id']);

                // Prevod type + logika ručak/večera
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
