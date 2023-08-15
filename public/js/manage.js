window.addEventListener('load', function() {
    let buttons = document.getElementsByClassName("clickable-icon")
    for (let i = 0; i < buttons.length; i++) {
        buttons[i].addEventListener('click', function (event) {
            event.preventDefault();
            document.getElementById("deck-modification-link-data-sender").action = event.target.getAttribute("data-href");
            document.getElementById("deck-modification-link-data-sender").submit();
        })
    }

    document.getElementById("reset-button").addEventListener('click', function(event){
        document.getElementById('manage-link').click();
    })
})

window.onkeydown = function(event){
    if(event.keyCode === 13) {
        event.preventDefault()
        document.getElementById("manage-form").submit()
    }
}

