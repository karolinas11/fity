@extends('layouts.app')


@section('content')
    @foreach($questions as $question)
            <div class="container mt-2">

                <label for="{{$question->id}}" class="form-label">{{ $question->title }}</label>

                @if($question->type === 'select')
                    <select name="{{ $question->name_question }}" id="{{ $question->name_question }}" class="form-select">
                        @foreach($question->options as $option)
                            <option value="{{ $option->name_option }}">{{ $option->value }}</option>
                        @endforeach
                    </select>

                    <button type="button" class="btn btn-primary mt-2 add-option-btn" data-question-id="{{ $question->id }}">
                        Dodaj opciju
                    </button>

                    <button type="button" class="btn btn-danger mt-2 delete-option-btn">Izbrisi opciju</button>

                    <div class="new-option-container create-container mt-2" style="display: none;">
                        <input type="text" class="form-control new-option-input" placeholder="Nova opcija">
                        <button type="button" class="btn btn-success mt-2 save-option-btn" data-question-id="{{ $question->id }}">
                            Sačuvaj opciju
                        </button>
                    </div>

                    <div class="new-option-container delete-container mt-2" style="display: none;">
                        <select name="{{ $question->name_question }}" id="{{ $question->name_question }}" class="form-select">
                            @foreach($question->options as $option)
                                <option value="{{ $option->name_option }}">{{ $option->value }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-danger mt-2 remove-option-btn" data-question-id="{{ $question->id }}">
                            Sačuvaj promene
                        </button>
                    </div>

                @else
                    <input type="{{$question->type}}" class="form-control" name="{{$question->name_question}}">
                @endif

            </div>
    @endforeach
@endsection

@section('scriptsBottom')
    <script>
        document.addEventListener("DOMContentLoaded",function(){
            document.querySelectorAll(".add-option-btn").forEach(button=>{
                button.addEventListener("click", function(){
                    const container = this.closest('.container').querySelector(".create-container");
                    container.style.display = "block";
                });
            });
            document.querySelectorAll(".delete-option-btn").forEach(button=>{
               button.addEventListener("click",function(){
                   const container2 = this.closest('.container').querySelector(".delete-container");
                   container2.style.display = "block";
               });
            });
            document.querySelectorAll(".remove-option-btn").forEach(button =>{
                button.addEventListener("click", function(){
                    const questionId = this.getAttribute("data-question-id");
                    console.log(`Question ID: ${questionId}`);


                    const container = this.closest(".delete-container");

                    if (container) {

                        const selectElement = container.querySelector("select");
                        const selectedOption = selectElement ? selectElement.value : null;

                        if (selectedOption) {
                            fetch("/api/delete-option", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                },
                                body: JSON.stringify({
                                    question_id: questionId,
                                    value: selectedOption
                                })
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        alert("Opcija je uspešno izbrisana");

                                        const optionToRemove = selectElement.querySelector(`option[value="${selectedOption}"]`);
                                        if (optionToRemove) {
                                            optionToRemove.remove();
                                        }
                                    } else {
                                        alert("Opcija nije izbrisana");
                                    }
                                })
                                .catch(error => {
                                    console.error("Greška u API zahtevu:", error);
                                    alert("Došlo je do greške. Pogledajte konzolu za više informacija.");
                                });
                        } else {
                            alert("Molimo odaberite opciju koju želite da obrišete.");
                        }
                    } else {
                        console.error("Kontejner za brisanje nije pronađen.");
                    }
                });
            });

            document.querySelectorAll(".save-option-btn").forEach(button=>{
                button.addEventListener("click", function(){
                const questionId = this.getAttribute("data-question-id");
                const newOptionInput = this.previousElementSibling;
                const newOptionValue = newOptionInput.value;

                if(newOptionValue.trim() === ""){
                    alert("Unesite naziv opcije!");
                    return;
                }

                fetch("api/add-option",{
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        question_id: questionId,
                        name_option: newOptionValue,
                        value: newOptionValue
                    })
                })
                    .then(response=>response.json())
                    .then(data=>{
                        if(data.success){
                            console.log(data);
                            const select=document.querySelector(`#${data.question_name}`);
                            const newOption=document.createElement("option");
                            newOption.value=data.name_option;
                            newOption.textContent=data.value;
                            select.appendChild(newOption);

                            newOptionInput.value="";
                            alert("Opcija uspesno dodata!");
                        } else{
                            alert("Doslo je do greske. Pokusajte ponovo");
                        }
                    })
                   /* .catch(error => console.error("Greska:", error));*/
                });
            });
        });
    </script>
@endsection
