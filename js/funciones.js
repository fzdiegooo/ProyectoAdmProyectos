function preview(e){

    const url=e.target.files[0];
    const urlTmp=URL.createObjectURL(url);
    document.getElementById("img-preview").src=urlTmp;
    document.getElementById("icon-image").classList.add("d-none");
    document.getElementById("icon-cerrar").innerHTML=`<button class="btn btn-danger" onclick="deleteImg(event)"><i class="fas fa-times"></i></button>
    ${url['name']}`;


}
function deleteImg(e){
    document.getElementById("icon-cerrar").innerHTML='';
    document.getElementById("icon-image").classList.remove("d-none");
    document.getElementById("img-preview").src='';



}
document.getElementById('fotoEditar').addEventListener('change', function(event) {
    let reader = new FileReader();
    reader.onload = function(){
        let output = document.getElementById('previewImagenEditar');
        output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
});
