(function ($) {
  "use strict"; // Start of use strict

  // Toggle the side navigation
  $("#sidebarToggle, #sidebarToggleTop").on('click', function (e) {
    $("body").toggleClass("sidebar-toggled");
    $(".sidebar").toggleClass("toggled");
    if ($(".sidebar").hasClass("toggled")) {
      $('.sidebar .collapse').collapse('hide');
    };
  });

  // Close any open menu accordions when window is resized below 768px
  $(window).resize(function () {
    if ($(window).width() < 768) {
      $('.sidebar .collapse').collapse('hide');
    };

    // Toggle the side navigation when window is resized below 480px
    if ($(window).width() < 480 && !$(".sidebar").hasClass("toggled")) {
      $("body").addClass("sidebar-toggled");
      $(".sidebar").addClass("toggled");
      $('.sidebar .collapse').collapse('hide');
    };
  });

  // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
  $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function (e) {
    if ($(window).width() > 768) {
      var e0 = e.originalEvent,
        delta = e0.wheelDelta || -e0.detail;
      this.scrollTop += (delta < 0 ? 1 : -1) * 30;
      e.preventDefault();
    }
  });

  // Scroll to top button appear
  $(document).on('scroll', function () {
    var scrollDistance = $(this).scrollTop();
    if (scrollDistance > 100) {
      $('.scroll-to-top').fadeIn();
    } else {
      $('.scroll-to-top').fadeOut();
    }
  });


  // Smooth scrolling using jQuery easing
  $(document).on('click', 'a.scroll-to-top', function (e) {
    e.preventDefault();
    $('html, body').scrollTop(0); // Ir arriba sin animación
  });

})(jQuery); // End of use strict


//---------------------------------------------------------------------------------------------------------------
//Funcion para filtro por checkbox en la lista de productos
$(document).ready(function () {
  $('input[type="checkbox"]').change(function () {
    var categoriasSeleccionadas = [];
    $('input[type="checkbox"]:checked').each(function () {
      categoriasSeleccionadas.push($(this).val());
    });

    $('.row > div').hide();
    categoriasSeleccionadas.forEach(function (categoria) {
      $('.categoria-' + categoria).show();
    });
  });
});

