/*
Getting the dropdown value which is being selected by the user
*/
function getDropdownValue() {
        var cat = document.getElementById("category");
        var catname = cat.options[cat.selectedIndex].text;
        return cat.options[cat.selectedIndex].value;
}
function getCategory(){
        window.location.href = "/coupondunia/" + getDropdownValue();
}
/*
Contains click Listener for filter options and pagination buttons
*/
$(document).ready(function() {
    // pagination buttons on-click Listener
$('#display-area').on('click','button[class="page"]', function() {
    $('#display-area').hide();
    $('#loadingmessage').show(); 
    var id = '#' + $(this).attr('id');
    var name = $('#ftype').attr("name");
    if(name == null) {
        name = "all";
    }
    var checkedArray = collectState();
    var val = $(this).attr('value');
    $.post('Filter.php', {checkedList: checkedArray, category: getDropdownValue(), ftype: name, pageno: val, display: 'true'}, function(result) {
        $('#display-area').html(result);
        $('#display-area').show();
        $('#loadingmessage').hide(); 
    });
    return false;
   });
});

//not(:checked)

function collectState() {
    var selected = [];
    var s1 = [];
    var s2 = [];
    var s3 = [];

    s1 = $("#sub-categories :checkbox:checked").map(function() {
        return this.value;
    }).get();
    
    s2 = $("#coupon-type :checkbox:checked").map(function() {
        return this.value;
    }).get();

    s3 = $("#store-type :checkbox:checked").map(function() {
        return this.value;
    }).get();
    selected[0]=s1;
    selected[1]=s2;
    selected[2]=s3;
    return selected;
}
function loadCoupons(selected) {
    var content = "";
    $.post('Filter.php', {checkedList: selected, category: getDropdownValue(), pageno: '1', display: 'true'}, function(result) {
        console.log(result);
        content  = result;
    });
    return content;
}
/*
Checkbox Listener Responsible for refreshing of Coupon Display Page 
*/
function refreshContent(val) {
    var selected = collectState();
    $('#display-area').hide();
    $('#loadingmessage').show();
    $.post('Filter.php', {checkedList: selected, category: getDropdownValue(), pageno: '1', filter: 'true'}, function(result) {
        var res = jQuery.parseJSON(result);
        $('#filters input').each(function() {
            var val = $(this).attr('value');
            var rr = "#"+val;
            var flag = 0;
            for (var key in res) {
                if(val == key) {
                    $(rr).html(res[key]);
                    flag =1;
                }
            }
            if(flag == 0) {
                $(rr).html("0");
            }
        });
    });
    
    $.post('Filter.php', {checkedList: selected, category: getDropdownValue(), pageno: '1', display: 'true'}, function(result) {
      $('#display-area').html(result);
    $('#display-area').show();
    $('#loadingmessage').hide(); 
    });

    return false;
    }
 