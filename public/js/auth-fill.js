window.addEventListener('load', function() {
    if (document.getElementById("author-input") && document.getElementById("author-input").value === "")
        document.getElementById("author-input").value = window.localStorage.getItem('author')
    if (document.getElementById("key-input") && document.getElementById("key-input").value === "")
        document.getElementById("key-input").value = window.localStorage.getItem('key')
})

