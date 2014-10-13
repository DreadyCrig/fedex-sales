$(document).ready(function(){

  // Validation
  $("#registerForm").bootstrapValidator({
    live: 'enabled',
    submitButtons: 'button[type="submit"]',
    trigger: "blur"
  });
  
  // Submit Registrant entry via ajax
  $('#registerForm').ajaxForm({
    dataType: 'json',
    success: function(data) {
      if (data.success) {
        resetForm($('#registerForm'));
        swal({
          title: "Felicidades",
          text: "¡Bien hecho, registraste una nueva cuenta!",
          confirmButtonText: "¡Sigue participando!",
          type: "success"
        });
      } else {
        
        swal({
          title: "Error",
          text: "La cuenta no pudo ser registrada con éxito.",
          confirmButtonText: "Trata de nuevo",
          type: "error"
        });
        resetForm($('#registerForm'));
      }
    }
  });

  function resetForm($form) {
    $form.find('input:text, input:password, input[type="email"], input[type="tel"], input:file, input.title-field, select, textarea').val('');
    $form.find('input:radio, input:checkbox')
    .removeAttr('checked').removeAttr('selected');
  }


});