function getDropdownValue() {
        var cat = document.getElementById("category");
        var catname = cat.options[cat.selectedIndex].text;
        return cat.options[cat.selectedIndex].value;
}
function getCategory(){
        window.location.href = "/coupondunia/" + getDropdownValue();
}
$(document).ready(function() {
$('#subcat').click(function() {
    $.post('Filter.php', {filterBy: getDropdownValue(), fType: 'subcat'}, function(result) {
    $('#feedback').html(result);
    });

    return false;
});
$('#ctype').click(function() {
    $.post('Filter.php', {filterBy: getDropdownValue(), fType: 'coupontype'}, function(result) {
    $('#feedback').html(result);
    });
    return false;
});
$('#store').click(function() {
    $.post('Filter.php', {filterBy: getDropdownValue(), fType: 'store'}, function(result) {
    $('#feedback').html(result);
    });
    return false;
});
//$('button.page').click(function() {
//$('#display-area button').on('click', function(e) {
$('#display-area').on('click','button[class="page"]', function() {
    $('#display-area').hide();
    $('#loadingmessage').show(); 
    var id = '#' + $(this).attr('id');
    var name = $('#ftype').attr("name");
    if(name == null) {
        name = "all";
    }
    var checkedArray = $(":checkbox:checked").map(function() {
        return this.value
    }).get();
    //var a = document.getElementById(id);
    var val = $(this).attr('value');
    //var value = $(id).attr("value");
    //value = parseInt(val);
   // alert("hello " + val*);
    $.post('Filter.php', {checkedList: checkedArray, cid: getDropdownValue(), ftype: name, pageno: val, mode: 'display'}, function(result) {
        $('#display-area').html(result).show;
        $('#display-area').show();
        $('#loadingmessage').hide(); 
    });
    return false;
   });
});

function refreshContent(val) {
    $('#display-area').hide();
    $('#loadingmessage').show(); 
    var name = $('#ftype').attr("name");
    var checkedArray = $(":checkbox:checked").map(function() {
        return this.value
    }).get();
    var res = "";
    $.post('Filter.php', {checkedList: checkedArray, cid: getDropdownValue(), ftype: name, pageno: '1', mode: 'display'}, function(result) {
        $('#display-area').html(result).show;
        $('#display-area').show();
        $('#loadingmessage').hide(); 
    });
    return false;
}
 