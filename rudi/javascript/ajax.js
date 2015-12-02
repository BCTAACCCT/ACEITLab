/**
 * Created by Michael on 10/4/2015.
 */

//const ajContentResponder = "/app/content_responder.php";
const ajDataResponder = "ace_rudi_data_responder.php";

function jqXHR(method, url, data, contentType, dataType) {
    return $.ajax({
        method: method,             // 'POST' |'GET' | 'PUT' | 'DELETE'
        url: url,                   // url of server side php responder page
        data: data,                 // data to send
        contentType: contentType,   // e.g. 'application/x-www-form-urlencoded' | 'application/json'
        dataType: dataType,         // 'json' | 'text' | 'html'
        beforeSend: function () {
        },
        success: function () {      // array of functions
        },
        error: function (xhr, textStatus, thrownError) {        // array of functions
            console.log(textStatus + ': ' + xhr.status + ' - ' + thrownError);
        },
        complete: function () {     // array of functions
        }
    });
}

function ajGetContent(query) {
    return jqXHR(
        'GET',
        ajContentResponder,
        query,
        'application/x-www-form-urlencoded; charset=UTF-8',
        'html'
    );
}

function ajGetTF(query) {
    return jqXHR(
        'GET',
        ajDataResponder,
        query,
        'application/x-www-form-urlencoded; charset=UTF-8',
        'text'
    )
}

function ajPostForm(query) {
    return jqXHR(
        'POST',
        ajDataResponder,
        query,
        'application/x-www-form-urlencoded; charset=UTF-8',
        'text'
    );
}

function ajGetJSON(query) {
    return jqXHR(
        'GET',
        ajDataResponder,
        query,
        'application/x-www-form-urlencoded; charset=UTF-8',
        'json'
    );
}

function ajPostJSON(json) {
    return jqXHR(
        'POST',
        ajDataResponder,
        json,
        'application/json; charset=UTF-8',
        'json'
    );
}

function ajPostObj(jsObj) {
    return ajPostJSON(JSON.stringify(jsObj));
}
