<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lector Fijo - SICA-EPO6</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Librería para lectura de códigos QR desde la cámara -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body class="bg-slate-50 min-h-screen">
    <header class="bg-[#800020] text-white py-3 px-6 shadow-md flex justify-between items-center">
        <div>
            <h2 class="text-xs font-light">C.C.T. 15EBH0008G • C.C.T. 15EBH0199N</h2>
            <h1 class="text-lg font-bold">ESCUELA PREPARATORIA OFICIAL NÚM. 6</h1>
        </div>
        <div>
            <a href="{{ route('login') }}" class="text-xs bg-white/10 hover:bg-white/20 py-2 px-4 rounded-lg transition">Regresar al Panel</a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto p-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 text-center">
            <h3 class="text-sm font-black text-gray-800 uppercase tracking-wide">Lector Fijo - Control de Entradas y Salidas</h3>
            <p class="text-xs text-gray-500 mt-1 mb-6">Selecciona el tipo de escaneo (Entrada o Salida) y escanea con la cámara frontal[cite: 8].</p>

            <!-- Botones de Modo -->
            <div class="flex justify-center gap-4 mb-6">
                <button id="btnEntrada" onclick="setModo('entrada')" class="px-6 py-2.5 bg-[#800020] text-white text-xs font-bold rounded-xl shadow transition">Registrar Entrada</button>
                <button id="btnSalida" onclick="setModo('salida')" class="px-6 py-2.5 bg-gray-100 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-200 transition">Registrar Salida</button>
            </div>

            <!-- Contenedor de la Cámara -->
            <div class="max-w-md mx-auto bg-black rounded-2xl overflow-hidden shadow-inner relative mb-4">
                <div id="reader" class="w-full"></div>
            </div>

            <!-- Controles de Cámara -->
            <div class="flex justify-center gap-3">
                <button onclick="iniciarCamara()" class="px-5 py-2 bg-[#800020] hover:bg-[#600018] text-white text-xs font-bold rounded-xl shadow transition">Activar Cámara Frontal</button>
                <button onclick="detenerCamara()" class="px-5 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold rounded-xl transition">Detener</button>
            </div>

            <!-- Resultado del Escaneo -->
            <div id="resultado" class="mt-6 p-4 bg-slate-100 rounded-xl hidden text-left max-w-md mx-auto">
                <p class="text-xs font-bold text-gray-700">Alumno detectado:</p>
                <p id="infoAlumno" class="text-sm text-gray-900 font-semibold mt-1"></p>
            </div>
        </div>
    </main>

    <script>
        let html5QrCode;
        let modoActual = 'entrada';

        function setModo(tipo) {
            modoActual = tipo;
            if(tipo === 'entrada') {
                document.getElementById('btnEntrada').className = "px-6 py-2.5 bg-[#800020] text-white text-xs font-bold rounded-xl shadow transition";
                document.getElementById('btnSalida').className = "px-6 py-2.5 bg-gray-100 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-200 transition";
            } else {
                document.getElementById('btnSalida').className = "px-6 py-2.5 bg-[#800020] text-white text-xs font-bold rounded-xl shadow transition";
                document.getElementById('btnEntrada').className = "px-6 py-2.5 bg-gray-100 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-200 transition";
            }
        }

        function iniciarCamara() {
            html5QrCode = new Html5Qrcode("reader");
            // Configuración estricta para forzar la cámara frontal (user) en el lector fijo de entrada
            html5QrCode.start(
                { facingMode: "user" }, 
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => {
                    // Éxito al leer el QR del alumno
                    document.getElementById('resultado').classList.remove('hidden');
                    document.getElementById('infoAlumno').innerText = `QR: ${decodedText} | Modo: ${modoActual.toUpperCase()} registrada con éxito.`;
                    // Aquí se enviaría la petición AJAX al backend de Laravel para guardar asistencia/retardo
                },
                (errorMessage) => {
                    // Errores de escaneo en tiempo real se pueden ignorar
                }
            ).catch((err) => {
                alert("No se pudo acceder a la cámara frontal: " + err);
            });
        }

        function detenerCamara() {
            if(html5QrCode) {
                html5QrCode.stop().then(() => {
                    console.log("Cámara detenida.");
                }).catch(err => {
                    console.log("Error al detener la cámara.");
                });
            }
        }
    </script>
</body>
</html>