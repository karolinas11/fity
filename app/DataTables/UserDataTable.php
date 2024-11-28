<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UserDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('id', function(User $user) {
                return $user->id;
            })
            ->addColumn('goal', function(User $user) {
                return $user->goal;
            })
            ->addColumn('height', function(User $user) {
                return $user->height;
            })
            ->addColumn('weight', function(User $user) {
                return $user->weight;
            })
            ->addColumn('age', function(User $user) {
                return $user->age;
            })
            ->addColumn('gender', function(User $user) {
                return $user->gender;
            })
            ->addColumn('activity', function(User $user) {
                return $user->activity;
            })
            ->addColumn('meals_num', function(User $user) {
                return $user->meals_num;
            })
            ->addColumn('tolerance_proiteins', function(User $user) {
                return $user->tolerance_proiteins;
            })
            ->addColumn('tolerance_fats', function(User $user) {
                return $user->tolerance_fats;
            })
            ->addColumn('tolerance_calories', function(User $user) {
                return $user->tolerance_calories;
            })
            ->addColumn('days', function(User $user) {
                return $user->days;
            })
            ->addColumn('action', function (User $user) {
                $editUrl = route('assign-recipes-to-user', $user->id);

                return '<a href="'.$editUrl.'" class="btn btn-sm btn-primary">Pregled</a>';
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $user): QueryBuilder
    {
        return $user->newQuery();
    }


    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('user-table')
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
            Column::make('id')->title('Korisnik')->searchable(true),
            Column::make('goal')->title('Cilj'),
            Column::make('height')->title('Visina'),
            Column::make('weight')->title('Težina'),
            Column::make('age')->title('Godine'),
            Column::make('gender')->title('Pol'),
            Column::make('activity')->title('Aktivnost'),
            Column::make('meals_num')->title('Broj obroka'),
            Column::make('tolerance_proteins')->title('Tolerancija proteina'),
            Column::make('tolerance_fats')->title('Tolerancija masti'),
            Column::make('tolerance_calories')->title('Tolerancija kalorija'),
            Column::make('days')->title('Dani'),
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
        return 'User_' . date('YmdHis');
    }
}