//---------------------------------------------------------------------------------------------------------------
$(document).ready(function (){

    $('#formAgregarProducto').submit(function (e) {
      e.preventDefault(); // Detenemos el envío normal del formulario

      if(validarFormularioAgreP()){
          // Creamos un objeto FormData para enviar archivos
        var formData = new FormData(this);
        formData.append('accion', 'agregar'); // Agregamos el parámetro 'accion'

        // Realizamos una petición AJAX para enviar los datos al servidor
        $.ajax({
          type: 'POST',
          url: 'productos_admin.php', // Archivo PHP que procesará la inserción
          data: formData,
          dataType: 'json',
          processData: false, // Necesario para enviar archivos
          contentType: false, // Necesario para enviar archivos
          success: function (data) {
              if (data.status === "success") {
                  Swal.fire({
                      icon: "success",
                      title: "Éxito",
                      text: data.message,
                      confirmButtonColor: "#3085d6",
                      allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      icon: "error",
                      title: "Error",
                      text: data.message,
                      confirmButtonColor: "#d33",
                      allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
                  });
              }
          },
          error: function (xhr, status, error) {
              console.log("Respuesta completa del servidor:", xhr.responseText);
              Swal.fire({
                  icon: "error",
                  title: "Error",
                  text: "Hubo un problema al agregar el producto: " + xhr.responseText,
                  confirmButtonColor: "#d33",
                  allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
              });
          }
        });

      }else{
        console.log("Validación fallida, no se envían datos.");
      }
    }); 
      //Asignar elementos de validación en tiempo real

    document.getElementById("descripcionP").addEventListener("input", function () {
      validarCampoVacio("descripcionP", "validaDescripcionP");
    });

    document.getElementById("stockP").addEventListener("input", function () {
      validarEntero("stockP", "validaStockP");
    });

    document.getElementById("pventaP").addEventListener("input", function () {
      validarDecimal("pventaP", "validaPventaP");
    });

    //Función de verificación de los campos: Permite el envio de los datos siemore y cuando este sea verdadero
    function validarFormularioAgreP() {
      console.log("Ejecutando validación del formulario de agregar...");
      let valido = true; // Inicia como válido
  
      // Ejecuta cada validación y actualiza 'valido' correctamente
      valido = validarCampoVacio("descripcionP", "validaDescripcionP") && valido;
      valido = validarEntero("stockP", "validaStockP") && valido;
      valido = validarDecimal("pventaP", "validaPventaP") && valido;
      console.log("Resultado de validación:", valido);
      return valido;
    }

    function validarTexto(campoId, errorId) {
      const campo = document.getElementById(campoId);
      const error = document.getElementById(errorId);
      //Formato que permite establecer solo letras
      const regex = /^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/;

      if (!campo.value.trim()) {
          error.textContent = "Este campo no puede estar vacío.";
          return false;
      }
      if (!regex.test(campo.value)) {
          error.textContent = "Solo se permiten letras.";
          return false;
      }
      // El campo span resulta vacio si todo esta en orden
      error.textContent = "";
      return true;
    }


    function validarCampoVacio(campoId, errorId) {
      const campo = document.getElementById(campoId);
      const error = document.getElementById(errorId);

      if (!campo.value.trim()) {
          error.textContent = "Este campo no puede estar vacío.";
          return false;
      }

      error.textContent = "";
      return true;
    }


    function validarEntero(campoId, errorId) {
      const campo = document.getElementById(campoId);
      const error = document.getElementById(errorId);
      const regex = /^[1-9]\d*$/; // Solo permite números enteros positivos (mayores a 0)
  
      if (!campo.value.trim()) {
          error.textContent = "Este campo no puede estar vacío.";
          return false;
      }
      if (!regex.test(campo.value)) {
          error.textContent = "Solo se permiten números enteros positivos (mayores a 0).";
          return false;
      }
      // Si todo está en orden, se limpia el mensaje de error
      error.textContent = "";
      return true;
    }
  
    function validarDecimal(campoId, errorId) {
      const campo = document.getElementById(campoId);
      const error = document.getElementById(errorId);
      const regex = /^(?!0(\.00)?)\d+(\.\d{2})$/; // Permite solo números positivos con 2 decimales, mayor a 0
  
      if (!campo.value.trim()) {
          error.textContent = "Este campo no puede estar vacío.";
          return false;
      }
      if (!regex.test(campo.value)) {
          error.textContent = "Debe ingresar un número decimal positivo mayor a 0 de 2 decimales.";
          return false;
      }
      // Si todo está en orden, se limpia el mensaje de error
      error.textContent = "";
      return true;
    }

    function validarDecimal2(campoId, errorId) {
      const campo = document.getElementById(campoId);
      const error = document.getElementById(errorId);
      //const regex = /^(?:0|[1-9]\d*)(\.\d{2})?$/; // Acepta 0.00 y números positivos con hasta 2 decimales
      const regex = /^(?:0\.00|[1-9]\d*(\.\d{2})?)$/;
      if (!campo.value.trim()) {
          error.textContent = "Este campo no puede estar vacío.";
          return false;
      }
      if (!regex.test(campo.value)) {
          error.textContent = "Debe ingresar un número decimal positivo mayor igual a 0 de 2 decimales.";
          return false;
      }
      // Si todo está en orden, limpia el mensaje de error
      error.textContent = "";
      return true;
    }
  

  });

