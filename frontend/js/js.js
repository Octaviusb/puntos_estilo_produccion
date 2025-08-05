
const forms = document.querySelector(".forms"),
    psh = document.querySelectorAll(".eye-icon"),
    inpt = document.querySelectorAll(".inputOTP"),
    frm = document.querySelectorAll("form"),
    lin = document.querySelectorAll(".link"),
    ppass = document.querySelector("#preg_pass1"),
    pgass = document.querySelector("#preg_pass2"),
    chk = document.querySelector("#chck"),
    umail = document.querySelector('[name="user_mail"]'),
    pas = document.querySelector('[name="pass"]'),
    pmail = document.querySelector('[name="preg_mail"]'),
    rrp = document.querySelector('[name="respuesta"]');

let ps = 0;
psh.forEach((e) => {
    e.addEventListener("click", () => {
        e.parentElement.parentElement.querySelectorAll(".password").forEach((t) => {
            if ("password" === t.type) return (t.type = "text"), void e.classList.replace("bx-hide", "bx-show");
            (t.type = "password"), e.classList.replace("bx-show", "bx-hide");
        });
    });
}),
    inpt.forEach((e, t) => {
        e.addEventListener("keyup", (a) => {
            const s = e,
                n = e.nextElementSibling,
                r = e.previousElementSibling;
            s.value.length > 1
                ? (s.value = "")
                : (2 == t && "SPAN" == n.tagName && (n.nextElementSibling.removeAttribute("disabled"), n.nextElementSibling.focus()),
                  n && n.hasAttribute("disabled") && "" !== s.value && (n.removeAttribute("disabled"), n.focus()),
                  "Backspace" === a.key &&
                      inpt.forEach((e, a) => {
                          t <= a && r && (3 == a && "SPAN" == r.tagName && r.previousElementSibling.focus(), e.setAttribute("disabled", !0), (s.value = ""), r.focus());
                      }),
                  inpt[5].disabled || "" === inpt[5].value ? (btnlogin.disabled = !0) : (btnlogin.disabled = !1));
        });
    }),
    lin.forEach((e) => {
        e.addEventListener("click", (e) => {
            switch ((e.preventDefault(), e.target.className)) {
                case "link signup-link":
                    forms.classList.add("show-signup");
                    break;
                case "link forgot-pass":
                    forms.classList.add("show-forgot");
                    break;
                case "link login-link":
                    forms.classList.remove("show-signup"), forms.classList.remove("show-forgot");
            }
            frm.forEach((e) => {
                e.reset();
            }),
                ppass.classList.add("d-none"),
                pgass.classList.add("d-none"),
                chk.classList.add("d-none"),
                (InPreguta.innerHTML = "");
        });
    }),
    umail.addEventListener("keyup", function (e) {
        "admin" == e.target.value.toLowerCase() ? (document.querySelector(".otp").classList.add("d-none"), (btnlogin.disabled = !1)) : (document.querySelector(".otp").classList.remove("d-none"), (btnlogin.disabled = !0));
    }),
    umail.addEventListener("change", function (e) {
        e.target.value.length > 0 ? ps++ : ps--, otp_ready(2 == ps);
    }),
    pas.addEventListener("change", function (e) {
        e.target.value.length > 0 ? ps++ : ps--, otp_ready(2 == ps);
    });
const otp_ready = (e) => {
    e ? ((inpt[0].disabled = !1), inpt[0].focus()) : (inpt[0].disabled = !0);
};
pmail.addEventListener("blur", function (e) {
    e.target.value.length > 0 &&
        FetchAsynAwait("query/query.php", { preg_mail: e.target.value }, "text").then((e) => {
            isNumber(CLearChar(e.trim())) ? (tshow("danger", "¡GRAVE!", "<br>El email no existe en nustra bbdd", 4500), (btnforgot.disabled = !0)) : ((btnforgot.disabled = !1), (InPreguta.innerHTML = e));
        });
}),
    rrp.addEventListener("change", function (e) {
        laRes(e);
    }),
    rrp.addEventListener("blur", function (e) {
        laRes(e);
    });
