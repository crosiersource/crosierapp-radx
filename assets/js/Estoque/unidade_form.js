'use strict';

import $ from "jquery";
import JSONEditor from "jsoneditor";

import 'jsoneditor/dist/jsoneditor.css';


$(document).ready(function () {


    $('.isJson').each(function () {

        let thiz = $(this);

        let options = {
            "mode": "tree",
            "modes": [
                "tree",
                "form",
                "code",
                "text",
                "view"
            ],
            "history": false,
            onChange: function () {
                if (editor) {
                    $('#' + thiz.data('hiddenInputId')).val(editor.getText());
                }
            }
        };
        let editor = new JSONEditor(this, options);
        // let text = "[{\"test\":\"test\"}]";
        // let json = JSON.parse(text);
        editor.set($(this).data('value'));
    });


});