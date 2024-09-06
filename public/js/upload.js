window.addEventListener('load', function() {
    document.getElementById("q1y").onclick = function(event){
        event.preventDefault();
        document.getElementById("q1").style.display = "none";
        document.getElementById("qy2").style.display = "block";
    }
    document.getElementById("q1n").onclick = function(event){
        event.preventDefault();
        document.getElementById("q1").style.display = "none";
        document.getElementById("qn2").style.display = "block";
    }

    document.getElementById("qy2n").onclick = function(event){
        event.preventDefault();
        document.getElementById("qy2").style.display = "none";
        document.getElementById("qn2").style.display = "block";
    }
    document.getElementById("qy2y").onclick = function(event){
        event.preventDefault();
        document.getElementById("guide").style.display = "none";
        document.getElementById("type-input").value = "remote";
        document.getElementById("remote-input-fieldset").style.display = "block";
        document.getElementById("remote-footer").style.display = "block";
    }

    document.getElementById("qn2y").onclick = function(event){
        event.preventDefault();
        document.getElementById("guide").style.display = "none";
        document.getElementById("type-input").value = "link";
        document.getElementById("code-insert-tutorial").style.display = "block";
        document.getElementById("link-input-fieldset").style.display = "block";
        document.getElementById("local-footer").style.display = "block";
    }
    document.getElementById("qn2n").onclick = function(event){
        event.preventDefault();
        document.getElementById("guide").style.display = "none";
        document.getElementById("type-input").value = "file";
        document.getElementById("code-insert-tutorial").style.display = "block";
        document.getElementById("file-input-fieldset").style.display = "block";
        document.getElementById("local-footer").style.display = "block";
    }

    document.getElementById("legacy-guide-button").onclick = function(event) {
        event.preventDefault();
        document.getElementById("qn2n").click();
    }
});