const laRes = (e) => {
    e.target.value.length > 0 &&
        FetchAsynAwait("query/query.php", { respuesta: e.target.value, email: pmail.value }, "text").then((e) => {
            "ok" == CLearChar(e.trim()) ? (ppass.classList.remove("d-none"), pgass.classList.remove("d-none"), chk.classList.remove("d-none"), (btnforgot.disabled = !1)) : ((btnforgot.disabled = !0), tshow("danger", "¡GRAVE!", e, 4500));
        });
};
btnforgot.addEventListener("click", (e) => {
    e.preventDefault(),
        spin(btnforgot),
        FetchAsynAwait("query/recupera_pass.php", { JsonPars: JsonPars(frmforgot) }, "text").then((e) => {
            let t = CLearChar(e.trim());
            spin(btnforgot, !1, "Solicita"),
                "ok" == t
                    ? (tshow("success", "¡EXITO!", "<br>Su nueva password ha sido guardada", 3500),
                      setTimeout(() => {
                          location.reload();
                      }, 3500))
                    : "correo" == t
                    ? (tshow("success", "¡EXITO!", "<br>Su nueva password ha sido guardada<br>El correo se envio con la nueva imagen", 4500),
                      setTimeout(() => {
                          location.reload();
                      }, 4500))
                    : (spin(btnforgot, !1, "Solicita"), tshow("danger", "¡ERROR!", e, 4500));
        });
}),
    btnlogin.addEventListener("click", (e) => {
        e.preventDefault(), spin(btnlogin);
        let t = pas;
        if (0 == umail.value.length) spin(btnlogin, !1, "Ingresa"), tshow("warning", "¡VACIO!", "<br>Ingrese su usuario o email", 4500);
        else if (0 == t.value.length) spin(btnlogin, !1, "Ingresa"), tshow("warning", "¡VACIO!", "<br>Ingrese su password", 4500);
        else {
            let e = "";
            inpt.forEach(function (t) {
                e += t.value;
            }),
                FetchAsynAwait("query/valida.php", { JsonPars: JsonPars(frmlogin), otp: e }, "text").then((e) => {
                    isNumber(CLearChar(e.trim()))
                        ? location.reload()
                        : (spin(btnlogin, !1, "Ingresa"),
                          tshow("danger", "¡ERROR!", e, 4500),
                          (btnlogin.disabled = !0),
                          inpt.forEach(function (e) {
                              (e.value = ""), (e.disabled = !0);
                          }),
                          (inpt[0].disabled = !1),
                          inpt[0].focus());
                });
        }
    }),
    btnregistro.addEventListener("click", (e) => {
        e.preventDefault(),
            spin(btnregistro),
            FetchAsynAwait("query/registro.php", { json: JsonPars(frmregister) }, "text").then((e) => {
                "ok" == CLearChar(e.trim())
                    ? (tshow("success", "¡REGISTRADO!", "<br>Revise su correo y ingrese nuevamente", 3500),
                      setTimeout(() => {
                          location.reload();
                      }, 3500))
                    : (spin(btnregistro, !1, "Registrate"), tshow("danger", "¡ERROR!", e, 4500));
            });
    });
const FetchAsynAwait = async (e, t, a = "json") => {
        const s = { method: "POST", cache: "no-cache", body: JSON.stringify(t), headers: { "Content-Type": "application/json" } };
        try {
            const t = await fetch(e, s);
            return "json" === (await a) ? t.json() : t.text();
        } catch (e) {
            return e;
        }
    },
    JsonPars = (e) => {
        let t = new FormData(e),
            a = {};
        return (
            t.forEach(function (e, t) {
                a[t] = e;
            }),
            a
        );
    },
    CLearChar = (e) =>
        (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = e.replace('"', "")).replace("\ufeff", "")).replace(".", "")).replace(":", "")).replace(",", "")).replace(";", "")).replace("-", "")).replace(
            "(",
            ""
        )).replace(")", "")).replace("*", "")).replace("+", "")).replace('"', "")).replace("[", "")).replace("]", "")).replace(" ", "")),
    spin = (e, t = !0, a = "") => {
        t ? ((e.disabled = !0), (e.innerHTML = '<i class="fa fa-spinner fa-spin fa-2x"></i>')) : ((e.disabled = !1), (e.innerHTML = a));
    };
function isNumber(e) {
    return /^-?[\d.]+(?:e-?\d+)?$/.test(e);
}
