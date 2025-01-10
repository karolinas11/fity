
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

                        document.querySelectorAll(`select[name="${selectElement.name}"]`).forEach(select => {

                            const optionToRemove = select.querySelector(`option[value="${selectedOption}"]`);
                            if (optionToRemove) {
                                optionToRemove.remove();
                            }
                        });
                    }
                     else {
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
            const container = this.closest(".new-option-container");
            const newOptionInput = container.querySelector(".new-option-input");
            const newSubtitleInput = container.querySelector(".new-subtitle-input");

            const newOptionValue = newOptionInput.value.trim();
            const newSubtitleValue = newSubtitleInput.value.trim();

            if (!newOptionValue || !newSubtitleValue) {
                alert("Molimo popunite i naslov i podnaslov opcije!");
                return;
            }

            console.log(newOptionValue, newSubtitleValue);
            fetch("/api/add-option", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    question_id: questionId,
                    name_option: newOptionValue,
                    value: newOptionValue,
                    subtitle: newSubtitleValue,
                }),
            })
            .then(response => response.json())
            .then(data => {
                    if (data.success) {
                        const selects = document.querySelectorAll(`select[name="${data.question_name}"]`);
                        const newOption = document.createElement("option");
                        newOption.value = data.name_option;
                        newOption.setAttribute("data-subtitle", data.subtitle);
                        newOption.setAttribute("data-value", data.value);
                        newOption.setAttribute("data-name", data.name_option);


                        newOption.innerHTML = `<p id="option-title">${data.value}</p> | |  <p id="option-subtitle">${data.subtitle}</p>`;

                        // Dodaj novu opciju u svaki relevantni select
                        selects.forEach(select => {
                            select.appendChild(newOption.cloneNode(true));
                        });
                        newOptionInput.value = "";
                        newSubtitleInput.value = "";

                        alert("Opcija uspešno dodata!");
                    } else {
                    alert("Došlo je do greške. Pokušajte ponovo.");
                    }
             })
            .catch(error => {
                console.error("Greška:", error);
                alert("Došlo je do greške pri slanju zahteva.");
            });
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
        console.log(title, type, name_question);
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
                const deleteQuestionSelect = document.querySelector(".delete-question-container select");
                const newOption = document.createElement("option");

                newOption.value = data.question.name_question;
                newOption.textContent = data.question.title;
                newOption.setAttribute('data-question-id', data.question.id);
                deleteQuestionSelect.appendChild(newOption);

                document.querySelector("#new_pitanje").value = "";
                document.querySelector("#new_type").value = "";
                document.querySelector("#new_id").value = "";
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

    document.querySelector(".delete-question").addEventListener("click", function () {
        const selectElement = document.getElementById("questions");
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const questionId = selectedOption.getAttribute("data-question-id");

        if (questionId) {
            console.log("ID izabranog pitanja:", questionId);

            fetch("api/delete-question", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                 },
                body: JSON.stringify({ id: questionId })
            })
            .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
            })
            .then(data => {
                    console.log("Server Response:", data);
                    if (data.success) {
                        alert(data.message);
                        const questionElement = document.getElementById(`question-${questionId}`);
                        if (questionElement) {
                            questionElement.remove();
                        }
                        selectedOption.remove();
                    } else {
                        alert(data.message);
                    }
            })
            .catch(error => {
                console.error("Greška:", error);
                alert("Došlo je do greške pri slanju zahteva: " + error.message);
            });
         } else {
            alert("Molimo odaberite pitanje za brisanje.");
        }
    });
    document.querySelectorAll(".edit-question-btn").forEach(button => {
        button.addEventListener("click",function(){
            const select = document.querySelector("#edit-questions"); // Select element
            const selectedOption = select.options[select.selectedIndex]; // Selektovani option
            const questionId = selectedOption ? selectedOption.getAttribute('data-question-id') : null;

            const container = this.closest('.container').querySelector(".edit-question-container");

            if (questionId) {
                if (container.style.display === "block") {
                    container.style.display = "none";
                } else {
                    container.style.display = "block";

                    const questionTitle = selectedOption.textContent; // Naslov pitanja
                    const questionType = selectedOption.getAttribute("data-question-type") || ""; // Pretpostavljamo da je tip pitanja opcionalan
                    const questionName = selectedOption.value; // name_question

                    document.getElementById("edit-question-text").value = questionTitle;
                    document.getElementById("edit-question-type").value = questionType;
                    document.getElementById("edit-question-name").value = questionName;
                    document.getElementById("update-question").addEventListener("click",function(){
                        updateQuestion(questionId);
                    });
                }
            } else {
                alert("Molimo selektujte pitanje da biste ga izmenili.");
            }
        });
    });
    function updateQuestion(questionId) {
        const updatedTitle = document.getElementById("edit-question-text").value;
        const updatedType = document.getElementById("edit-question-type").value;
        const updatedName = document.getElementById("edit-question-name").value;


        fetch(`/api/update-question/${questionId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
            },
            body: JSON.stringify({
                id: questionId,
                title: updatedTitle,
                type: updatedType,
                name_question: updatedName,
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Pitanje je uspesno izmenjeno!");
                    document.querySelector(".edit-question-container").style.display = "none";
                } else {
                    alert("Doslo je do greske prilikom izmene!");
                }
            })
            .catch(error => {
                console.error("Greška: ", error);
                alert("Došlo je do greške pri slanju zahteva: " + error.message);
            });
    }
    /**/



    document.querySelectorAll('.update-option-btn').forEach(button => {
        button.addEventListener('click', function () {
            const container = button.nextElementSibling;
           if(container.style.display === 'none')
           {
               container.style.display = 'block';
           }else{
               container.style.display = 'none';
           }
        });
    });
    document.querySelectorAll('.open').forEach(button => {
        button.addEventListener('click', function () {
            const container = button.closest('.update-option-container');
            const selectElement = container.querySelector('select');
            const selectedOption = selectElement.options[selectElement.selectedIndex];

            const id = selectedOption.getAttribute('data-option-id');
            const value = selectedOption.getAttribute('data-value');
            const subtitle = selectedOption.getAttribute('data-subtitle');
            const name = selectedOption.getAttribute('data-name');

            container.querySelector('#id-option').value = id;
            container.querySelector('#value_option').value = value;
            container.querySelector('#subtitle_option').value = subtitle;
            container.querySelector('#name_option').value = name;
        });
    });
    document.querySelectorAll(".update-option").forEach(button => {
        button.addEventListener("click", function () {
            const container = button.closest('.update-option-container');
            const optionId=container.querySelector("#id-option").value;
            const newName = container.querySelector("#name_option").value;
            const newValue = container.querySelector("#value_option").value;
            const newSubtitle = container.querySelector("#subtitle_option").value;
            console.log(optionId);
                if (newName && newValue && newSubtitle) {
                    fetch(`/api/update-option/${optionId}`, {
                        method: 'PUT',
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({
                            name_option: newName,
                            value: newValue,
                            subtitle: newSubtitle,
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert("Opcija je uspešno ažurirana!");
                                // Ažuriranje UI-a, ako je potrebno


                            } else {
                                alert("Greška prilikom ažuriranja opcije.");
                            }
                        })
                       /* .catch(error => {
                            console.error("Greška u API zahtevu:", error);
                            alert("Došlo je do greške. Pogledajte konzolu za više informacija.");
                        });*/
                } else {
                    alert("Molimo popunite sva polja za ažuriranje.");
                }
        });
    });
});

