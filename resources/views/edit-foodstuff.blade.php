@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <form action="{{ route('edit-foodstuff', $foodstuff->id) }}" method="post">
            @csrf
            <div class="form-group mb-3">
                <label for="featured_image">Slika</label>
                <input type="file" name="featured_image" class="form-control">
            </div>
            @if($foodstuff->featured_image)
                <div class="row mb-3">
                    <div class="col-md-2">
                        <img id="preview-image" src="{{ asset('storage/foodstuffs/' . $foodstuff->featured_image) }}" alt="Glavna slika" style="width: 100px; height: auto;">
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-md-2">
                        <img id="preview-image" src="" alt="Glavna slika" style="width: 100px; height: auto; display: none;">
                    </div>
                </div>
            @endif
            <div class="row"><hr></div>

            <div class="row mb-3">
                <label for="name" class="col-md-4 col-form-label">Naziv namirnice</label>
                <div class="col-md-8">
                    <input type="text" name="name" value="{{ $foodstuff->name }}" class="form-control" placeholder="Unesite naziv namirnice">
                </div>
            </div>

            <div class="row mb-3">
                <label for="foodstuff_category_id" class="col-md-4 col-form-label">Kategorija namirnice</label>
                <div class="col-md-8">
                    <select name="foodstuff_category_id" class="form-select">
                        @foreach($foodstuffCategories as $foodstuffCategory)
                            <option {{ $foodstuffCategory->id == $foodstuff->foodstuff_category_id ? 'selected' : '' }} value="{{ $foodstuffCategory->id }}">{{ $foodstuffCategory->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <label for="amount" class="col-md-4 col-form-label">Količina</label>
                <div class="col-md-8">
                    <input type="number" value="{{ $foodstuff->amount }}" name="amount" class="form-control" placeholder="Unesite količinu">
                </div>
            </div>

            <div class="row mb-3">
                <label for="measurement_unit" class="col-md-4 col-form-label">Jedinica mere</label>
                <div class="col-md-8">
                    <input type="text" value="{{ $foodstuff->measurement_unit }}" name="measurement_unit" class="form-control" placeholder="Unesite jedinicu mere">
                </div>
            </div>

            <div class="row mb-3">
                <label for="calories" class="col-md-4 col-form-label">Kalorije</label>
                <div class="col-md-8">
                    <input type="text" value="{{ $foodstuff->calories }}" name="calories" class="form-control" placeholder="Unesite broj kalorija">
                </div>
            </div>

            <div class="row mb-3">
                <label for="proteins" class="col-md-4 col-form-label">Proteini</label>
                <div class="col-md-8">
                    <input type="text" value="{{ $foodstuff->proteins }}" name="proteins" class="form-control" placeholder="Unesite količinu proteina">
                </div>
            </div>

            <div class="row mb-3">
                <label for="fats" class="col-md-4 col-form-label">Masti</label>
                <div class="col-md-8">
                    <input type="text" value="{{ $foodstuff->fats }}" name="fats" class="form-control" placeholder="Unesite količinu masti">
                </div>
            </div>

            <div class="row mb-3">
                <label for="carbohydrates" class="col-md-4 col-form-label">Ugljeni hidrati</label>
                <div class="col-md-8">
                    <input type="text" value="{{ $foodstuff->carbohydrates }}" name="carbohydrates" class="form-control" placeholder="Unesite količinu ugljenih hidrata">
                </div>
            </div>

            <div class="row mb-3">
                <label for="min" class="col-md-4 col-form-label">Minimalna količina</label>
                <div class="col-md-8">
                    <input type="text" value="{{ $foodstuff->min }}" name="min" class="form-control" placeholder="Unesite minimalnu količinu">
                </div>
            </div>

            <div class="row mb-3">
                <label for="max" class="col-md-4 col-form-label">Maksimalna količina</label>
                <div class="col-md-8">
                    <input type="text" value="{{ $foodstuff->max }}" name="max" class="form-control" placeholder="Unesite maksimalnu količinu">
                </div>
            </div>

            <div class="row mb-3">
                <label for="max" class="col-md-4 col-form-label">Stepen promene</label>
                <div class="col-md-8">
                    <input type="text" value="{{ $foodstuff->step }}" name="step" class="form-control" placeholder="Stepen promene">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12 text-center">
                    <button type="submit" id="submit-btn" class="btn btn-primary">Pošalji</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scriptsBottom')
    <script>
        let submitBtn = document.getElementById('submit-btn');
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            let imageInput = document.querySelector('input[name="featured_image"]');
            let formData= new FormData();
            if (imageInput.files.length > 0 ){
                formData.append('featured_image', imageInput.files[0]);
            }

            formData.append('_token', " {{csrf_token() }}");
            formData.append('name', document.querySelector('input[name="name"]').value);
            formData.append('foodstuff_category_id', document.querySelector('select[name="foodstuff_category_id"]').value);
            formData.append('amount', document.querySelector('input[name="amount"]').value);
            formData.append('measurement_unit', document.querySelector('input[name="measurement_unit"]').value);
            formData.append('calories', document.querySelector('input[name="calories"]').value);
            formData.append('proteins', document.querySelector('input[name="proteins"]').value);
            formData.append('fats', document.querySelector('input[name="fats"]').value);
            formData.append('carbohydrates', document.querySelector('input[name="carbohydrates"]').value);
            formData.append('min', document.querySelector('input[name="min"]').value);
            formData.append('max', document.querySelector('input[name="max"]').value);
            formData.append('step', document.querySelector('input[name="step"]').value);

            jQuery.ajax({
                url: "{{ route('edit-foodstuff', $foodstuff->id) }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(result) {
                    alert('Namirnica uspešno izmenjena!');
                },
                error: function(xhr, status, error) {
                    console.log("Greška: ", status, error);
                }
            });
        });

        document.querySelector('input[name="featured_image"]').addEventListener('change', function(event) {
            let reader = new FileReader();
            reader.onload = function(e) {
                let img = document.getElementById('preview-image');
                img.src = e.target.result;
                img.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        });
    </script>
@endsection
