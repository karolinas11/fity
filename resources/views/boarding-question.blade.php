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
                        <option data-question-id="{{ $question->id }}" value="{{ $question->name_question }}">{{ $question->title }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-danger mt-2 delete-question">Izbrisi pitanje</button>
            </div>
        </div>
       <div class="col-4">
           <h3>Izmeni pitanje</h3>
           <select id="edit-questions" name="edit-questions" class="form-select">
               @foreach($questions as $question)
                   <option  data-question-id="{{ $question->id }}" data-question-type="{{ $question->type }}" value="{{ $question->name_question }}">{{ $question->title }}</option>
               @endforeach
           </select>
           <button type="button" class="btn btn-dark mt-2 edit-question-btn">Izmeni pitanja</button>
           <div class="new-option-container edit-question-container mt-4" style="display:none;">
                <input type="text" id="edit-question-text" class="form-control mb-2" placeholder="pitanje">
                <input type="text" id="edit-question-type" class="form-control mb-2" placeholder="tip pitanja(select,input)">
                <input type="text" id="edit-question-name" class="form-control mb-2" placeholder="name_question za id name">
                <button type="submit" class="btn btn-success" id="update-question">Izmenite</button>
           </div>
       </div>
    </div>
    @foreach($questions as $question)
            <div id="question-{{ $question->id }}" class="container mt-5">
                <b>Naslov:</b> <label for="{{ $question->id }}" class="form-label">{{ $question->title }}</label><br>
                @if($question->type === 'select')
                    <select name="{{ $question->name_question }}" id="{{ $question->name_question }}" class="form-select main-select">
                        @foreach($question->options as $option)
                            <option value="{{ $option->name_option }}" data-subtitle="{{ $option->subtitle }}" data-value="{{ $option->value }}" data-name="{{ $option->name_option }}">
                                <p id="option-title">{{ $option->value }}</p> | |
                                <p id="option-subtitle">{{ $option->subtitle }}</p>
                            </option>
                        @endforeach
                    </select>

                    <button type="button" class="btn btn-primary mt-2 add-option-btn" data-question-id="{{ $question->id }}">
                        Dodaj opciju
                    </button>

                    <button type="button" class="btn btn-danger mt-2 delete-option-btn">Izbrisi opciju</button>
                    <button type="button" class="btn btn-dark mt-2 update-option-btn">Izmeni opciju</button>
                    <div class="new-option-container update-option-container mt-2" style="display:none;">
                        <p class="mt-4">Izaberi sta zelis da izmenis!</p>
                        <select data-question-id="{{ $question->id }}" name="{{ $question->name_question }}" id="{{ $question->name_question }}" class="form-select mb-4">
                            @foreach($question->options as $option)
                                <option data-option-id="{{ $option->id }}" value="{{ $option->name_option }}" data-subtitle="{{ $option->subtitle }}" data-value="{{ $option->value }}" data-name="{{ $option->name_option }}">
                                    <p id="option-title">{{ $option->value }}</p> | |
                                    <p id="option-subtitle">{{ $option->subtitle }}</p>
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-dark mt-2 open">Popuni inpute</button>
                        <input id="id-option" type="text" class="form-control">
                        <input type="text" id="value_option" class="form-control">
                        <input type="text" id="subtitle_option" class="form-control">
                        <input type="text" id="name_option" class="form-control">
                        <button type="button" class="btn btn-success mt-2 update-option" data-question-id="{{ $question->id }}">Sacuvaj izmene</button>
                    </div>

                    <div class="new-option-container create-container mt-2" style="display: none;">
                        <input type="text" class="form-control new-option-input" placeholder="Nova opcija">
                        <input type="text" class="form-control new-subtitle-input" placeholder="Unesite subtitle obavezno!">
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
    <script src="{{ asset('js/script-question-option.js') }}"></script>
@endsection
