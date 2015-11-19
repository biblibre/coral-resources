$(document).ready(function(){
  $('#catalogingForm').submit(submitCataloging);
});

function submitCataloging(){
  $('#submitCatalogingChanges').attr("disabled", "disabled");
  var form = $('#catalogingForm');
  $.post(
    form.attr('action'),
    form.serialize(),
    function(html) {
			if (html){
				$("#span_errors").html(html);
				$("#submitCatalogingChanges").removeAttr("disabled");
			}else{
				kill();
				window.parent.tb_remove();
				window.parent.updateCataloging();
			}
		}
  );
  
  
  return false;
}

//kill all binds done by jquery live
function kill(){

	$('.changeDefault').die('blur');
	$('.changeDefault').die('focus');
	$('.changeInput').die('blur');
	$('.changeInput').die('focus');
	$('.select').die('blur');
	$('.select').die('focus');

}

//the following are all to change the look of the inputs when they're clicked
$(document).on('focus', '.changeDefaultWhite', function(e) {
	if (this.value == this.defaultValue){
		this.value = '';
	}
});

 $(document).on('blur', '.changeDefaultWhite', function() {
	if(this.value == ''){
		this.value = this.defaultValue;
	}		
 });


  	$('.changeInput').addClass("idleField");
  	
$(document).on('focus', '.changeInput', function() {


	$(this).removeClass("idleField").addClass("focusField");

	if(this.value != this.defaultValue){
		this.select();
	}

 });


 $(document).on('blur', '.changeInput', function() {
	$(this).removeClass("focusField").addClass("idleField");
 });


$(document).on('focus', '.changeAutocomplete', function() {
	if (this.value == this.defaultValue){
		this.value = '';
	}

 });


 $(document).on('blur', '.changeAutocomplete', function() {
	if(this.value == ''){
		this.value = this.defaultValue;
	}	
 });
 



$('select').addClass("idleField");
$(document).on('focus', 'select', function() {
	$(this).removeClass("idleField").addClass("focusField");

});

$(document).on('blur', 'select', function() {
	$(this).removeClass("focusField").addClass("idleField");
});



$('textarea').addClass("idleField");
$('textarea').focus(function() {
	$(this).removeClass("idleField").addClass("focusField");
});
    
$('textarea').blur(function() {
	$(this).removeClass("focusField").addClass("idleField");
});