//---------------------------------------------------------------------------------------------------------------
/*Funcion para mostrar los datos de los productos en el modal para editarlos*/
$(document).ready(function () {
  // Manejar el evento de clic en el botón de editar producto
  $('.btn-editar-producto').click(function () {
    // Obtener los datos del producto del atributo data-
    var codigo = $(this).data('codigo');
    var id_categoria = $(this).data('id-categoria');
    var descripcion = $(this).data('descripcion');
    var stock = $(this).data('stock');
    var pventa = $(this).data('pventa');
    var desc_web = $(this).data('desc-web');

    
    // Llenar los campos del modal con los datos del producto
    $('#editarProductoModal #codigoEditarP').val(codigo).prop('readonly', true); // El campo de código es de solo lectura
    $('#editarProductoModal #id_categoriaEditarP').val(id_categoria);
    $('#editarProductoModal #descripcionEditarP').val(descripcion);
    $('#editarProductoModal #stockEditarP').val(stock);
    $('#editarProductoModal #pventaEditarP').val(pventa);

    $('#editarProductoModal #desc_webEditar').val(desc_web);

    // Abrir el modal de edición
    $('#editarProductoModal').modal('show');
  });
});

//---------------------------------------------------------------------------------------------------------------
$(document).ready(function () {
  $('#formEditarProducto').submit(function (e) {
    e.preventDefault(); // Evitar el envío normal del formulario

    if(validarFormularioEditP()){
      // Crear un objeto FormData para enviar archivos
      var formData = new FormData(this);
      formData.append('accion', 'actualizar'); // Añadir un parámetro extra

      $.ajax({
          type: 'POST',
          url: '../Admin/productos_admin.php',
          data: formData,
          dataType: 'json',
          contentType: false, // Importante para enviar archivos
          processData: false, // Importante para enviar archivos
          success: function (data) {
            if (data.status === "success") {
                Swal.fire({
                    icon: "success",
                    title: "Éxito",
                    text: data.message,
                    confirmButtonColor: "#3085d6",
                    allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.message,
                    confirmButtonColor: "#d33",
                    allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
                });
            }
          },
          error: function (xhr, status, error) {
              console.log("Respuesta completa del servidor:", xhr.responseText);
              Swal.fire({
                  icon: "error",
                  title: "Error",
                  text: "Hubo un problema al actualizar el producto: " + xhr.responseText,
                  confirmButtonColor: "#d33",
                  allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
              });
          }
        });
    }else{
      console.log("Validación fallida, no se envían datos.");
    }
    
  });

   // Asigna eventos de validación en tiempo real
   document.getElementById("descripcionEditarP").addEventListener("input", function () {
    validarCampoVacio("descripcionEditarP", "validaDescripcionEditarP");
  });

  document.getElementById("stockEditarP").addEventListener("input", function () {
    validarEntero("stockEditarP", "validaStockEditarP");
  });

  document.getElementById("pventaEditarP").addEventListener("input", function () {
    validarDecimal("pventaEditarP", "validaPventaEditarP");
  });

  //Función de verificación de los campos: Permite el envio de los datos siemore y cuando este sea verdadero
  function validarFormularioEditP() {
    console.log("Ejecutando validación del formulario de agregar...");
    let valido = true; // Inicia como válido

    // Ejecuta cada validación y actualiza 'valido' correctamente
    valido = validarCampoVacio("descripcionEditarP", "validaDescripcionEditarP") && valido;
    valido = validarEntero("stockEditarP", "validaStockEditarP") && valido;
    valido = validarDecimal("pventaEditarP", "validaPventaEditarP") && valido;
    console.log("Resultado de validación:", valido);
    return valido;
  }

  function validarTexto(campoId, errorId) {
    const campo = document.getElementById(campoId);
    const error = document.getElementById(errorId);
    //Formato que permite establecer solo letras
    const regex = /^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/;

    if (!campo.value.trim()) {
        error.textContent = "Este campo no puede estar vacío.";
        return false;
    }
    if (!regex.test(campo.value)) {
        error.textContent = "Solo se permiten letras.";
        return false;
    }
    // El campo span resulta vacio si todo esta en orden
    error.textContent = "";
    return true;
  }


  function validarCampoVacio(campoId, errorId) {
    const campo = document.getElementById(campoId);
    const error = document.getElementById(errorId);

    if (!campo.value.trim()) {
        error.textContent = "Este campo no puede estar vacío.";
        return false;
    }

    error.textContent = "";
    return true;
  }


  function validarEntero(campoId, errorId) {
    const campo = document.getElementById(campoId);
    const error = document.getElementById(errorId);
    const regex = /^[1-9]\d*$/; // Solo permite números enteros positivos (mayores a 0)

    if (!campo.value.trim()) {
        error.textContent = "Este campo no puede estar vacío.";
        return false;
    }
    if (!regex.test(campo.value)) {
        error.textContent = "Solo se permiten números enteros positivos (mayores a 0).";
        return false;
    }
    // Si todo está en orden, se limpia el mensaje de error
    error.textContent = "";
    return true;
  }

  function validarDecimal(campoId, errorId) {
    const campo = document.getElementById(campoId);
    const error = document.getElementById(errorId);
    const regex = /^(?!0(\.00)?)\d+(\.\d{2})$/; // Permite solo números positivos con 2 decimales, mayor a 0

    if (!campo.value.trim()) {
        error.textContent = "Este campo no puede estar vacío.";
        return false;
    }
    if (!regex.test(campo.value)) {
        error.textContent = "Debe ingresar un número decimal positivo mayor a 0 de 2 decimales.";
        return false;
    }
    // Si todo está en orden, se limpia el mensaje de error
    error.textContent = "";
    return true;
  }

  function validarDecimal2(campoId, errorId) {
    const campo = document.getElementById(campoId);
    const error = document.getElementById(errorId);
    //const regex = /^(?:0|[1-9]\d*)(\.\d{2})?$/; // Acepta 0.00 y números positivos con hasta 2 decimales
    const regex = /^(?:0\.00|[1-9]\d*(\.\d{2})?)$/;
    if (!campo.value.trim()) {
        error.textContent = "Este campo no puede estar vacío.";
        return false;
    }
    if (!regex.test(campo.value)) {
        error.textContent = "Debe ingresar un número decimal positivo mayor igual a 0 de 2 decimales.";
        return false;
    }
    // Si todo está en orden, limpia el mensaje de error
    error.textContent = "";
    return true;
  }


});


