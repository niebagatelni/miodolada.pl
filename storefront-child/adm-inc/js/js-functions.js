function isFileExist(url)
{
    var xhr = new XMLHttpRequest();
    xhr.open('HEAD', url, false);
    xhr.send();

    if (xhr.status == "200") {
        return true;
    } else {
        console.log("Błąd w dostępie do pliku audio");
        return false;
    }
}
