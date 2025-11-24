<?php

namespace App\DataTables;

use App\Models\Foodstuff;
use App\Models\Recipe;
use App\Models\User;
use App\Models\UserRecipe;
use App\Services\RecipeFoodstuffService;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class RecipeDataTable extends DataTable
{

    protected RecipeFoodstuffService $recipeFoodstuffService;
    protected UserService $userService;
    public function __construct()
    {
        parent::__construct();
        $this->recipeFoodstuffService = new RecipeFoodstuffService();
        $this->userService = new UserService();
    }

    public function getRecipeData(Recipe $recipe) {
        $recipeFoodstuffs = $this->recipeFoodstuffService->getRecipeFoodstuffs($recipe->id);
        $fats = 0;
        $proteins = 0;
        $carbs = 0;
        foreach( $recipeFoodstuffs as $recipeFoodstuff){
            $foodstuff = Foodstuff::find($recipeFoodstuff->foodstuff_id);
            $proteins += ($recipeFoodstuff->amount / 100) * $foodstuff->proteins;
            $fats += ($recipeFoodstuff->amount / 100) * $foodstuff->fats;
            $carbs += ($recipeFoodstuff->amount / 100) * $foodstuff->carbohydrates;
        }

        $totalMass = $proteins + $fats + $carbs;

        $totalCal = ($proteins * 4) + ($fats * 9) + ($carbs * 4);

        $proteinCalPercentage = $totalCal > 0 ? (($proteins * 4) / $totalCal) * 100 : 0;
        $fatCalPercentage = $totalCal > 0 ? (($fats * 9) / $totalCal) * 100 : 0;
        $carbCalPercentage = $totalCal > 0 ? (($carbs * 4) / $totalCal) * 100 : 0;

//        $userRecipes = UserRecipe::where('recipe_id', $recipe->id)->get();
//        $userRecipesNum = $userRecipes->count();
//        $first = $second = $third = 0;
//        $reduction = $stable = $increase = 0;
//        foreach($userRecipes as $userRecipe) {
//            $user = User::find($userRecipe->user_id);
//            $calories = $this->userService->getMacrosForUser2($user)['calories'];
//            if($calories > 3000) {
//                $third++;
//            } else if($calories > 2000) {
//                $second++;
//            } else if($calories > 1000) {
//                $first++;
//            }
//            if($user->goal == 'reduction') {
//                $reduction++;
//            } elseif($user->goal == 'stable') {
//                $stable++;
//            } else {
//                $increase++;
//            }
//        }


        return [
            'proteins' => $proteins,
            'fats' => $fats,
            'carbs' => $carbs,
            'proteinCalPercentage' => $proteinCalPercentage,
            'fatCalPercentage' => $fatCalPercentage,
            'carbCalPercentage' => $carbCalPercentage,
//            'userRecipesNum' => $userRecipesNum,
//            'first' => $first,
//            'second' => $second,
//            'third' => $third,
//            'reduction' => $reduction,
//            'stable' => $stable,
//            'increase' => $increase
        ];
    }

    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('name', function(Recipe $recipe) {
                return '<span>' . $recipe->name . '</span><img height="80" src="' . asset('storage/featured_recipes/' . $recipe->featured_image) . '" alt=""/>';
            })
            ->addColumn('proteins', function(Recipe $recipe) {
                $macros = $this->getRecipeData($recipe);
                return number_format($macros['proteins'], 2) . 'g';
            })
            ->addColumn('fats', function(Recipe $recipe) {
                $macros = $this->getRecipeData($recipe);
                return number_format($macros['fats'], 2) . 'g';
            })
            ->addColumn('carbs', function(Recipe $recipe) {
                $macros = $this->getRecipeData($recipe);
                return number_format($macros['carbs'], 2) . 'g';
            })
            ->addColumn('proteins_percentage', function(Recipe $recipe) {
                $macros = $this->getRecipeData($recipe);
                return number_format($macros['proteinCalPercentage'], 2) . '%';
            })
            ->addColumn('fats_percentage', function(Recipe $recipe) {
                $macros = $this->getRecipeData($recipe);
                return number_format($macros['fatCalPercentage'], 2) . '%';
            })
            ->addColumn('carbs_percentage', function(Recipe $recipe) {
                $macros = $this->getRecipeData($recipe);
                return number_format($macros['carbCalPercentage'], 2) . '%';
            })
            ->addColumn('type', function(Recipe $recipe) {
                return match ($recipe->type) {
                    2 => 'Ručak, Večera',
                    3 => 'Užina',
                    default => 'Doručak',
                };
            })
//            ->addColumn('num_users', function(Recipe $recipe) {
//                $macros = $this->getRecipeData($recipe);
//                return $macros['userRecipesNum'];
//            })
//            ->addColumn('first', function(Recipe $recipe) {
//                $macros = $this->getRecipeData($recipe);
//                return $macros['first'];
//            })
//            ->addColumn('second', function(Recipe $recipe) {
//                $macros = $this->getRecipeData($recipe);
//                return $macros['second'];
//            })
//            ->addColumn('third', function(Recipe $recipe) {
//                $macros = $this->getRecipeData($recipe);
//                return $macros['third'];
//            })
//            ->addColumn('reduction', function(Recipe $recipe) {
//                $macros = $this->getRecipeData($recipe);
//                return $macros['reduction'];
//            })
//            ->addColumn('stable', function(Recipe $recipe) {
//                $macros = $this->getRecipeData($recipe);
//                return $macros['stable'];
//            })
//            ->addColumn('increase', function(Recipe $recipe) {
//                $macros = $this->getRecipeData($recipe);
//                return $macros['increase'];
//            })
            ->addColumn('action', function (Recipe $recipe) {
                $editUrl = route('show-recipe-edit', $recipe->id);
                $deleteUrl = route('delete-recipe', $recipe->id);

                return '
                <a href="'.$editUrl.'" target="_blank" class="btn btn-sm btn-primary">Izmeni</a>
                <form action="'.$deleteUrl.'" method="POST" style="display:inline-block;">
                    '.csrf_field().'
                    '.method_field('DELETE').'
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Da li ste sigurni?\')">Izbriši</button>
                </form>
            ';
            })
            ->rawColumns(['action', 'name', 'macros'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Recipe $model): QueryBuilder
    {
        return $model->newQuery()
            ->select('recipes.*')
            ->when(request('search.value'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when(request('recipeType'), function ($query, $type) {
                $query->where('type', $type);
            });
    }


    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('recipe-table')
            ->columns($this->getColumns())
            ->ajax([
                'url' => route('show-recipes-list'),
                'data' => 'function(d) {
                    d.recipeType = $("#recipeType").val();
                }'
            ])
            ->orderBy(1)
            ->pageLength(500)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            ]);
    }


    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('name')->title('Recept')->searchable(true),
            Column::computed('action')
                ->title('Akcije')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center text-nowrap')
                ->width(150),
            Column::make('proteins')->title('Proteini')->searchable(false),
            Column::make('fats')->title('Masti')->searchable(false),
            Column::make('carbs')->title('Ugljeni hidrati')->searchable(false),
            Column::make('proteins_percentage')->title('Proteini %')->searchable(false),
            Column::make('fats_percentage')->title('Masti %')->searchable(false),
            Column::make('carbs_percentage')->title('Ugljeni hidrati %')->searchable(false),
            Column::make('type')->title('Tip obroka'),
//            Column::make('num_users')->title('Broj korisnika')->searchable(false),
//            Column::make('first')->title('1000-2000cal')->searchable(false),
//            Column::make('second')->title('2000-3000cal')->searchable(false),
//            Column::make('third')->title('Preko 3000cal')->searchable(false),
//            Column::make('reduction')->title('Redukcija')->searchable(false),
//            Column::make('stable')->title('Održavanje')->searchable(false),
//            Column::make('increase')->title('Dobijanje')->searchable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Recipe_' . date('YmdHis');
    }
}
