window.addEventListener('load', function() {
    document.getElementById("home-link").addEventListener('click', function (event) {
        window.location = '/';
    })

    document.getElementById("browse-link").addEventListener('click', function (event) {
        window.location = '/browse';
    })

    document.getElementById("manage-link").addEventListener('click', function (event) {
        event.preventDefault()
        document.getElementById("manage-link-key-input").value = window.localStorage.getItem('key');
        document.getElementById("manage-link-data-sender").submit();
    })

    document.getElementById("create-link").addEventListener('click', function (event) {
        window.location = '/new-deck';
    })
})

