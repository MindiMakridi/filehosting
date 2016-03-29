function uploadComment(event){
    event.preventDefault();
    var form = event.target;
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "/files/"+id);
    var fd = new FormData(event.target);
    xhr.onreadystatechange = function(event){
        if(event.target.readyState==4){
            if(event.target.status==200){
                 getNewComments();
                 
            }
            else{
                form.submit();
            }
        }
    }
    xhr.send(fd);
    returnReplyButton(form);
    deleteFormWrapper(form.parentNode);
    
    
   

}


function getNewComments(){
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "/newComments/"+id);
    xhr.onreadystatechange = function(event){
        if(event.target.readyState==4){
            if(event.target.status==200){
                renderNewComments(JSON.parse(xhr.responseText));
            }
        }
    }
    xhr.send();
}


function renderNewComments(comments){
    var commentsSection = document.getElementsByClassName("comments-wrapper")[0];
    for(var i = 0; i<comments.length; i++){
        var comment = document.createElement('div');
        comment.style.marginLeft = comments[i].depth*25+"px";
        comment.className = "commentary";
        comment.setAttribute("id", comments[i].path);
        comment.innerHTML = document.getElementById('commentary-template').innerHTML;
        
        comment.innerHTML = comment.innerHTML.replace("{name}", comments[i].name);
        comment.innerHTML = comment.innerHTML.replace("{date}", comments[i].date);
        comment.innerHTML = comment.innerHTML.replace("{text}", comments[i].text);
        comment.innerHTML = comment.innerHTML.replace("{path}", comments[i].path);
       
        var path = comments[i].path.split(".");
        if(path.length>1){
            var lastDigits = parseInt(path[path.length-1]);
            var id;
            if(lastDigits>1){
                id = lastDigits-1;
                id = leftPad(id, "0", 3);
                path[path.length-1] = id;
            }
            else{
                path = path.slice(0, path.length-1);
            }
            
            id = path.join(".");
            var nextSiblingElement = getNonDescendantSibling(document.getElementById(id));
            commentsSection.insertBefore(comment, nextSiblingElement);
        }
        else{
        commentsSection.insertBefore(comment, commentsSection.lastElementChild);
        }
    }
}


function leftPad(text, filler, quantity){
    text = text.toString();
    while(text.length<quantity){
        text = filler+text;
    }
    return text;
}

function returnReplyButton(form){
    var anchor = document.createElement('a');
    anchor.className = "add-comment";
    if(form.path){
        var elem = document.getElementById(form.path.value);
        var reply = document.createElement('div');
        reply.className = "reply";
        anchor.setAttribute("data-path", form.path.value);
        anchor.innerHTML = "ответить";
        reply.appendChild(anchor);
        elem.appendChild(reply);
    }
    else{
        anchor.innerHTML = "Добавить комментарий";
        document.querySelector('.comments-wrapper').appendChild(anchor);
    }
    
}

function getNonDescendantSibling(comment){
    var path = comment.getAttribute("id").split(".");
    var sibling = comment.nextElementSibling;
    var siblingId = sibling.getAttribute("id");
    
    while(sibling.classList.contains("commentary") && siblingId.split(".").length>path.length){
        sibling = sibling.nextElementSibling;
    }
    
     return sibling;
}

function deleteFormWrapper(wrapper){
    wrapper.parentNode.removeChild(wrapper);
}
