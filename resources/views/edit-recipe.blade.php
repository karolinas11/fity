@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h2 class="mb-4">Izmeni recept</h2>
        <p><strong>Proteini: </strong>{{ round($proteinPercentage, 2) }}%</p>
        <p><strong>Masti: </strong>{{ round($fatPercentage, 2) }}%</p>
        <p><strong>Ugljeni hidrati: </strong>{{ round($carbPercentage, 2) }}%</p>
        <hr>
        <div style="position: sticky; width: 100%; padding-top: 70px; background: white; top: 0; z-index: 1000;">
            <h5 style="text-align: center;">Cal procenti</h5>
            <div style="display: flex; flex-direction: row; justify-content: space-between;">
                <p><strong>Proteini: </strong>{{ round($proteinCalPercentage, 2) }}%</p>
                <p><strong>Masti: </strong>{{ round($fatCalPercentage, 2) }}%</p>
                <p><strong>Ugljeni hidrati: </strong>{{ round($carbCalPercentage, 2) }}%</p>
            </div>
        </div>
        <form id="recipe-form">
            @csrf
            <div class="form-group mb-3">
                <label for="featured_image">Glavna slika recepta</label>
                <input type="file" name="featured_image" class="form-control">
            </div>
            @if($recipe->featured_image)
                <div class="row mb-3">
                    <div class="col-md-2">
                        <img id="preview-image" src="{{ asset('storage/featured_recipes/' . $recipe->featured_image) }}" alt="Glavna slika" style="width: 100px; height: auto;">
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

            <div class="form-group mb-3">
                <label for="gallery_images">Galerija slika</label>
                <input type="file" id="gallery_images" name="gallery_images[]" class="form-control" multiple>
            </div>

            @if($recipe->galleryImages && count($recipe->galleryImages))
                <div id="imagePreviewContainer" class="d-flex flex-wrap gap-2 mb-4">
                    @foreach($recipe->galleryImages as $galleryImage)
                        <div style="position: relative;">
                            <img src="{{ asset('storage/gallery_recipes/' . $galleryImage->image_path) }}" style="width: 100px; height: auto; margin:5px;">
                            <!-- Opcionalno dugme za brisanje slike -->
                        </div>
                    @endforeach
                </div>
            @else
                <div id="imagePreviewContainer" class="d-flex flex-wrap gap-2"></div>
            @endif

            <div class="form-group mb-3">
                <label for="name">Naziv recepta</label>
                <input type="text" value="{{ $recipe->name }}" name="name" class="form-control" placeholder="Unesite naziv recepta">
            </div>

            <div class="form-group mb-3">
                <label for="description">Upustvo i opis</label>
                <textarea name="description" class="form-control" placeholder="Unesite upustvo i opis">{{ $recipe->description }}</textarea>
            </div>

            <div class="form-group mb-3">
                <label for="short_description">Kratki opis</label>
                <textarea name="short_description" class="form-control" placeholder="Unesite kratki opis">{{ $recipe->short_description }}</textarea>
            </div>



            <div class="form-group mb-3">
                <label for="type">Tip obroka</label>
                <select name="type" class="form-select">
                    <option @if($recipe->type == 1) selected @endif value="1">Doručak</option>
                    <option @if($recipe->type == 2) selected @endif value="2">Ručak</option>
                    <option value="2">Večera</option>
                    <option @if($recipe->type == 3) selected @endif value="3">Užina</option>
                </select>
            </div>

            <div class="col-md-12">
                <label>Insulinska rezistencija</label>
                <input  @if($recipe->insulin == 1) checked @endif type="checkbox" name="insulin">
            </div>

            <div class="foodstuffs mb-4">
                @foreach($recipeFoodstuffs as $recipeFoodstuff)
                    <div class="single-foodstuff row mb-3 mt-3">
                        <div class="col-md-6">
                            <label>Namirnica</label>
                            <select name="foodstuff_id" class="form-select">
                                @foreach($foodstuffs as $foodstuff)
                                    <option @if($foodstuff->id == $recipeFoodstuff->foodstuff_id) selected @endif value="{{ $foodstuff->id }}">{{ $foodstuff->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="amount">Količina (g)</label>
                            <input value="{{ $recipeFoodstuff->amount }}" type="number" name="amount" class="form-control" placeholder="Unesite količinu">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger remove-foodstuff">Ukloni</button>
                        </div>



                        <div class="col-md-12">
                            <label>Nosilac proteina</label>
                            <input @if($recipeFoodstuff->proteins_holder) checked @endif type="checkbox" name="proteins_holder">
                        </div>
                        <div class="col-md-12">
                            <label>Nosilac masti</label>
                            <input @if($recipeFoodstuff->fats_holder) checked @endif type="checkbox" name="fats_holder">
                        </div>
                        <div class="col-md-12">
                            <label>Nosilac ugljenih hidrata</label>
                            <input @if($recipeFoodstuff->carbohydrates_holder) checked @endif type="checkbox" name="carbohydrates_holder">
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-success" id="add-foodstuff">Dodaj namirnicu</button>
            </div>

            <button type="button" class="mt-5 btn btn-primary" id="submit-recipe">Izmeni</button>

        </form>
    </div>
@endsection

@section('scriptsBottom')
    <script>
        let addFoodstuffBtn = document.getElementById('add-foodstuff');
        let foodstuffContainer = document.querySelector('.foodstuffs');

        addFoodstuffBtn.addEventListener('click', function() {
            let newFoodstuff = document.createElement('div');
            newFoodstuff.classList.add('single-foodstuff', 'row', 'mb-3');
            newFoodstuff.innerHTML = `
                <div class="col-md-6">
                    <label>Namirnica</label>
                    <select name="foodstuff_id" class="form-select">
                        @foreach($foodstuffs as $foodstuff)
            <option value="{{ $foodstuff->id }}">{{ $foodstuff->name }}</option>
                        @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="amount">Količina (g)</label>
            <input type="number" name="amount" class="form-control" placeholder="Unesite količinu">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="button" class="btn btn-danger remove-foodstuff">Ukloni</button>
        </div>
        <div class="col-md-12">
            <label>Nosilac proteina</label>
            <input type="checkbox" name="proteins_holder" value="1">
        </div>
        <div class="col-md-12">
            <label>Nosilac masti</label>
            <input type="checkbox" name="fats_holder" value="1">
        </div>
        <div class="col-md-12">
            <label>Nosilac ugljenih hidrata</label>
            <input type="checkbox" name="carbohydrates_holder" value="1">
        </div>
`;
            foodstuffContainer.appendChild(newFoodstuff);

            attachRemoveHandler();
        });

        function attachRemoveHandler() {
            let removeButtons = document.querySelectorAll('.remove-foodstuff');
            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    button.closest('.single-foodstuff').remove();
                });
            });
        }

        attachRemoveHandler();

        let submitBtn = document.getElementById('submit-recipe');
        submitBtn.addEventListener('click', function() {
            let foodstuffs = document.querySelectorAll('.single-foodstuff');
            let foodstuffData = [];

            foodstuffs.forEach(foodstuff => {
                let foodstuffId = foodstuff.querySelector('select[name="foodstuff_id"]').value;
                let amount = foodstuff.querySelector('input[name="amount"]').value;
                let proteinsHolder = foodstuff.querySelector('input[name="proteins_holder"]').checked ? 1 : 0;
                let fatsHolder = foodstuff.querySelector('input[name="fats_holder"]').checked ? 1 : 0;
                let carbohydratesHolder = foodstuff.querySelector('input[name="carbohydrates_holder"]').checked ? 1 : 0;

                foodstuffData.push({
                    foodstuff_id: foodstuffId,
                    amount: amount,
                    proteins_holder: proteinsHolder,
                    fats_holder: fatsHolder,
                    carbohydrates_holder: carbohydratesHolder,
                });
            });

            let imageInput = document.querySelector('input[name="featured_image"]');
            let formData= new FormData();
            if (imageInput.files.length > 0 ){
                formData.append('featured_image', imageInput.files[0]);
            }

            let galleryInput = document.querySelector('input[name="gallery_images[]"]');
            let galleryFiles = galleryInput.files;
            if(galleryFiles.length > 0){
                for (let i = 0; i < galleryFiles.length; i++) {
                    formData.append(`gallery_images[]`, galleryFiles[i]);
                }
            }

            formData.append('_token', " {{csrf_token() }}");
            formData.append('name', document.querySelector('input[name="name"]').value);
            formData.append('description', document.querySelector('textarea[name="description"]').value);
            formData.append('short_description', document.querySelector('textarea[name="short_description"]').value);
            formData.append('type', document.querySelector('select[name="type"]').value);
            formData.append('insulin', document.querySelector('input[name="insulin"]').checked ? 1 : 0);
            formData.append('foodstuffs', JSON.stringify(foodstuffData));

            jQuery.ajax({
                url: "{{ route('edit-recipe', $recipe->id) }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(result) {
                    alert('Recept uspešno izmenjen!');
                    window.location.href = window.origin + '/recipes/';
                }
            });

        });

        document.getElementById('gallery_images').addEventListener('change', function(event) {
            let container = document.getElementById('imagePreviewContainer');
            container.innerHTML = '';

            Array.from(event.target.files).forEach(file => {
                let reader = new FileReader();
                reader.onload = function(e) {
                    let img = document.createElement('img');
                    img.setAttribute('src', e.target.result);
                    img.style.width = '100px';
                    img.style.margin = '5px';
                    container.appendChild(img);
                }
                reader.readAsDataURL(file);
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
