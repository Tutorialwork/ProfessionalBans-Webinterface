function openModal(){
    Swal.fire({
        title: 'Enter your search query',
        input: 'text',
        inputAttributes: {
            autocapitalize: 'off'
        },
        showCancelButton: true,
        confirmButtonText: 'Search',
        showLoaderOnConfirm: true,
        preConfirm: (login) => {
            return fetch(`/search/${login}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(response.statusText)
                    }
                    return response.json()
                })
                .catch(error => {
                    Swal.showValidationMessage(
                        `No results found`
                    )
                })
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.value) {
            let searchResults = "";
            console.log(JSON.stringify(result.value))
            console.log(result.value["results"].forEach((each) => {
                searchResults += "<img src='https://minotar.net/helm/"+each["Name"]+"/100.png' class='img-circle elevation-2'> <a href='/user/"+each["Name"]+"'>&nbsp;&nbsp;&nbsp;"+each["Name"]+"</a> <br><br>"
            }))
            Swal.fire({
                title: '<strong>Searchresults</strong>',
                html: searchResults,
                showCloseButton: true,
                focusConfirm: false,
            })
        }
    })
}