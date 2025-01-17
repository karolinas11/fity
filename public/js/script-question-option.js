
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
           const option_id = this.getAttribute("data-option-id");
           console.log(`Option ID: ${option_id}`);
           if(option_id){
               fetch("api/delete-option",{
                   method: "POST",
                   headers:{
                       "Content-Type": "application/json",
                       "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                   },
                   body: JSON.stringify({
                       option_id: option_id
                   })
               })
               .then(response => response.json())
               .then(data=>{
                   if(data.success)
                   {
                       alert("Opcija je uspesno izbrisana");
                       document.querySelectorAll(`option[value="${option_id}"]`).forEach(optionToRemove => {
                           optionToRemove.remove();
                       });
                   }else {
                       alert("Opcija nije izbrisana"+ (data.error || "Nepoznata greška."));
                   }
               })
               .catch(error=>{
                   console.error("Greska u Api zahtevu:", error);
                   alert("Došlo je do greške. Pogledajte konzolu za više informacija.");
               });
           }else{
               alert("ID opcije nije pronadjen");
           }


    });
});

    document.querySelectorAll(".save-option-btn").forEach(button=>{
        button.addEventListener("click", function(){
            const questionId = this.getAttribute("data-question-id");
            const container = this.closest(".new-option-container");
            const newTitleInput = container.querySelector(".new-title-input");
            const newSubtitleInput = container.querySelector(".new-subtitle-input");
            const newNameInput = container.querySelector(".new-name-input");
            const newDataValueInput= container.querySelector(".new-datavalue-input");

            const newTitleValue = newTitleInput.value.trim();
            const newSubtitleValue = newSubtitleInput.value.trim();
            const newNameValue = newNameInput.value.trim();
            const newDataValue = newDataValueInput.value.trim();

            if (!newTitleValue || !newNameValue) {
                alert("Molimo popunite sva polja!");
                return;
            }

            console.log(newTitleValue, newSubtitleValue, newNameValue, newDataValue);
            fetch("/api/add-option", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    question_id: questionId,
                    name_option: newNameValue,
                    value: newTitleValue,
                    subtitle: newSubtitleValue,
                    data_value: newDataValue,
                }),
            })
            .then(response => response.json())
            .then(data => {
                    if (data.success) {
                        const selects = document.querySelectorAll(`select[name="${data.question_name}"]`);
                        const newOption = document.createElement("option");
                        newOption.setAttribute("data-subtitle", data.subtitle);
                        newOption.setAttribute("data-title", data.value);
                        newOption.setAttribute("data-name", data.name_option);
                        newOption.setAttribute("data-datavalue", data.data_value);

                        newOption.innerHTML = `<p id="option-title">${data.value}</p> | <p id="option-subtitle">${data.subtitle}</p> | <p id="option-datavalue">${data.data_value}</p> | <p id="option-name">${data.name_option}</p>`;

                        // Dodaj novu opciju u svaki relevantni select
                        selects.forEach(select => {
                            select.appendChild(newOption.cloneNode(true));
                        });
                        newTitleInput.value = "";
                        newSubtitleInput.value = "";
                        newNameInput.value = "";
                        newDataValueInput.value = "";
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
        const description = document.querySelector("#new_description").value;
        console.log(title, type, name_question, description);
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
                description: description
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
                newOption.setAttribute('data-question-description', data.question.description);
                deleteQuestionSelect.appendChild(newOption);

                document.querySelector("#new_pitanje").value = "";
                document.querySelector("#new_type").value = "";
                document.querySelector("#new_id").value = "";
                document.querySelector("#new_description").value = "";
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
                    const questionDescription = selectedOption.getAttribute("data-question-description") || "";

                    document.getElementById("edit-question-text").value = questionTitle;
                    document.getElementById("edit-question-type").value = questionType;
                    document.getElementById("edit-question-name").value = questionName;
                    document.getElementById("edit-question-description").value = questionDescription;
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
        const updatedDescription = document.getElementById("edit-question-description").value;


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
                description: updatedDescription
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
            const value1 = selectedOption.getAttribute('data-title');
            const subtitle = selectedOption.getAttribute('data-subtitle');
            const data_value = selectedOption.getAttribute('data-datavalue');
            const name = selectedOption.getAttribute('data-name');
            console.log(id, value1, subtitle, data_value, name);
            container.querySelector('#id-option').value = id;
            container.querySelector('#value_option').value = value1;
            container.querySelector('#subtitle_option').value = subtitle;
            container.querySelector('#data_value').value = data_value;
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

