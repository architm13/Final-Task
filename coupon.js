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
function collectUnchecked() {
    var unselected = [];
    var s4 = [];
    var s5 = [];
    var s6 = [];

    s4 = $("#sub-categories :checkbox:not(:checked)").map(function() {
        return this.value;
    }).get();

    s5 = $("#coupon-type :checkbox:not(:checked)").map(function() {
        return this.value;
    }).get();

    s6 = $("#store-type :checkbox:not(:checked)").map(function() {
        return this.value;
    }).get();

    unselected[0] = s4;
    unselected[1] = s5;
    unselected[2] = s6;
    return unselected;
}
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

/*
Checkbox Listener Responsible for refreshing of Coupon Display Page 
*/
function refreshContent(val) {
    console.log("came as expected: ");
    var selected = collectState();
    var unselected = collectUnchecked();

    $('#display-area').hide();
    $('#loadingmessage').show();
    $.post('Filter.php', {checkedList: selected, category: getDropdownValue(), pageno: '1', filter: 'true'}, function(result) {
        var res = jQuery.parseJSON(result);
        console.log("all the checked list items");
        console.log(res);
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
        });
    });
    $.post('Filter.php', {checkedList: selected, uncheckedList: unselected, category: getDropdownValue(), pageno: '1', unfilter: 'true'}, function(result) {
      //  console.log("Displaying the unchecked boxes values");
        var res = jQuery.parseJSON(result);
        console.log(result);
        $('#filters input').each(function() {
            var val = $(this).attr('value');
            var rr = "#"+val;
            var flag = 0;
            for (var key in res) {
                if(val == key) {
                    console.log("illuminati: " + val);
                    $(rr).html(res[key]);
                }
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
 