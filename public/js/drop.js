$(document).ready(function()
{
	var dropZone = $('#dropZone'), maxFileSize = $('#maxFileSize').val();

	dropZone[0].ondragover = function() {
		console.log(maxFileSize);
		dropZone.addClass('hover');
		return false;
	};

	dropZone[0].ondragleave = function(){
		dropZone.removeClass('hover');
		return false;
	};

	dropZone[0].ondrop = function(event){
		event.preventDefault();
		dropZone.removeClass('hover');
		dropZone.addClass('drop');

		var file = event.dataTransfer.files[0];

		if(file.size > maxFileSize){
			dropZone.text ="Файл слишком большой!";
			dropZone.addClass('error');
			return false;
		}

		var xhr = new XMLHttpRequest();
		xhr.upload.addEventListener('progress', uploadProgress, false);
				xhr.open('POST', '/');
		xhr.setRequestHeader('X-FILE-NAME', file.name);
		xhr.onreadystatechange = stateChange;
		var fd = new FormData;
		fd.append("userfile", file);
		xhr.send(fd);

		function uploadProgress(event) {
    	var percent = parseInt(event.loaded / event.total * 100);
    	dropZone.text('Загрузка: ' + percent + '%');
		}


		function stateChange(event) {
    	if (event.target.readyState == 4) {
        	if (event.target.status == 200) {
            dropZone.text('Загрузка успешно завершена!');
            window.location.href = xhr.responseURL;
        } else {
            dropZone.text('Произошла ошибка!');
            dropZone.addClass('error');
        }
    }
}

	
	};
})