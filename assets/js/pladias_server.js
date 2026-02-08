export function isLocalhost() {
    return ('localhost' === window.location.href.toString().split("/")[2].split(":")[0])
}

export function redirect(url) {
    setTimeout(function () {
        window.location.href = url;
    }, 0);
}

export function getHost() {
    return window.location.href.toString().split("/")[2];
}

export function getProtocol() {
    return window.location.href.toString().split("/")[0];
}

export function getGeoBasePath(){
    return getProtocol() + '//' + getHost();
}

export function getAppBasePath() {
    return getGeoBasePath();

}