//---------------------------------------------------------------------------------------------------------------
$('.btn-eliminar-producto').click(function () {
  var codigoProducto = $(this).data('codigo');

  Swal.fire({
      title: "¿Estás seguro?",
      text: "Esta acción eliminará el producto permanentemente.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar",
      allowOutsideClick: false // Evita que se cierre al hacer clic fuera
  }).then((result) => {
      if (result.isConfirmed) {
          console.log("Enviando código a AJAX:", codigoProducto);

          $.ajax({
              type: 'POST',
              url: 'productos_admin.php',
              data: {
                  codigo: codigoProducto,
                  elimina: 'eliminar'
              },
              dataType: 'json',
              success: function (data) {
                  if (data.status === "success") {
                      Swal.fire({
                          icon: "success",
                          title: "Eliminado",
                          text: data.message,
                          confirmButtonColor: "#3085d6",
                          allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
                      }).then(() => {
                          location.reload();
                      });
                  } else {
                      Swal.fire({
                          icon: "error",
                          title: "Error",
                          text: data.message,
                          confirmButtonColor: "#d33",
                          allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
                      });
                  }
              },
              error: function (xhr, status, error) {
                  console.log("Respuesta completa del servidor:", xhr.responseText);
                  Swal.fire({
                      icon: "error",
                      title: "Error",
                      text: "Hubo un problema al eliminar el producto: " + xhr.responseText,
                      confirmButtonColor: "#d33",
                      allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
                  });
              }
          });
      } else {
          console.log("Eliminación cancelada por el usuario.");
      }
  });
});

