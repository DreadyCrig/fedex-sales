$(document).ready(function(){
  
  // Submit Registrant entry via ajax
  $('#registerForm').ajaxForm({
    dataType: 'json',
    success: function(data) {
      if (data.success) {
        resetForm($('#registerForm'));
        swal({
          title: "Success!",
          text: "Great job on registering another account!",
          confirmButtonText: "Keep going",
          type: "success"
        });
      } else {
        
        swal({
          title: "Error!",
          text: 'Failed with the following errors: '+data.errors.join(', '),
          confirmButtonText: "Try again",
          type: "error"
        });
        resetForm($('#registerForm'));
      }
    }
  });

  function resetForm($form) {
    $form.find('input:text, input:password, input[type="email"], input[type="tel"], input:file, select, textarea').val('');
    $form.find('input:radio, input:checkbox')
    .removeAttr('checked').removeAttr('selected');
  }



});