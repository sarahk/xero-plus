class NotesWidget {
    tagId = '#notesCard';
    formId = '#notesCardForm';
    tableBodySelector = '#notesCardTable tbody';

    constructor(keys = {}) {
        this.keys = keys;
        if ($(this.tagId).length) {
            console.log('found the card');
            this.setListeners();
            this.populateTable();
        }
    }


    populateTable() {
        $.getJSON('/json.php', {
            endpoint: 'Notes',
            action: 'ListAssociated',
            contract_id: this.keys.invoice.contract_id ?? this.keys.contract.contract_id ?? 0,
            ckcontact_id: this.keys.contact.id ?? 0,
        })
            .done((data) => {
                data.data.forEach(note => {
                    this.addNoteToTable(note);
                });
            })
            .fail((jqXHR, textStatus, errorThrown) => {
                console.error('Error populating table:', textStatus, errorThrown);
            });
    }

    saveNote() {
        let noteText = $("#notesCardText").val().trim();
        if (!noteText) {
            console.error('Note text is empty.');
            return;
        }

        $('#notesCardSubmit').prop("disabled", true);

        let data = {
            endpoint: 'save',
            form: 'notesCard',
            payload: {
                note: noteText,
                parent: $("#notesFormParent").val(),
                foreign_id: $("#notesFormForeignid").val(),
                createdby: $("#notesFormCreatedby").val(),
                createdbyname: $("#notesFormCreatedbyname").val(),
                created: $("#notesFormCreated").val(),
            }
        };

        $.ajax({
            type: "GET",
            url: "/run.php",
            data: data,
            encode: true,
        }).done(() => {
            this.addNoteToTable(data.payload);
            $("#notesCardText").val('');
            $("#notesFormCreated").val(new Date().toISOString().slice(0, 19).replace('T', ' '));
        }).fail((jqXHR, textStatus, errorThrown) => {
            console.error('Error saving note:', textStatus, errorThrown);
        }).always(() => {
            $('#notesCardSubmit').prop("disabled", false);
        });
    }

    addNoteToTable(note) {
        let createdBy = note.createdbyname ?? note.createdby;
        let newRow = `<tr>
                        <td>${note.note}</td>
                        <td>${note.created}</td>
                        <td>${createdBy}</td>
                      </tr>`;
        $(this.tableBodySelector).prepend(newRow);
    }
}

//typeof keys !== 'undefined' && keys !== null ? keys : {}
export const nsNotesWidget = new NotesWidget(getKeys());



