<?php

namespace App\DataTables;

use App\Models\Foodstuff;
use App\Models\FoodstuffCategory;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class FoodstuffDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('name', function(Foodstuff $foodstuff) {
                return '<span>' . $foodstuff->name . '</span><img height="80" src="' . asset('storage/foodstuffs/' . $foodstuff->featured_image) . '" alt=""/>';
            })
            ->addColumn('amount', function(Foodstuff $foodstuff) {
                return $foodstuff->amount;
            })
            ->addColumn('measurement_unit', function(Foodstuff $foodstuff) {
                return $foodstuff->measurement_unit;
            })
            ->addColumn('calories', function(Foodstuff $foodstuff) {
                return $foodstuff->calories;
            })
            ->addColumn('proteins', function(Foodstuff $foodstuff) {
                return $foodstuff->proteins;
            })
            ->addColumn('fats', function(Foodstuff $foodstuff) {
                return $foodstuff->fats;
            })
            ->addColumn('carbohydrates', function(Foodstuff $foodstuff) {
                return $foodstuff->carbohydrates;
            })
            ->addColumn('min', function(Foodstuff $foodstuff) {
                return $foodstuff->min;
            })
            ->addColumn('max', function(Foodstuff $foodstuff) {
                return $foodstuff->max;
            })
            ->addColumn('foodstuff_category_id', function(Foodstuff $foodstuff) {
                return FoodstuffCategory::find($foodstuff->foodstuff_category_id)->name;
            })
            ->addColumn('action', function (Foodstuff $foodstuff) {
                $editUrl = route('show-foodstuff-edit', $foodstuff->id);
                $deleteUrl = route('delete-foodstuff', $foodstuff->id);

                return '
                <a href="'.$editUrl.'" target="_blank" class="btn btn-sm btn-primary">Izmeni</a>
                <form action="'.$deleteUrl.'" method="POST" style="display:inline-block;">
                    '.csrf_field().'
                    '.method_field('DELETE').'
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Da li ste sigurni?\')">Izbriši</button>
                </form>
            ';
            })
            ->rawColumns(['action', 'name'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Foodstuff $model): QueryBuilder
    {
        return $model->newQuery()
            ->select('foodstuffs.*')
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
                    ->setTableId('foodstuff-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
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
            Column::make('name')->title('Namirnica')->searchable(true),
            Column::make('amount')->title('Količina'),
            Column::make('measurement_unit')->title('Merna jedinica'),
            Column::make('calories')->title('Kalorije'),
            Column::make('proteins')->title('Proteini'),
            Column::make('fats')->title('Masti'),
            Column::make('carbohydrates')->title('Ugljeni hidrati'),
            Column::make('min')->title('Minimalna količina'),
            Column::make('max')->title('Maksimalna količina'),
            Column::make('foodstuff_category_id')->title('Kategorija'),
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
        return 'Foodstuff_' . date('YmdHis');
    }
}
