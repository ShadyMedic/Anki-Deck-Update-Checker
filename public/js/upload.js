window.addEventListener('load', function() {
    document.getElementById("q1y").onclick = function(event){
        event.preventDefault()
        document.getElementById("q1").style.display = "none";
        document.getElementById("qy2").style.display = "block";
    }
    document.getElementById("q1n").onclick = function(event){
        event.preventDefault()
        document.getElementById("q1").style.display = "none";
        document.getElementById("qn2").style.display = "block";
    }

    document.getElementById("qy2y").onclick = function(event){
        event.preventDefault()
        document.getElementById("qy2").style.display = "none";
        //TODO
    }
    document.getElementById("qy2n").onclick = function(event){
        event.preventDefault()
        document.getElementById("qy2").style.display = "none";
        //TODO
    }

    document.getElementById("qn2y").onclick = function(event){
        event.preventDefault()
        document.getElementById("qn2").style.display = "none";
        //TODO
    }
    document.getElementById("qn2n").onclick = function(event){
        event.preventDefault()
        document.getElementById("qn2").style.display = "none";
        //TODO
    }
});