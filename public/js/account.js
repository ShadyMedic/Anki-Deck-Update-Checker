window.addEventListener('load', function() {
    document.getElementById("generate-key-button").addEventListener('click', function (event) {
        event.preventDefault()
        let characters = [
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
        ]
        let pass = ''
        for (let i = 0; i < 31; i++) {
            pass += characters[Math.floor(Math.random() * characters.length)]
        }

        document.getElementById("key-input").value = pass
    })

    document.getElementById("manage-link-account").addEventListener('click', function (event) {
        event.preventDefault()
        document.getElementById("manage-link").click();
    })

    document.getElementById("submit-button").addEventListener('click', function (event) {
        event.preventDefault()

        let author = document.getElementById("author-input").value;
        window.localStorage.setItem('author', author);

        let key = document.getElementById("key-input").value;
        window.localStorage.setItem('key', key);

        document.getElementById("result-box").innerText = "Credentials saved.";
        setTimeout(function() {document.getElementById("result-box").innerText = "";}, 2000);
    })

    document.getElementById("key-input").value = window.localStorage.getItem('key')
    document.getElementById("author-input").value = window.localStorage.getItem('author')
})