/*
$('.btn-eliminar-producto').click(function () {
  var codigoProducto = $(this).data('codigo');

  if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
      console.log("Enviando código a AJAX:", codigoProducto);

      $.ajax({
          type: 'POST',
          url: 'productos_admin.php',
          data: {
              codigo: codigoProducto,
              elimina: 'eliminar'
          },
          dataType: 'json',
          success: function (data) {
              if (data.status === "success") {
                  Swal.fire({
                      icon: "success",
                      title: "Éxito",
                      text: data.message,
                      confirmButtonColor: "#3085d6",
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      icon: "error",
                      title: "Error",
                      text: data.message,
                      confirmButtonColor: "#d33",
                  });
              }
          },
          error: function (xhr, status, error) {
              console.log("Respuesta completa del servidor:", xhr.responseText);
              Swal.fire({
                  icon: "error",
                  title: "Error",
                  text: "Hubo un problema al eliminar el producto: " + xhr.responseText,
                  confirmButtonColor: "#d33",
              });
          }
      });
  }
});

*/
//Validadicones para AGREGAR PRODUCTOS Y EDITAR PRODUCTOS-----------------------------------------------------
// Obtener referencias a los campos del modal "Agregar Producto"
//const codigoInputAgregar = document.getElementById('codigo');
const idCategoriaInputAgregar = document.getElementById('id_categoria');
const fotoInputAgregar= document.getElementById('foto');
//const descripcionInputAgregar = document.getElementById('descripcion');
const stockInputAgregar = document.getElementById('stock');
const pventaInputAgregar = document.getElementById('pventa');

// Obtener referencias a los elementos donde se mostrarán los mensajes de error en el modal "Agregar Producto"
//const codigoErrorAgregar = document.getElementById('codigoError');
const idCategoriaErrorAgregar = document.getElementById('validaIdCategoria');
//const descripcionErrorAgregar = document.getElementById('validaDescripcion');
const stockErrorAgregar = document.getElementById('validaStock');
const pventaErrorAgregar = document.getElementById('validaPventa');

// Obtener referencias a los campos del modal "Editar Producto"
const codigoInputEditar = document.getElementById('codigoEditar');
const idCategoriaInputEditar = document.getElementById('id_categoriaEditar');
//const descripcionInputEditar = document.getElementById('descripcionEditar');
const stockInputEditar = document.getElementById('stockEditar');
const pventaInputEditar = document.getElementById('pventaEditar');

// Obtener referencias a los elementos donde se mostrarán los mensajes de error en el modal "Editar Producto"
const codigoErrorEditar = document.getElementById('validaCodigoEditar');
const idCategoriaErrorEditar = document.getElementById('validaIdCategoriaEditar');
//const descripcionErrorEditar = document.getElementById('validaDescripcionEditar');
const stockErrorEditar = document.getElementById('validaStockEditar');
const pventaErrorEditar = document.getElementById('validaPventaEditar');

// Función para validar campos que solo permiten letras
function validarCampoLetras(input, errorElement) {
  const valor = input.value.trim();
  const regex = /^[a-zA-Z\s]+$/;

  if (!regex.test(valor)) {
    errorElement.textContent = 'Solo se permiten letras y espacios';
    input.classList.add('is-invalid');
  } else {
    errorElement.textContent = '';
    input.classList.remove('is-invalid');
  }
}

// Función para validar campos que solo permiten números enteros
function validarCampoNumericoEntero(input, errorElement) {
  const valor = input.value.trim();
  const regex = /^\d+$/;

  if (!errorElement) {
    console.error("Error: el elemento de error es nulo.");
    return;
  }

  if (!regex.test(valor)) {
    errorElement.textContent = 'Solo se permiten valores numéricos enteros';
    input.classList.add('is-invalid');
  } else {
    errorElement.textContent = '';
    input.classList.remove('is-invalid');
  }
}


