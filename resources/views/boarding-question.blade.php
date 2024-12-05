@extends('layouts.app')


@section('content')
    <div class="container d-flex mt-5">
        <div class="col-4">
            <h3>Dodaj pitanje</h3>
        <button type="button" class="btn btn-primary mt-2 add-question-btn">Prikaži pitanje</button>
        <div class="new-option-container add-question-container mt-4" style="display: none;">
            <input type="text" id="new_pitanje" class="form-control mb-2" placeholder="unesite pitanje...">
            <input type="text" id="new_type" class="form-control mb-2" placeholder="unesite tip pitanja(number,text,date,select....)">
            <input type="text" id="new_id" class="form-control mb-2" placeholder="unesite njegov id,name">

            <button type="button" class="btn btn-success mt-2 add-question">Dodaj pitanje</button>
        </div>
        </div>
        <div class="col-4">
            <h3>Izbrisi pitanje</h3>
            <button type="button" class="btn btn-danger mt-2 delete-question-btn" >Izbrisi željeno pitanje</button>
            <div class="new-option-container delete-question-container mt-4" style="display: none;">
                <select name="questions" id="questions" class="form-select">
                    @foreach($questions as $question)
                        <option data-question-id="{{ $question->id }}" value="{{$question->name_question}}">{{$question->title}}</option>
                    @endforeach
                </select>

                <button type="button" class="btn btn-danger mt-2 delete-question">Izbrisi pitanje</button>
            </div>
        </div>
    </div>
    @foreach($questions as $question)
            <div class="container mt-5">

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

                    if(container.style.display === "block"){
                        container.style.display = "none";
                    }else{
                        container.style.display = "block";
                    }
                });
            });
            document.querySelectorAll(".delete-option-btn").forEach(button=>{
               button.addEventListener("click",function(){
                   const container2 = this.closest('.container').querySelector(".delete-container");
                   if(container2.style.display === "block"){
                       container2.style.display = "none";
                   }else{
                       container2.style.display = "block";
                   }
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

                                        const mainSelectElement = document.querySelector(`select[name="${selectElement.name}"]`);
                                        if (mainSelectElement) {
                                            const optionToRemoveFromMain = mainSelectElement.querySelector(`option[value="${selectedOption}"]`);
                                            if (optionToRemoveFromMain) {
                                                optionToRemoveFromMain.remove();
                                            }
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


            /*OVO JE ZA DODAVANJE PITANJA*/
            document.querySelectorAll(".add-question-btn").forEach(button =>{
                button.addEventListener("click",function(){
                    const container3 = this.closest('.container').querySelector(".add-question-container");
                    if(container3.style.display === "block"){
                        container3.style.display = "none";
                    }else {
                        container3.style.display = "block";
                    }
                });
            });

            document.querySelector(".add-question").addEventListener("click", function(){
                //Prkupi podatke iz input
                const title = document.querySelector("#new_pitanje").value;
                const type = document.querySelector("#new_type").value;
                const name_question = document.querySelector("#new_id").value;
                console.log(title,type,name_question);
                if (!title || !type || !name_question) {
                    alert("Molimo popunite sva polja.");
                    return;
                }
                fetch("api/add-question",{
                   method: "POST",
                   headers: {
                       "Content-Type": "application/json",
                       "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                   },
                   body: JSON.stringify({
                      title: title,
                      type: type,
                      name_question: name_question,
                   }),
                })
                .then((response)=>response.json())
                .then((data)=>{
                    if(data.success){
                        alert("Pitanje je uspesno dodato!");
                        /*addQuestionToDOM(data.question);*/

                    }else{
                        alert("Greska prilikom dodavanja pitanja");
                    }
                })
                .catch((error)=>{
                    console.error("Greska:", error);
                });
            });

            /*OVO JE ZA BRISANJE PITANJA ISPOD */
            document.querySelectorAll(".delete-question-btn").forEach(button => {
                button.addEventListener("click", function(){
                   const container4 = this.closest('.container').querySelector(".delete-question-container");
                   if(container4.style.display === "block"){
                       container4.style.display = "none";
                   }else{
                       container4.style.display = "block";
                   }
                });
            });

            document.querySelector(".delete-question").addEventListener("click", function (){
                const selectElement = document.getElementById("questions");
                const selectedOption = selectElement.options[selectElement.selectedIndex];
                const questionId = selectedOption.getAttribute("data-question-id");

                if (questionId) {
                    console.log("ID izabranog pitanja:", questionId);

                    fetch("/api/delete-question", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ id: questionId })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert("Pitanje uspešno izbrisano!");
                                selectedOption.remove();
                            } else {
                                alert("Greška pri brisanju pitanja.");
                            }
                        })
                        .catch(error => {
                            console.error("Greška:", error);
                            alert("Došlo je do greške pri slanju zahteva.");
                        });
                } else {
                    alert("Molimo odaberite pitanje za brisanje.");
                }
            })
        });
    </script>
@endsection
