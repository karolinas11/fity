@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        table {
            width: 100% !important;
        }

        .container {
            max-width: 1400px;
        }

    </style>
@endsection

@section('scriptsTop')
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.3/js/dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
@endsection

@section('content')
    <div class="admin-dashboard container mt-4">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="recipeType">Filtriraj po tipu obroka:</label>
                <select id="recipeType" class="form-control">
                    <option value="">Svi tipovi</option>
                    <option value="1">Doručak</option>
                    <option value="2">Ručak, Večera</option>
                    <option value="3">Užina</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table table-striped table-bordered', 'id' => 'recipe-table']) }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scriptsBottom')
    {{ $dataTable->scripts() }}
    <script>
        $(document).ready(function () {
            const table = $('#recipe-table').DataTable();

            $('#recipeType').on('change', function () {
                table.ajax.reload();
            });
        });
    </script>
@endsection

