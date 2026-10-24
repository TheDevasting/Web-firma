document.addEventListener('DOMContentLoaded', function () {

    // ---- Menú móvil ----
    var btnMenu = document.getElementById('btn-menu');
    var menu = document.getElementById('menu');
    btnMenu.addEventListener('click', function () {
        var abierto = menu.classList.toggle('abierto');
        btnMenu.setAttribute('aria-expanded', abierto);
    });
    menu.addEventListener('click', function (e) {
        if (e.target.tagName === 'A') {
            menu.classList.remove('abierto');
            btnMenu.setAttribute('aria-expanded', 'false');
        }
    });

    // ---- Modales legales (<dialog> cierra con Esc automáticamente) ----
    document.querySelectorAll('[data-abrir]').forEach(function (b) {
        b.addEventListener('click', function () {
            document.getElementById(b.dataset.abrir).showModal();
        });
    });
    document.querySelectorAll('dialog').forEach(function (d) {
        d.querySelector('[data-cerrar]').addEventListener('click', function () { d.close(); });
        d.addEventListener('click', function (e) { if (e.target === d) d.close(); });
    });

    // ---- Formulario ----
    var form = document.getElementById('form-contacto');
    var respuesta = document.getElementById('respuesta-form');
    var btnEnviar = document.getElementById('btn-enviar');
    var textoBoton = btnEnviar.textContent;

    var reglas = {
        nombre:   function (v) { return /^[\p{L}\s'’.-]{3,100}$/u.test(v.trim()); },
        correo:   function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()); },
        telefono: function (v) { var d = v.replace(/\D/g, ''); return d.length >= 7 && d.length <= 15; },
        servicio: function (v) { return v !== ''; },
        mensaje:  function (v) { var n = v.trim().length; return n >= 10 && n <= 3000; },
        acepto:   function () { return form.elements.acepto.checked; }
    };

    function marcar(nombre, valido) {
        form.elements[nombre].closest('.campo').classList.toggle('error', !valido);
    }

    // Quita el error mientras la persona corrige
    form.addEventListener('input', function (e) {
        if (e.target.name && reglas[e.target.name]) marcar(e.target.name, true);
    });
    form.elements.telefono.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9+\s-]/g, '');
    });

    function mostrar(texto, clase) {
        respuesta.textContent = texto;
        respuesta.className = clase || '';
    }
    function reactivar() {
        btnEnviar.disabled = false;
        btnEnviar.textContent = textoBoton;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var todoBien = true;
        Object.keys(reglas).forEach(function (nombre) {
            var valor = nombre === 'acepto' ? '' : form.elements[nombre].value;
            var ok = reglas[nombre](valor);
            marcar(nombre, ok);
            if (!ok) todoBien = false;
        });
        if (!todoBien) {
            mostrar('Revisa los campos marcados en rojo antes de enviar.', 'mal');
            return;
        }

        btnEnviar.disabled = true;
        btnEnviar.textContent = 'Enviando...';
        mostrar('Procesando tu consulta confidencial...', 'ok');

        fetch('procesar_formulario.php', { method: 'POST', body: new FormData(form) })
            .then(function (res) {
                return res.json().catch(function () { throw new Error('Respuesta no válida'); });
            })
            .then(function (data) {
                if (data.status === 'success') {
                    window.location.href = 'gracias.html';
                } else {
                    mostrar(data.message || 'No pudimos enviar tu consulta. Inténtalo de nuevo.', 'mal');
                    reactivar();
                }
            })
            .catch(function () {
                // Pruebas locales (abriendo el archivo sin servidor): simula el éxito
                if (window.location.protocol === 'file:') {
                    setTimeout(function () { window.location.href = 'gracias.html'; }, 800);
                } else {
                    mostrar('Error de comunicación con el servidor. Inténtalo de nuevo en unos minutos.', 'mal');
                    reactivar();
                }
            });
    });
});