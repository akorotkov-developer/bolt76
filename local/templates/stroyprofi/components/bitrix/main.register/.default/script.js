$(document).ready(function() {
    $( "form[name='regform']" ).submit(function( event ) {
        if ($(".bt-input").val() != '') {
            alert('Ваши действия кажутся нам подозрительными');
            event.preventDefault();
        }
    });
});