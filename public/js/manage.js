document.getElementById("load-key-button").addEventListener('click', function (event) {
    event.preventDefault()

    document.getElementById("key-input").value = window.localStorage.getItem('key')
})

function formSubmitted(event) {
    event.preventDefault()

    let key = document.getElementById("key-input").value
    window.localStorage.setItem('key', key)
    console.log("saving " + key)

    document.getElementById("manage-form").submit()
}

document.getElementById("manage-form").addEventListener('submit', formSubmitted)

window.onkeydown = function(event){
    if(event.keyCode === 13) {
        event.preventDefault()
        formSubmitted(event)
    }
}

