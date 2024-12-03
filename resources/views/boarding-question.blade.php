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

                    <div class="new-option-container mt-2" style="display: none;">
                        <input type="text" class="form-control new-option-input" placeholder="Nova opcija">
                        <button type="button" class="btn btn-success mt-2 save-option-btn" data-question-id="{{ $question->id }}">
                            Sačuvaj opciju
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
                    const container=this.nextElementSibling;
                    container.style.display = "block";
                });
            });

            document.querySelectorAll("save-option-btn").forEach(button=>{
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
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        question_id: questionId,
                        name_option: newOptionValue,
                        value: newOptionValue
                    })
                })

                });
            });
        });
    </script>
@endsection
