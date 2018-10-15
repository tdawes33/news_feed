
$(document).ready(function() {
    var date_options = { 
        monthNames: ['一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月'],
        dayNamesMin: ['日','月','火','水','木','金','土'],
        dateFormat: 'yy-mm-dd',
        minDate: new Date(1985, 1 - 1, 1)
    };
    $(function() { $("#date").datepicker(date_options); });
});

