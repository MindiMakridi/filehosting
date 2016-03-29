$(document).ready(function()
{
        if(window.FormData===undefined){
            return false;
        }
	var dropZone = $('#dropZone'), maxFileSize = $('#maxFileSize').val();
        dropZone.on({dragover:function() {
		dropZone.addClass('hover');
		return false;
	}, dragleave:function(){
		dropZone.removeClass('hover');
		return false;
	}, drop:function(event){
		event.preventDefault();
		dropZone.removeClass('hover');
		dropZone.addClass('drop');
		var file = event.originalEvent.dataTransfer.files[0];
                if(event.originalEvent.dataTransfer.files.length>1){
                    dropZone.text("одновременная загрузка нескольких файлов не поддерживаеися");
                    dropZone.addClass('error');
                    dropZone.removeClass('drop');
                    return false;
                }
		if(file.size > maxFileSize){
			dropZone.text("Файл слишком большой");
			dropZone.addClass('error');
                        dropZone.removeClass('drop');
			return false;
		}

		var xhr = new XMLHttpRequest();
                if("progress" in  xhr.upload){
                    xhr.upload.addEventListener('progress', uploadProgress, false);
                }
		xhr.open('POST', '/');
		xhr.onreadystatechange = stateChange;
		var fd = new FormData;
		fd.append("userfile", file, file.name);
		xhr.send(fd);

		function uploadProgress(event) {
                    var percent = parseInt(event.loaded / event.total * 100);
                    dropZone.text('Загрузка: ' + percent + '%');
		}


		function stateChange(event) {
                    if (event.target.readyState == 4) {
                        if (event.target.status == 200) {
                            success();
                        window.location.href = xhr.responseURL;
                        } else {
                            error();
                          }
                    }
                }

	
	}});
	function error(){
            dropZone.text('Произошла ошибка!');
            dropZone.addClass('error');
            dropZone.removeClass('drop');
        }
        
        function success(){
            dropZone.text('Загрузка успешно завершена!');
        }
})