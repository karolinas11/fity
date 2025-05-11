<?php

namespace App\DataTables;

use App\Models\Recipe;
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
            ->addColumn('type', function(Recipe $recipe) {
                return match ($recipe->type) {
                    2 => 'Ručak',
                    3 => 'Užina',
                    default => 'Doručak',
                };
            })
            ->addColumn('action', function (Recipe $recipe) {
                $editUrl = route('show-recipe-edit', $recipe->id);
                $deleteUrl = route('delete-recipe', $recipe->id);

                return '
                <a href="'.$editUrl.'" class="btn btn-sm btn-primary">Izmeni</a>
                <form action="'.$deleteUrl.'" method="POST" style="display:inline-block;">
                    '.csrf_field().'
                    '.method_field('DELETE').'
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Da li ste sigurni?\')">Izbriši</button>
                </form>
            ';
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Recipe $model): QueryBuilder
    {
        return $model->newQuery()
            ->select('recipes.*')
            ->when(request('search')['value'], function ($query) {
                $search = request('search')['value'];
                $query->where('name', 'like', "%{$search}%");
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
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(1)
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
            Column::make('type')->title('Tip obroka'),
            Column::computed('action')
                ->title('Akcije')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center')
                ->width(150),
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
