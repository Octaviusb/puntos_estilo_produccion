const body = document.querySelector("body"),
    modeToggle = body.querySelector(".mode-toggle"),
    sidebar = body.querySelector("nav"),
    sidebarToggle = body.querySelector(".sidebar-toggle");

let getMode = localStorage.getItem("mode");
if (getMode && getMode === "dark") {
    body.classList.toggle("dark");
}
let getStatus = localStorage.getItem("status");
if (getStatus && getStatus === "close") {
    sidebar.classList.toggle("close");
}
modeToggle.addEventListener("click", () => {
    body.classList.toggle("dark");
    if (body.classList.contains("dark")) {
        localStorage.setItem("mode", "dark");
    } else {
        localStorage.setItem("mode", "light");
    }
});
sidebarToggle.addEventListener("click", () => {
    sidebar.classList.toggle("close");
    if (sidebar.classList.contains("close")) {
        localStorage.setItem("status", "close");
    } else {
        localStorage.setItem("status", "open");
    }
})
const FetchAsynAwait = async (pag, dataJson, r = 'json') => {
    const opciones = {
        method: 'POST',
        cache: 'no-cache',
        body: JSON.stringify(dataJson),
        headers: {
            'Content-Type': 'application/json'
        }
    };
    try {
        const fetchResp = await fetch(pag, opciones);
        const retorna = await r === 'json' ? fetchResp.json() : fetchResp.text();
        return retorna;
    } catch (e) {
        return e;
    }
}
const recorre = (clase, array) => {
    let a = document.querySelector('.' + clase);
    a.innerHTML = '';
    let o = '<span class="data-title">' + clase + '</span>';
    for (let i = 0; i < array.length; i++) {
        o += '<span class="data-list">' + array[i] + '</span>';
    }
    a.innerHTML = o;
}
const ContentLoad = (clase) => {
    FetchAsynAwait('query/query.php', { 'user': true }, 'json').then((ret) => {
        if (ret.length > 0 || ret.length == undefined) {
            recorre('User', ret.names);
            recorre('Email', ret.mail);
            recorre('Verify', ret.verify);
            recorre('Status', ret.status);
            let a = document.querySelector('.Out');
            a.innerHTML = '';
            let o = '<span class="data-title">Out</span>';
            for (let i = 0; i < ret.out.length; i++) {
                o += '<span class="data-list"><label class="suiche"><input type="checkbox" id="' + ret.out[i].id + '" ' + ret.out[i].check + '><span class="slider round"></span></label></span>';
            }
            a.innerHTML = o;
            const check = document.querySelectorAll("."+clase);
            check.forEach(function (elem) {
                elem.addEventListener("click", function (e) {
                    let id = elem.parentNode.childNodes[0].id;
                    let status = elem.parentNode.childNodes[0].checked ? 'activo' : 'fuera';
                    FetchAsynAwait('query/query.php', { 'id': id, 'status': status }, 'text').then((ret) => {
                        tshow('info', '¡EDITADO!', ret);
                        ContentLoad('slider');
                    });
                });
            });
        }
    });    
}
document.addEventListener('DOMContentLoaded', function () { 
    ContentLoad('slider');
});