// Función para validar campos que permiten números decimales
function validarCampoNumericoDecimal(input, errorElement) {
  const valor = input.value.trim();
  const regex = /^\d+(\.\d+)?$/;

  if (!regex.test(valor)) {
    errorElement.textContent = 'Solo se permiten valores numéricos decimales';
    input.classList.add('is-invalid');
  } else {
    errorElement.textContent = '';
    input.classList.remove('is-invalid');
  }
}

// Función de manejo de eventos para validar campos en el modal "Agregar Producto"
function validarCamposAgregarProducto() {
  //validarCampoNumericoEntero(codigoInputAgregar, codigoErrorAgregar);
  validarCampoLetras(descripcionInputAgregar, descripcionErrorAgregar);
  validarCampoNumericoEntero(idCategoriaInputAgregar, idCategoriaErrorAgregar);
  validarCampoNumericoEntero(stockInputAgregar, stockErrorAgregar);
  validarCampoNumericoDecimal(pventaInputAgregar, pventaErrorAgregar);
}

// Función de manejo de eventos para validar campos en el modal "Editar Producto"
function validarCamposEditarProducto() {
  //validarCampoLetras(descripcionInputEditar, descripcionErrorEditar);
  validarCampoNumericoEntero(idCategoriaInputEditar, idCategoriaErrorEditar);
  validarCampoNumericoEntero(stockInputEditar, stockErrorEditar);
  validarCampoNumericoDecimal(pventaInputEditar, pventaErrorEditar);
}

// Agregar eventos blur a los campos en el modal "Agregar Producto"
codigoInputAgregar.addEventListener('blur', validarCamposAgregarProducto);
//descripcionInputAgregar.addEventListener('blur', validarCamposAgregarProducto);
idCategoriaInputAgregar.addEventListener('blur', validarCamposAgregarProducto);
stockInputAgregar.addEventListener('blur', validarCamposAgregarProducto);
pventaInputAgregar.addEventListener('blur', validarCamposAgregarProducto);

// Agregar eventos blur a los campos en el modal "Editar Producto"
//descripcionInputEditar.addEventListener('blur', validarCamposEditarProducto);
idCategoriaInputEditar.addEventListener('blur', validarCamposEditarProducto);
stockInputEditar.addEventListener('blur', validarCamposEditarProducto);
pventaInputEditar.addEventListener('blur', validarCamposEditarProducto);


//FUNCION PARA NO PASAR EL FORMULARIO DE CAMBIO DE CONTRA SI SE ENCUENTRAN ERRORES------------------------
function guardarCambios() {
  // Verificar si hay algún span de alerta activo
  var spansDeAlerta = document.querySelectorAll('.text-danger');
  for (var i = 0; i < spansDeAlerta.length; i++) {
    if (spansDeAlerta[i].innerText !== "") {
      // Mostrar mensaje de error
      alert("Hay errores en el formulario. Por favor, verifica y vuelve a intentarlo.");
      // Evitar que se envíe el formulario
      return false;
    }
  }

  // Si no hay spans de alerta activos, permitir que se ejecute la acción del botón "Cambiar Contraseña"
  return true;
}

//Bloque de código, para saber si es còdigo ya existe cuando se agregar un producto, gestiona el contenido del codigo errado
document.getElementById('codigo').addEventListener('blur', function() {
  var codigo = this.value;
  var codigoError = document.getElementById('codigoError');
  
  // Realizar solicitud AJAX
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'productos_admin.php', true);
  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhr.onload = function() {
      if (xhr.status == 200) {
          var response = JSON.parse(xhr.responseText);
          if (response.exists) {
              codigoError.textContent = '¡El código ya existe!';
          } else {
              codigoError.textContent = '';
          }
      }
  };
  xhr.send('codigo=' + codigo);
});


