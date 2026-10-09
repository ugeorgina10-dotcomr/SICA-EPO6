<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#800020">
<title>SICA-EPO6 | Sistema de Identificación y Control de Acceso</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fontawesome/6.4.0/css/all.min.css">
<style>
body{background:#f4f6f9;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;color:#333;margin:0}
/* Fondo epo6.png: public/img/epo6.png. Se ve a partir del login, con brillo y color originales. */
.bg-escuela{background:linear-gradient(rgba(0,0,0,.12),rgba(0,0,0,.12)),url("{{ asset('img/epo6.png') }}") center/cover no-repeat fixed !important;min-height:100vh}
#input-clave::-ms-reveal,#input-clave::-ms-clear{display:none}
.bg-login{background:#fff !important;min-height:100vh}
.main-card{background:rgba(255,255,255,.95);border-radius:20px;box-shadow:0 20px 40px rgba(0,0,0,.3);border:1px solid rgba(128,0,32,.25)}
.login-card{background:rgba(255,255,255,.96);border-radius:20px;box-shadow:0 15px 40px rgba(0,0,0,.35);border:1px solid #e2e8f0}
.btn-vinotinto{background:#800020;color:#fff;font-weight:600;transition:.3s}
.btn-vinotinto:hover{background:#600018;color:#fff}
.institution-logo{width:130px;height:130px;object-fit:contain}
.small-logo{width:75px;height:75px;object-fit:contain}
.cct-text{font-size:.9rem;color:#444;letter-spacing:.5px;font-weight:500}
.profile-card{border:2px solid #dee2e6;border-radius:12px;cursor:pointer;transition:.2s;background:#fff}
.profile-card.active{border-color:#800020;background:#fdf8f8}
.camera-box-ref{width:100%;max-width:320px;min-height:200px;background:#111;border-radius:10px;margin:0 auto;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center;flex-direction:column}
.camera-box-ref video{width:100%}
.form-control,.form-select{border-radius:10px}
.form-label{font-weight:600;color:#2c3e50}
.bloqueado-permiso{opacity:.55;cursor:not-allowed !important}
.res-scan{min-height:24px;font-weight:600;font-size:.85rem}
.tarjeta-grado{cursor:pointer;transition:.2s}
.tarjeta-grado:hover{box-shadow:0 6px 16px rgba(0,0,0,.12)}
.tarjeta-grado.seleccionada{border:2px solid #800020 !important;background:#fdf8f8 !important}
.tarjeta-grado.grado-desactivado{opacity:.4;pointer-events:none;cursor:not-allowed}
.preview-credencial{width:100%;max-width:340px;border:1px solid #dee2e6;border-radius:10px;background:#eee;display:block;margin:0 auto}
.badge-asignado{font-size:.7rem}
/* ===== Responsivo ===== */
@media (max-width:767.98px){
.form-control,.form-select{font-size:16px;min-height:46px} /* 16px evita el zoom automático de iPhone */
.btn{min-height:44px}
.main-card,.login-card{padding:1.5rem !important;border-radius:16px}
.institution-logo{width:90px;height:90px}
.small-logo{width:55px;height:55px}
h2.fw-bold{font-size:1.4rem}
h5.fw-bold{font-size:1.05rem}
#selector-pestanas-superior .profile-card{padding:.75rem !important}
#selector-pestanas-superior .profile-card i{font-size:1.1rem !important}
#selector-pestanas-superior .profile-card h6{font-size:.75rem}
.camera-box-ref{max-width:100%}
.table-responsive table{font-size:.75rem}
.d-flex.justify-content-between.align-items-center.mb-2.flex-wrap.gap-2 .badge{font-size:.75rem}
#stats-grados .grado-btn{font-size:.8rem}
}
@media (max-width:575.98px){
.btn-lg.rounded-pill{padding:.75rem 1.25rem !important;font-size:1rem !important}
.row.g-2.mb-4.justify-content-center .btn{font-size:.75rem}
#vista-sistema .d-flex.justify-content-between.align-items-center.mb-2.flex-wrap.gap-2{flex-direction:column;align-items:flex-start !important;gap:.75rem !important}
#vista-sistema .d-flex.align-items-center.gap-2{width:100%;justify-content:space-between}
.d-flex.justify-content-center.gap-3.mb-3.flex-wrap .btn{flex:1 1 45%;font-size:.8rem;white-space:normal}
.d-flex.gap-2.flex-wrap .btn{flex:1 1 100%}
#panel-control .row.text-center.mb-4.g-3 > div{margin-bottom:.5rem}
.card.main-card{padding:1rem !important}
}
@media (max-width:400px){
.institution-logo{width:70px;height:70px}
h2.fw-bold{font-size:1.15rem}
.btn{font-size:.8rem}
}
canvas#grafica-asistencias{max-width:100%;height:auto !important}
html{-webkit-text-size-adjust:100%}
body{padding-bottom:env(safe-area-inset-bottom)}
body{overflow-x:hidden}
/* ===== MODO TELÉFONO (orientador): solo escanear ===== */
body.modo-movil #selector-pestanas-superior,
body.modo-movil #panel-orientador > *:not(#bloque-camara-orientador),
body.modo-movil #btn-regresar-panel{display:none !important}
body.modo-movil #vista-sistema{padding:.75rem !important}
body.modo-movil .camera-box-ref{max-width:100%;min-height:260px}
body.modo-movil #res-cam-orient{font-size:1.1rem;min-height:40px}
body.modo-movil #bloque-camara-orientador .btn{width:100%;margin-bottom:.5rem;padding:.9rem}
</style>
</head>
<body id="cuerpo-contenedor" class="bg-login">
<div class="container d-flex flex-column justify-content-center align-items-center min-vh-100 py-4">
<!-- ============ INICIO ============ -->
<div id="vista-inicio" class="card main-card p-4 p-md-5 text-center col-12 col-md-8 col-lg-6">
 <div class="d-flex justify-content-center mb-3">
 <img src="{{ asset('img/logo6.png') }}" alt="Logo EPO6" class="institution-logo">
 </div>
<h2 class="fw-bold mb-1">SICA - EPO6</h2>
<p class="text-muted fw-semibold mb-3">Sistema de Identificación y Control de Acceso</p>
<div class="mb-4 bg-light p-3 rounded-4 border">
 <h5 class="fw-bold text-uppercase mb-1" style="color:#800020">Escuela Preparatoria Oficial Núm. 6</h5>
 <p class="cct-text mb-0">C.C.T. 15EBH0008G (Matutino) &nbsp;|&nbsp; C.C.T. 15EBH0199N (Vespertino)</p>
</div>
 <button onclick="mostrarSeccion('vista-login')" class="btn btn-vinotinto btn-lg rounded-pill py-3 px-5 shadow fs-5">
 <i class="fa-solid fa-right-to-bracket me-2"></i> INGRESAR AL SISTEMA
</button>
</div>
<!-- ============ LOGIN ============ -->
<div id="vista-login" class="card login-card p-4 p-md-5 col-12 col-sm-10 col-md-7 col-lg-5 d-none">
<div class="text-center mb-2">
 <img src="{{ asset('img/logo6.png') }}" alt="Logo EPO6" class="small-logo mb-2">
 <h4 class="fw-bold mb-1">SICA - EPO6</h4>
 <p class="text-muted small mb-2">Sistema de Identificación y Control de Acceso</p>
 <hr class="w-25 mx-auto my-2" style="border-top:2px solid #800020">
 <h5 class="fw-bold m-0" style="color:#800020">Iniciar Sesión</h5>
</div>
<form id="form-login" onsubmit="return false">
 <div class="mb-3">
 <label class="form-label">Seleccionar Rol</label>
 <select id="rol-select" class="form-select shadow-sm" onchange="actualizarCredenciales()">
 <option value="orientador">Orientador</option>
 <option value="director">Director</option>
 <option value="subdirector">Subdirector</option>
 <option value="control">Encargado de Control Escolar</option>
 </select>
 </div>
 <!-- Solo aparece cuando el rol elegido es Orientador: selecciona su nombre de la lista que
 subió Control Escolar. El grupo se calcula solo, no lo elige el orientador. -->
 <div id="campo-orientador-login" class="mb-3 d-none">
 <label class="form-label">Selecciona tu nombre</label>
 <select id="select-orientador-login" class="form-select shadow-sm">
 <option value="" selected disabled>Selecciona tu nombre...</option>
 </select>
 <div id="aviso-sin-orientadores" class="form-text text-danger d-none">
 Aún no hay orientadores dados de alta. Control Escolar debe subir la lista de orientadores primero.
 </div>
 </div>
 <div class="mb-3">
 <label class="form-label">Correo Institucional</label>
 <input type="email" id="input-correo" class="form-control shadow-sm" autocomplete="username" readonly>
 </div>
 <div class="mb-4">
 <label class="form-label">Contraseña</label>
 <div class="position-relative shadow-sm rounded">
 <input type="password" id="input-clave" class="form-control pe-5" placeholder="Escribe tu contraseña" autocomplete="current-password" onkeydown="if(event.key==='Enter') intentarEntrar()">
 <button type="button" id="btn-ver-clave" onclick="verOcultarClave()" aria-label="Mostrar contraseña" aria-pressed="false" title="Mostrar contraseña"
 style="position:absolute;top:0;bottom:0;right:0;width:46px;border:0;background:transparent;color:#6c757d;display:flex;align-items:center;justify-content:center;cursor:pointer">
 <svg id="ojo-abierto" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
 <svg id="ojo-cerrado" class="d-none" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.5 18.5 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
 </button>
 </div>
 </div>
 <button type="button" onclick="intentarEntrar()" class="btn btn-vinotinto w-100 py-3 rounded-3 shadow fs-5">Ingresar al Sistema</button>
</form>
</div>
<!-- ============ SISTEMA ============ -->
<div id="vista-sistema" class="card main-card p-3 p-md-4 col-12 col-lg-11 d-none text-start">
<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
 <div class="d-flex align-items-center gap-3">
 <img src="{{ asset('img/logo6.png') }}" alt="Logo EPO6" class="small-logo">
 <div>
 <h5 class="fw-bold text-uppercase m-0" style="color:#800020">Escuela Preparatoria Oficial Núm. 6</h5>
 <p class="cct-text m-0 small">C.C.T. 15EBH0008G (Matutino) - C.C.T. 15EBH0199N (Vespertino)</p>
 </div>
 </div>
 <div class="d-flex align-items-center gap-2">
 <span id="badge-rol-activo" class="badge bg-dark px-3 py-2 fs-6">Rol: --</span>
 <button onclick="location.reload()" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-sm">
 <i class="fa-solid fa-rotate me-1"></i> Recargar Sistema
 </button>
 </div>
</div>
<hr class="mt-2 mb-3">
<div id="selector-pestanas-superior" class="mb-4">
 <label class="form-label mb-2 text-uppercase fs-6">Selecciona tu sección:</label>
 <div class="row g-2 text-center justify-content-center" id="fila-pestanas">
 @foreach([['alumno','fa-user-graduate','Portal del Alumno'],['lector','fa-qrcode','Lector Fijo (Entrada/Salida)'],['orientador','fa-users-rectangle','Panel de Orientadores'],['control','fa-user-gear','Control Escolar']] as $t)
 <div class="col-md-3 col-tab-pestana">
 <div id="tab-{{ $t[0] }}" onclick="seleccionarPerfil('{{ $t[0] }}')" class="profile-card p-3">
 <i class="fa-solid {{ $t[1] }} mb-1 text-secondary" style="font-size:1.4rem"></i>
 <h6 class="fw-bold m-0 text-dark small">{{ $t[2] }}</h6>
 </div>
 </div>
 @endforeach
 </div>
</div>
<!-- ===== 1. PORTAL DEL ALUMNO ===== -->
<div id="panel-alumno" class="panel-perfil d-none">
 <!-- ===== PLANTILLA OFICIAL DE CREDENCIALES (solo Control Escolar, dentro del Portal del Alumno) ===== -->
 <div id="bloque-plantilla-oficial" class="p-4 bg-white rounded-4 border shadow-sm mb-4 d-none">
 <h6 class="fw-bold mb-2" style="color:#800020"><i class="fa-solid fa-id-card me-2"></i> Plantilla Oficial de Credenciales</h6>
 <p class="text-muted small mb-3">Reemplaza el diseño oficial (frente y reverso) para <b>todos los grupos del turno elegido</b>. Cada turno tiene su propia plantilla (cambia el C.C.T.). La imagen debe conservar las mismas proporciones y la misma distribución que la actual (frente 797×541, reverso 930×575 px, o mayor con la misma proporción) para que nombre, CURP, grado, grupo, turno y QR caigan en su lugar.</p>
 <div class="mb-3" style="max-width:260px">
 <label class="form-label small">Turno de la plantilla</label>
 <!-- CAMBIO: al cambiar este selector también se mueven los botones Matutino/Vespertino de abajo -->
 <select id="sel-turno-plantilla" class="form-select form-select-sm" onchange="setTurnoCred(this.value)">
 <option value="Matutino" selected>Matutino</option>
 <option value="Vespertino">Vespertino</option>
 </select>
 </div>
 <div class="row g-3">
 <div class="col-md-6">
 <label class="form-label small">Frente (.png/.jpg)</label>
 <div class="input-group input-group-sm mb-2">
 <input type="file" class="form-control" id="input-plantilla-oficial-frente" accept="image/png,image/jpeg">
 <button class="btn btn-vinotinto" type="button" onclick="subirPlantillaOficial('frente')">Actualizar</button>
 </div>
 <img id="prev-plantilla-oficial-frente" class="preview-credencial" alt="Frente actual">
 </div>
 <div class="col-md-6">
 <label class="form-label small">Reverso (.png/.jpg)</label>
 <div class="input-group input-group-sm mb-2">
 <input type="file" class="form-control" id="input-plantilla-oficial-reverso" accept="image/png,image/jpeg">
 <button class="btn btn-vinotinto" type="button" onclick="subirPlantillaOficial('reverso')">Actualizar</button>
 </div>
 <img id="prev-plantilla-oficial-reverso" class="preview-credencial" alt="Reverso actual">
 </div>
 </div>
 <button class="btn btn-outline-dark btn-sm mt-3" onclick="restaurarPlantillaOficial()">
 <i class="fa-solid fa-rotate-left me-1"></i> Restaurar plantilla original
 </button>
 <div id="resumen-plantilla-oficial" class="mt-2 small fw-semibold"></div>
 </div>

 
 <div class="text-center p-4 bg-light rounded-4 border mb-3">
 <h5 class="fw-bold text-uppercase mb-2" style="color:#800020">Credencial digital con código QR</h5>
 <p class="text-muted small mb-3">Elige el turno, selecciona el grupo y administra las credenciales. Cada turno usa su plantilla oficial (frente y reverso); solo el Encargado de Control Escolar puede cambiarla (bloque "Plantilla Oficial de Credenciales" de arriba).</p>
 <div class="d-flex justify-content-center gap-3 mb-3 flex-wrap">
 <button onclick="setTurnoCred('Matutino')" id="btn-turno-cred-matutino" class="btn btn-vinotinto px-4 py-2 fw-semibold shadow-sm small"><i class="fa-solid fa-sun me-1"></i> Matutino</button>
 <button onclick="setTurnoCred('Vespertino')" id="btn-turno-cred-vespertino" class="btn btn-outline-dark bg-white px-4 py-2 fw-semibold shadow-sm small"><i class="fa-solid fa-moon me-1"></i> Vespertino</button>
 </div>
 <div class="row g-2 mb-4 justify-content-center">
 @foreach(['Primero','Segundo','Tercero'] as $i => $gr)
 @foreach([1,2,3,4] as $n)
 <div class="col-lg-2 col-md-3 col-sm-4">
 <button onclick="selCred({{ $i+1 }},{{ $n }})" class="btn btn-outline-dark w-100 py-2 bg-white fw-semibold border shadow-sm small">{{ $gr }} {{ $n }}</button>
 </div>
 @endforeach
 @endforeach
 </div>
 <div id="resultado-credencial-box" class="d-none p-4 bg-white rounded-3 border shadow-sm mb-3 col-md-10 mx-auto text-start">
 <div class="d-flex justify-content-between mb-3">
 <h6 class="fw-bold m-0 text-danger" id="titulo-grupo-credencial"></h6>
 <span id="badge-plantilla" class="badge bg-secondary">Plantilla oficial</span>
 </div>
 <div class="mb-3 p-3 bg-light rounded-3 border">
 <label class="form-label small">1. Subir lista de alumnos (Excel/.csv con <b>CURP en la columna A</b> y el <b>nombre completo en la columna B</b>; o bien <b>Apellido paterno, Apellido materno y Nombre(s)</b> en las columnas B, C y D) — asigna el CURP y la credencial digital de cada alumno de este grupo y turno. El nombre siempre se guarda como <b>Apellido paterno, Apellido materno y Nombre(s)</b>.</label>
 <select id="orden-nombre-cred" class="form-select form-select-sm mb-2">
 <option value="reordenar" selected>El nombre viene como Nombre(s) + Apellidos (reordenar)</option>
 <option value="tal-cual">El nombre ya viene como Apellido paterno, materno y nombre (no cambiar)</option>
 </select>
 <div class="input-group input-group-sm">
 <input type="file" class="form-control" id="input-subir-lista-credencial" accept=".xlsx,.xls,.csv">
 <button class="btn btn-dark btn-accion-restringida" type="button" onclick="subirListaCredencial()">Subir Lista</button>
 </div>
 </div>
 <div class="table-responsive bg-white rounded-3 border mb-3">
 <table class="table table-striped table-hover align-middle mb-0 small">
 <thead class="table-dark"><tr><th>Matrícula (CURP)</th><th>Nombre Completo</th><th class="text-center">Acción</th></tr></thead>
 <tbody id="tabla-alumnos-credencial"></tbody>
 </table>
 </div>
 <button onclick="descargarCredencialesPDF()" class="btn btn-vinotinto w-100 py-3 shadow-sm btn-accion-restringida">
 <i class="fa-solid fa-file-pdf me-2"></i> Descargar Credenciales Completas del Grupo (Frente y Reverso)
 </button>
 </div>
 <div id="vista-editar-credencial-individual" class="d-none p-4 bg-white rounded-3 border shadow-sm mb-3 col-md-10 mx-auto text-start">
 <div class="d-flex justify-content-between mb-3">
 <h6 class="fw-bold m-0 text-danger"><i class="fa-solid fa-pen-to-square me-2"></i>Editar Credencial Individual</h6>
 <button onclick="regresarListaGrupo()" class="btn btn-outline-dark btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Regresar a Todos</button>
 </div>
 <div class="row">
 <div class="col-md-6">
 <div class="mb-3"><label class="form-label small">Matrícula (CURP)</label>
 <input type="text" id="edit-curp-alumno" class="form-control form-control-sm" oninput="actualizarVistaPreviaCredencial()"></div>
 <div class="mb-3"><label class="form-label small">Nombre Completo (Apellido paterno, Apellido materno y Nombre)</label>
 <input type="text" id="edit-nombre-alumno" class="form-control form-control-sm" oninput="actualizarVistaPreviaCredencial()"></div>
 <div class="d-flex gap-2">
 <button onclick="guardarCambiosCredencial()" class="btn btn-success w-100 btn-accion-restringida"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cambios</button>
 <button onclick="descargarCredencialIndividual()" class="btn btn-vinotinto w-100 btn-accion-restringida"><i class="fa-solid fa-download me-1"></i> Descargar PDF</button>
 </div>
 </div>
 <div class="col-md-6">
 <label class="form-label small d-block text-center">Vista previa (se actualiza mientras editas)</label>
 <div class="row g-2">
 <div class="col-6 text-center">
 <span class="d-block small text-muted mb-1">Frente</span>
 <canvas id="preview-canvas-frente" width="380" height="258" class="preview-credencial"></canvas>
 </div>
 <div class="col-6 text-center">
 <span class="d-block small text-muted mb-1">Reverso</span>
 <canvas id="preview-canvas-reverso" width="380" height="235" class="preview-credencial"></canvas>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
</div>
<!-- ===== 2. LECTOR FIJO (no visible para Director/Subdirector) ===== -->
<div id="panel-lector" class="panel-perfil d-none">
 <div class="text-center p-4 bg-light rounded-4 border mb-3">
 <h5 class="fw-bold text-uppercase mb-1" style="color:#800020">Lector Fijo - Control de Entradas y Salidas</h5>
 <p class="text-muted small mb-3">Elige el tipo de escaneo, enciende la cámara y muestra el QR de la credencial:</p>
 <div class="d-flex justify-content-center gap-3 mb-3 flex-wrap">
 <button onclick="setModoEscaneo('Entrada')" id="btn-modo-entrada" class="btn btn-success px-4 py-2 fw-semibold shadow-sm"><i class="fa-solid fa-right-to-bracket me-2"></i> Registrar Entrada</button>
 <button onclick="setModoEscaneo('Salida')" id="btn-modo-salida" class="btn btn-outline-dark bg-white px-4 py-2 fw-semibold shadow-sm"><i class="fa-solid fa-right-from-bracket me-2"></i> Registrar Salida</button>
 </div>
 <div class="camera-box-ref mb-2 border border-danger shadow">
 <div class="cam-ph text-center text-warning"><i class="fa-solid fa-camera fa-2x mb-2"></i><span class="small d-block fw-semibold">[ <span id="lbl-modo">Entrada</span> ] - Cámara apagada</span></div>
 <div id="cam-fija" style="width:100%"></div>
 </div>
 <div id="res-cam-fija" class="res-scan mb-2"></div>
 <button onclick="toggleCamaraFija()" class="btn btn-vinotinto px-4"><i class="fa-solid fa-poweroff me-2"></i> Encender / Apagar Cámara</button>
 </div>
</div>
<!-- ===== 3. PANEL DE ORIENTADORES ===== -->
<div id="panel-orientador" class="panel-perfil d-none">
 <div id="bloque-turno-panel" class="d-flex justify-content-center gap-3 mb-3 flex-wrap">
 <button onclick="setTurno('Matutino')" id="btn-turno-matutino" class="btn btn-vinotinto px-4 py-2 fw-semibold shadow-sm small"><i class="fa-solid fa-sun me-1"></i> Turno Matutino</button>
 <button onclick="setTurno('Vespertino')" id="btn-turno-vespertino" class="btn btn-outline-dark bg-white px-4 py-2 fw-semibold shadow-sm small"><i class="fa-solid fa-moon me-1"></i> Turno Vespertino</button>
 </div>
 <!-- VISUALIZAR GRUPO: para Control Escolar, Director y Subdirector (eligen cualquier grupo) -->
 <div id="bloque-visualizar-grupo" class="mb-3">
 <label class="form-label mb-2 text-uppercase fs-6">Visualizar Grupo:</label>
 <div class="row g-2">
 @foreach([1,2,3] as $g)
 <div class="col-md-4">
 <label class="form-label small mb-1">Grado {{ $g }}</label>
 <select id="sel-grado-{{ $g }}" class="form-select form-select-sm shadow-sm" onchange="elegirGrupo({{ $g }},this.value)">
 <option value="" selected disabled>Selecciona el grupo...</option>
 @foreach([1,2,3,4] as $n)<option value="{{ $n }}">Grupo {{ $n }}</option>@endforeach
 </select>
 </div>
 @endforeach
 </div>
 </div>
 <!-- MI GRUPO: solo para el rol Orientador. Su grupo lo asigna Control Escolar, aquí no lo elige. -->
 <div id="bloque-mi-grupo-orientador" class="mb-3 d-none">
 <label class="form-label mb-2 text-uppercase fs-6">Mi Grupo Asignado:</label>
 <div class="p-3 bg-light rounded-3 border d-flex align-items-center gap-3 flex-wrap">
 <div id="sin-grupo-orientador" class="text-danger fw-semibold small d-none">
 <i class="fa-solid fa-triangle-exclamation me-1"></i> Aún no tienes un grupo asignado. Solicita a Control Escolar que te asigne uno.
 </div>
 <div id="con-grupo-orientador" class="d-none w-100">
 <div class="row g-2 align-items-end">
 <div class="col-md-8">
 <label class="form-label small mb-1">Grupo(s) que te corresponden</label>
 <select id="sel-mi-grupo" class="form-select form-select-sm shadow-sm" onchange="elegirMiGrupo(this.value)"></select>
 </div>
 <div class="col-md-4">
 <span class="badge bg-dark">Solo puedes ver tu(s) grupo(s) asignado(s)</span>
 </div>
 </div>
 </div>
 </div>
 </div>
 <!-- SOLO CONTROL ESCOLAR: dar de alta a los orientadores -->
 <div id="bloque-orientadores-control" class="p-3 bg-light rounded-4 border shadow-sm mb-4 d-none">
 <h6 class="fw-bold mb-2" style="color:#800020"><i class="fa-solid fa-user-plus me-2"></i> Subir Lista de Orientadores</h6>
 <p class="text-muted small mb-3">Excel, CSV o PDF (.xlsx/.xls/.csv/.pdf) con el <b>nombre completo de cada orientador(a)</b>, uno por fila o por línea. Después, asígnalos a un grado, grupo y turno en el bloque de horario de abajo.</p>
 <div class="row g-3 align-items-end mb-2">
 <div class="col-md-8"><label class="form-label small">Archivo</label><input type="file" id="archivo-orientadores" class="form-control form-control-sm" accept=".xlsx,.xls,.csv,.pdf"></div>
 <div class="col-md-4"><button onclick="subirOrientadores()" class="btn btn-vinotinto btn-sm w-100 py-2 fw-semibold"><i class="fa-solid fa-upload me-1"></i> Subir Orientadores</button></div>
 </div>
 <div id="resumen-orientadores" class="small text-muted"></div>
 <div id="lista-orientadores-actual" class="small mt-2"></div>
 </div>
 <!-- SOLO CONTROL ESCOLAR: subir lista del grupo elegido arriba -->
 <div id="bloque-listas-orientador" class="p-3 bg-light rounded-4 border shadow-sm mb-4 d-none">
 <h6 class="fw-bold mb-2" style="color:#800020"><i class="fa-solid fa-file-arrow-up me-2"></i> Subir Lista del Grupo</h6>
 <p class="text-muted small mb-3">Excel, CSV o PDF (.xlsx/.xls/.csv/.pdf) con el <b>nombre completo</b>, uno por fila o por línea (o en 3 columnas: Apellido paterno, Apellido materno y Nombre(s)). Se asigna al grado, grupo y turno elegidos arriba, y el nombre se guarda como <b>Apellido paterno, Apellido materno y Nombre(s)</b>. El CURP y la credencial se asignan después desde el <b>Portal del Alumno</b>.</p>
 <div class="row g-3 align-items-end">
 <div class="col-md-4"><label class="form-label small">Grupo seleccionado</label><input type="text" id="lbl-grupo-lista" class="form-control form-control-sm" value="Selecciona grado y grupo arriba" readonly></div>
 <div class="col-md-5"><label class="form-label small">Archivo</label><input type="file" id="archivo-lista" class="form-control form-control-sm" accept=".xlsx,.xls,.csv,.pdf"></div>
 <div class="col-md-3"><button onclick="subirLista()" class="btn btn-vinotinto btn-sm w-100 py-2 fw-semibold btn-accion-restringida"><i class="fa-solid fa-upload me-1"></i> Subir Lista</button></div>
 <div class="col-12">
 <label class="form-label small">Orden de los nombres en el archivo</label>
 <select id="orden-nombre-lista" class="form-select form-select-sm">
 <option value="reordenar" selected>Vienen como Nombre(s) + Apellidos (reordenar a Apellido paterno, materno y nombre)</option>
 <option value="tal-cual">Ya vienen como Apellido paterno, materno y nombre (no cambiar)</option>
 </select>
 </div>
 </div>
 </div>
 <!-- SOLO CONTROL ESCOLAR: horario de entrada/salida y orientador del grupo -->
 <div id="bloque-horario-control" class="p-3 bg-light rounded-4 border shadow-sm mb-4 d-none">
 <h6 class="fw-bold mb-2" style="color:#800020"><i class="fa-solid fa-clock me-2"></i> Horario y Orientador(a) del Grupo</h6>
 <p class="text-muted small mb-3">Define la hora límite de entrada (para marcar retardo), la hora de cierre (para marcar permanencia) y el orientador(a) del grado, grupo y turno seleccionados arriba. El horario y el orientador son independientes en cada turno. Un orientador(a) ya asignado a otro grupo aparece bloqueado aquí para evitar duplicar su asignación.</p>
 <div class="row g-3 align-items-end mb-3">
 <div class="col-md-3"><label class="form-label small">Hora límite de entrada</label><input type="time" id="horario-entrada-limite" class="form-control form-control-sm"></div>
 <div class="col-md-3"><label class="form-label small">Hora de cierre (salida)</label><input type="time" id="horario-salida-limite" class="form-control form-control-sm"></div>
 <div class="col-md-4"><label class="form-label small">Orientador(a) del grupo</label>
 <select id="orientador-grupo-select" class="form-select form-select-sm">
 <option value="" selected>Sin asignar</option>
 </select>
 </div>
 <div class="col-md-2"><button onclick="guardarHorarioGrupo()" class="btn btn-vinotinto btn-sm w-100 py-2 fw-semibold"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar</button></div>
 </div>
 <div class="row g-3 align-items-end">
 <div class="col-md-6"><label class="form-label small">Archivo de horarios (.xlsx/.xls/.csv): columnas Grado, Grupo, HoraEntrada, HoraSalida, NombreOrientador y Turno (opcional; si no se indica se usa el turno activo)</label><input type="file" id="archivo-horarios" class="form-control form-control-sm" accept=".xlsx,.xls,.csv"></div>
 <div class="col-md-3"><button onclick="subirHorarios()" class="btn btn-dark btn-sm w-100 py-2 fw-semibold"><i class="fa-solid fa-upload me-1"></i> Subir Horarios</button></div>
 </div>
 <div id="resumen-horario-grupo" class="mt-2 small text-muted"></div>
 </div>
 <!-- Cámara (orientador / director / subdirector) -->
 <div id="bloque-camara-orientador" class="p-3 bg-light rounded-4 border mb-4 text-center">
 <h6 class="fw-bold mb-3 text-uppercase" style="color:#800020;font-size:.9rem">Cámara de Escaneo (Entradas y Salidas)</h6>
 <!-- CAMBIO: cualquier orientador puede registrar entrada o salida de cualquier alumno -->
 <div class="d-flex justify-content-center gap-2 mb-3 flex-wrap">
 <button onclick="setModoOrient('Entrada')" id="btn-orient-entrada" class="btn btn-success px-3 py-2 fw-semibold shadow-sm small"><i class="fa-solid fa-right-to-bracket me-1"></i> Entrada</button>
 <button onclick="setModoOrient('Salida')" id="btn-orient-salida" class="btn btn-outline-dark bg-white px-3 py-2 fw-semibold shadow-sm small"><i class="fa-solid fa-right-from-bracket me-1"></i> Salida</button>
 </div>
 <div class="camera-box-ref mb-2 border shadow-sm">
 <div class="cam-ph text-center text-warning"><i class="fa-solid fa-qrcode fa-2x mb-1"></i><span class="d-block small">Escáner apagado</span></div>
 <div id="cam-orient" style="width:100%"></div>
 </div>
 <div id="res-cam-orient" class="res-scan mb-2"></div>
 <div id="aviso-camara-bloqueada" class="small text-danger fw-semibold mb-2 d-none"><i class="fa-solid fa-lock me-1"></i> Necesitas autorización de Control Escolar para usar la cámara.</div>
 <button onclick="activarCamaraOrientador()" id="btn-activar-camara-orientador" class="btn btn-vinotinto px-4 py-2 rounded-pill shadow-sm small btn-accion-restringida"><i class="fa-solid fa-camera me-1"></i> Activar Cámara</button>
 <button onclick="detener('cam-orient')" class="btn btn-dark py-2 px-3 rounded-pill small">Apagar</button>
 </div>
 <div class="row g-3 mb-4 bg-light p-3 rounded-3 border">
 <div class="col-md-5"><label class="form-label">Buscador de Alumno</label><input type="text" id="buscador-alumno" class="form-control shadow-sm" placeholder="Ej. Maria Jose..." oninput="renderTabla()"></div>
 <div class="col-md-4"><label class="form-label">Calendario (fecha a consultar)</label><input type="date" id="calendario-orientador" class="form-control shadow-sm" onchange="renderTabla()"></div>
 <div class="col-md-3">
 <label class="form-label">Período del reporte</label>
 <select id="sel-periodo-reporte" class="form-select shadow-sm">
 <option value="semana" selected>Semana</option>
 <option value="mes">Mes</option>
 </select>
 </div>
 </div>
 <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
 <h6 class="fw-bold m-0" id="titulo-tabla-orientador">Turno Matutino</h6>
 <div class="d-flex gap-2 flex-wrap">
 <button onclick="descargarReporte('Excel')" class="btn btn-success px-4 py-2 fw-semibold shadow-sm small btn-accion-restringida"><i class="fa-solid fa-file-excel me-1"></i> Descargar Excel</button>
 <button onclick="descargarReporte('PDF')" class="btn btn-danger px-4 py-2 fw-semibold shadow-sm small btn-accion-restringida"><i class="fa-solid fa-file-pdf me-1"></i> Descargar PDF</button>
 </div>
 </div>
 <div class="table-responsive bg-white rounded-3 p-2 border shadow-sm mb-4">
 <table class="table table-striped table-hover align-middle mb-0 small">
 <thead class="table-dark"><tr><th>Matrícula / QR</th><th>Alumno</th><th>Grado</th><th>Grupo</th><th>Entrada</th><th>Salida</th><th>Permanencia</th><th>Estatus</th><th class="text-center">Acción</th></tr></thead>
 <tbody id="tabla-cuerpo-orientador"></tbody>
 </table>
 </div>
</div>
<!-- ===== 4. CONTROL ESCOLAR ===== -->
<div id="panel-control" class="panel-perfil d-none">
 <div class="alert alert-warning py-2 mb-3 small fw-semibold"><i class="fa-solid fa-shield-halved me-1"></i>Control Escolar: estadísticas y asignación de permisos. Las listas, los orientadores y los horarios se suben en el <b>Panel de Orientadores</b> (elige turno, grado y grupo).</div>
 <div class="row text-center mb-4 g-3">
 <div class="col-md-4"><div class="p-3 bg-white rounded-4 border shadow-sm"><span class="text-muted d-block fw-bold small mb-1">TOTAL ALUMNOS</span><h3 class="fw-bold mb-0" id="st-total">0</h3></div></div>
 <div class="col-md-4"><div class="p-3 bg-white rounded-4 border shadow-sm"><span class="text-muted d-block fw-bold small mb-1">ASISTENCIAS DE HOY</span><h3 class="fw-bold text-success mb-0" id="st-asis">0</h3></div></div>
 <div class="col-md-4"><div class="p-3 bg-white rounded-4 border shadow-sm"><span class="text-muted d-block fw-bold small mb-1">RETARDOS Y PERMANENCIAS</span><h3 class="fw-bold text-primary mb-0" id="st-ret">0</h3></div></div>
 </div>
 <div class="p-4 bg-white rounded-4 border shadow-sm mb-4">
 <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
 <h6 class="fw-bold m-0" style="color:#800020"><i class="fa-solid fa-chart-pie me-2"></i> Gráficas Comparativas y Estadísticas</h6>
 <div class="d-flex align-items-center gap-2 flex-wrap">
 <select id="sel-periodo-stats" class="form-select form-select-sm" style="width:auto" onchange="renderStats()">
 <option value="hoy" selected>Hoy</option>
 <option value="semana">Semana</option>
 <option value="mes">Mes</option>
 </select>
 <button onclick="descargarGraficas('Excel')" class="btn btn-success px-4 py-2 fw-semibold shadow-sm small btn-accion-restringida"><i class="fa-solid fa-file-excel me-1"></i> Excel</button>
 <button onclick="descargarGraficas('PDF')" class="btn btn-danger px-4 py-2 fw-semibold shadow-sm small btn-accion-restringida"><i class="fa-solid fa-file-pdf me-1"></i> PDF</button>
 </div>
 </div>
 <p class="text-muted small mb-2" id="subtitulo-periodo-stats">Estadísticas de asistencia -Hoy</p>
 <p class="small mb-2"><span class="fw-semibold" style="color:#800020">Se descargará:</span> <span id="lbl-descarga-seleccion" class="fw-semibold">Estadísticas generales (todos los grados)</span></p>
 <div class="mb-3" style="max-width:640px;margin:0 auto"><canvas id="grafica-asistencias" height="220"></canvas></div>
 <div class="row text-center g-3" id="stats-grados"></div>
 </div>

<div id="bloque-permisos-roles" class="p-4 bg-white rounded-4 border shadow-sm mb-4 d-none">
 <h6 class="fw-bold mb-2" style="color:#800020"><i class="fa-solid fa-user-lock me-2"></i> Permisos y Roles</h6>
 <p class="text-muted small mb-3">Define si el rol solo visualiza o también puede realizar acciones (descargas, marcar salidas, subir listas, editar credenciales, usar la cámara de escaneo).</p>
 <div class="row g-3 align-items-end">
 <div class="col-md-4"><label class="form-label small">Rol</label>
 <select id="select-rol-permiso" class="form-select form-select-sm">
 <option value="director">Director</option>
 <option value="subdirector">Subdirector</option>
 <option value="orientador">Orientador</option>
 </select>
 </div>
 <div class="col-md-5"><label class="form-label small">Tarea a realizar</label>
 <select id="select-tarea-permiso" class="form-select form-select-sm">
 <option value="ver">Solo visualizar</option>
 <option value="editar">Puede editar / realizar acciones (incluye cámara)</option>
 </select>
 </div>
 <div class="col-md-3"><button onclick="guardarPermisoRol()" class="btn btn-vinotinto btn-sm w-100 py-2 fw-semibold"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar</button></div>
 </div>
 <div id="resumen-permisos" class="mt-3 small text-muted"></div>
 </div>
</div>
<div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
 <button onclick="regresarPanel()" id="btn-regresar-panel" class="btn btn-vinotinto px-4 rounded-pill fw-semibold shadow-sm d-none"><i class="fa-solid fa-arrow-left me-2"></i> Regresar</button>
 <button onclick="cerrarSesion()" class="btn btn-dark px-4 rounded-pill fw-semibold shadow-sm ms-auto">Cerrar Sesión</button>
</div>
</div>
</div>
<div id="canvas-temporal" style="position:absolute;left:-9999px;top:-9999px;"></div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
<script>
if (typeof pdfjsLib !== 'undefined') {
 pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js';
}
</script>
<script>
/* Lo incrusta el servidor al abrir la página: quién tiene sesión y la lista de nombres de orientadores.
   Así al abrir NO hace falta pedir /auth/me ni /api/sica/orientadores (arranque más rápido).
   OJO: esto solo sirve para mostrar la pantalla; los permisos reales los decide siempre el servidor. */
window.SICA_SESION = @json($sesion ?? null);
window.SICA_ORIENTADORES = @json($orientadoresLogin ?? []);
</script>
<script>
/* ============ DATOS (localStorage del navegador) ============ */
const LS = 'sica_epo6_v4';
/* Limpieza única de datos guardados en el navegador.
 Para volver a borrar en el futuro, cambia '_3' por '_4'.
 (Se subió a _3 porque cambió el formato de las claves de horario y de plantillas: ahora llevan turno.) */
const LIMPIEZA = 'sica_epo6_limpieza_3';
if (!localStorage.getItem(LIMPIEZA)) {
 localStorage.removeItem(LS);
 localStorage.removeItem('sica_epo6_plantilla_oficial');
 localStorage.setItem(LIMPIEZA, '1');
}
// Detecta si se abrió desde un teléfono (cámara trasera, modo "solo escanear" para el orientador)
const ES_MOVIL = /Android|iPhone|iPad|iPod|Mobile|Tablet|Silk|webOS|BlackBerry|Opera Mini/i.test(navigator.userAgent)
 || (/Macintosh/i.test(navigator.userAgent) && navigator.maxTouchPoints > 1) // iPad con iPadOS se identifica como Mac
 || (window.matchMedia && window.matchMedia('(pointer: coarse)').matches); // pantalla táctil (aunque use "sitio de escritorio")
const correosRoles = {orientador:'orientador1@epo6.edu.mx',director:'director@epo6.edu.mx',subdirector:'subdirector@epo6.edu.mx',control:'control.escolar@epo6.edu.mx'};
const nombresRoles = {orientador:'Orientador',director:'Director',subdirector:'Subdirector',control:'Encargado de Control Escolar'};
const ordenPaneles = ['orientador','lector','alumno'];
let db = JSON.parse(localStorage.getItem(LS) || 'null') || {
 permisos: {orientador:'editar', director:'ver', subdirector:'ver'},
 plantillas: {}, // claveGrupo -> {frente:{img,fmt}, reverso:{img,fmt}} (override opcional por grupo)
 horarios: {}, // claveGrupo ("Turno-grado-grupo") -> {entrada, salida, orientadorId}
 orientadores: [], // {id, nombre}
 alumnos: []
};
// Compatibilidad con datos guardados en versiones anteriores
db.plantillas = db.plantillas || {};
db.horarios = db.horarios || {};
db.orientadores = db.orientadores || [];
Object.keys(db.horarios).forEach(k => {
 const h = db.horarios[k];
 if (h && h.orientador && !h.orientadorId) {
 // Migración: si existía un nombre libre de orientador, se crea el registro correspondiente
 let o = db.orientadores.find(x => norm(x.nombre) === norm(h.orientador));
 if (!o) { o = {id: generarId(), nombre: h.orientador}; db.orientadores.push(o); }
 h.orientadorId = o.id;
 }
});
// El teléfono nunca sobrescribe toda la base en el servidor: solo envía cada escaneo (registrar_asistencia).
const save = () => {
 localStorage.setItem(LS, JSON.stringify(db));
 if (!ES_MOVIL) sincronizarConServidor('guardar_todo', db);
};
/* ============ SINCRONIZACIÓN CON EL SERVIDOR (MySQL vía Laravel) ============
 Envía {accion, datos} a /api/sica/sync (ControlEscolarController@sync).
 Si el backend no responde, falla en silencio y el sistema sigue con localStorage. */
let _ultimoAvisoPermiso = 0;
function avisoPermiso(mensaje) {
 if (Date.now() - _ultimoAvisoPermiso < 5000) return;   // un solo aviso cada 5 s
 _ultimoAvisoPermiso = Date.now();
 alert(mensaje || 'No tienes permiso para realizar esta acción.');
}
function sincronizarConServidor(accion, datos, reintento) {
 try {
  fetch('/api/sica/sync', {
   method: 'POST',
   headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '' },
   body: JSON.stringify({ accion: accion, datos: datos })
  }).then(r => {
   // 419: el token CSRF venció -> se pide uno nuevo y se reintenta UNA vez
   if (r.status === 419 && !reintento) return refrescarCsrf().then(() => sincronizarConServidor(accion, datos, true));
   if (r.status === 401) { sesionExpirada(); return; }
   if (r.status === 403) return r.json().then(d => avisoPermiso(d && d.mensaje)).catch(() => avisoPermiso());
  }).catch(() => { /* sin conexión: se ignora, localStorage sigue funcionando */ });
 } catch (err) { /* entorno sin fetch: se ignora */ }
}
/* Mezcla el estado del servidor con el local sin perder entradas/salidas ya registradas.
 Los alumnos se emparejan por CURP (el id del servidor es distinto al id local). */
function fusionarServidor(s) {
 if (!s || !Array.isArray(s.alumnos)) return;
 const clv = a => a.curp ? 'c:' + a.curp : 'i:' + a.id;
 const locales = new Map(db.alumnos.map(a => [clv(a), a])), claves = new Set();
 const lista = s.alumnos.map(sa => {
  claves.add(clv(sa)); sa.reg = sa.reg || {}; sa.enServidor = true;
  const la = locales.get(clv(sa));
  if (la) {
   sa.id = la.id;
   if (la.reg) Object.keys(la.reg).forEach(f => {
    const l = la.reg[f], r = sa.reg[f] = sa.reg[f] || {};
    if (l.entrada && !r.entrada) r.entrada = l.entrada;
    if (l.salida && !r.salida) r.salida = l.salida;
   });
  }
  return sa;
 });
 // Se conservan los locales aún no enviados; los que ya estuvieron en el servidor y ya no están (eliminados) se quitan
 db.alumnos = lista.concat(db.alumnos.filter(a => !claves.has(clv(a)) && !a.enServidor));
 if (s.horarios) db.horarios = s.horarios;
 if (s.orientadores) db.orientadores = s.orientadores;
 if (s.permisos) db.permisos = s.permisos;
 localStorage.setItem(LS, JSON.stringify(db));
}
/* Pide un token CSRF nuevo (cuando la sesión venció, el anterior ya no sirve) */
function refrescarCsrf() {
 return fetch('/auth/me', {headers:{'Accept':'application/json'}})
 .then(r => r.json())
 .then(d => { if (d && d.csrf) $('meta[name="csrf-token"]').attr('content', d.csrf); })
 .catch(() => {});
}
/* Lista de nombres de orientadores para la pantalla de login (no incluye alumnos ni CURP) */
function cargarOrientadoresLogin() {
 return fetch('/api/sica/orientadores', {headers:{'Accept':'application/json'}})
 .then(r => r.ok ? r.json() : null)
 .then(lista => {
 if (!Array.isArray(lista)) return;
 db.orientadores = lista;
 localStorage.setItem(LS, JSON.stringify(db));
 if (!rolActivo && !$('#select-orientador-login').val()) actualizarCredenciales();
 })
 .catch(() => {});
}
/* El servidor respondió 401: no hay sesión (nunca se inició o ya venció) */
function sesionExpirada() {
 if (rolActivo) {
 alert('Tu sesión expiró. Vuelve a iniciar sesión.');
 cerrarSesion();
 } else {
 refrescarCsrf();
 cargarOrientadoresLogin();
 }
}
/* Descarga el estado del servidor (listas, CURPs, horarios, asistencias de todos los dispositivos) */
function cargarDesdeServidor() {
 return fetch('/api/sica/estado', {headers:{'Accept':'application/json'}})
  .then(r => { if (r.status === 401) { sesionExpirada(); return null; } return r.ok ? r.json() : null; })
  .then(s => {
   if (!s) return;
   fusionarServidor(s);
   if (orientadorActivo) {
    orientadorActivo = db.orientadores.find(o => o.id === orientadorActivo.id) || orientadorActivo;
    gruposDelOrientador = calcularGruposOrientador(orientadorActivo.id);
   }
   if (rolActivo) renderTabla();
   else if (!$('#vista-login').hasClass('d-none') && !$('#select-orientador-login').val()) actualizarCredenciales();
  })
  .catch(() => {});
}
let rolActivo = null, turnoActivo = 'Matutino', indicePanelActual = 0, modoEscaneo = 'Entrada';
let filtro = null, credSel = null, idEditando = null, chartAsistencias = null, gradoSeleccionado = null;
let turnoCred = 'Matutino'; // turno elegido en el Portal del Alumno (credenciales)
let orientadorActivo = null; // {id, nombre} cuando el rol activo es 'orientador'
let gruposDelOrientador = []; // [{turno,grado,grupo}] calculados a partir de db.horarios
const scanners = {}; let ultimo = {t:'', ts:0};
// dataURL cacheado de la plantilla oficial de cada turno
const plantillasDefault = {Matutino:{frente:null, reverso:null}, Vespertino:{frente:null, reverso:null}};
const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const norm = t => (t||'').toString().normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().trim();
const p2 = n => String(n).padStart(2,'0');
const fechaLocal = d => { d = d || new Date(); return d.getFullYear()+'-'+p2(d.getMonth()+1)+'-'+p2(d.getDate()); };
const hoy = () => fechaLocal();
const horaAhora = () => { const d = new Date(); return p2(d.getHours())+':'+p2(d.getMinutes()); };
const toMin = hm => { const [h,m] = hm.split(':').map(Number); return h*60+m; };
/* Clave de grupo: "Turno-grado-grupo" (ej. "Vespertino-1-2"). Matutino y Vespertino son grupos distintos. */
const claveGrupo = (grado, grupo, turno) => (turno || 'Matutino') + '-' + grado + '-' + grupo;
/* Lee una clave. Acepta también el formato viejo "grado-grupo" (se toma como Matutino). */
const parseClave = k => {
 const p = String(k).split('-');
 return p.length === 2
  ? {turno:'Matutino', grado:Number(p[0]), grupo:Number(p[1])}
  : {turno:p[0], grado:Number(p[1]), grupo:Number(p[2])};
};
const generarId = () => 'id_' + Date.now().toString(36) + Math.random().toString(36).slice(2,7);
const LIM = {Matutino:{retardo:430,fin:840}, Vespertino:{retardo:790,fin:1200}}; // 7:10-14:00 / 13:10-20:00 (por defecto)
const nomGrado = ['', 'Primero', 'Segundo', 'Tercero'];
const ROMANOS = ['', 'I', 'II', 'III', 'IV']; // grupo en número romano para la credencial
Chart.defaults.devicePixelRatio = Math.max(window.devicePixelRatio || 1, 2);
function limiteDe(a) {
 const h = db.horarios[claveGrupo(a.grado, a.grupo, a.turno)];
 if (h && h.entrada && h.salida) return {retardo: toMin(h.entrada), fin: toMin(h.salida)};
 return LIM[a.turno];
}
function estatusDe(a, r, fecha) {
 if (!r || !r.entrada) return 'Sin registro';
 const l = limiteDe(a);
 if (!r.salida && fecha === hoy() && toMin(horaAhora()) > l.fin) return 'Permanencia';
 return toMin(r.entrada) > l.retardo ? 'Retardo' : 'Asistencia';
}
function permDe(r) {
 if (!r || !r.entrada || !r.salida) return 'Pendiente';
 const d = toMin(r.salida) - toMin(r.entrada), h = Math.floor(d/60);
 return (h > 0 ? h+' h ' : '') + (d%60) + ' min';
}
const claseEst = {'Asistencia':'bg-success','Retardo':'bg-warning text-dark','Permanencia':'bg-danger','Sin registro':'bg-secondary'};
function diasDePeriodo(periodo, ref) {
 ref = ref || hoy();
 const d = new Date(ref + 'T12:00:00');
 if (periodo === 'mes') {
 const anio = d.getFullYear(), mes = d.getMonth(), ultimoDia = new Date(anio, mes+1, 0).getDate();
 return [...Array(ultimoDia)].map((_, i) => fechaLocal(new Date(anio, mes, i+1)));
 }
 if (periodo === 'semana') {
 const dd = new Date(d); dd.setDate(dd.getDate() - ((dd.getDay() + 6) % 7));
 return [...Array(7)].map((_, i) => { const x = new Date(dd); x.setDate(dd.getDate() + i); return fechaLocal(x); });
 }
 return [ref];
}
function tituloPeriodo(periodo, ref) {
 ref = ref || hoy();
 const d = new Date(ref + 'T12:00:00');
 if (periodo === 'mes') return 'Mes de ' + d.toLocaleDateString('es-MX', {month:'long', year:'numeric'});
 if (periodo === 'semana') { const dias = diasDePeriodo('semana', ref); return 'Semana del ' + dias[0] + ' al ' + dias[6]; }
 return 'Hoy (' + ref + ')';
}
/* ============ ORDEN DEL NOMBRE: APELLIDO PATERNO + APELLIDO MATERNO + NOMBRE(S) ============ */
const PARTICULAS = ['de','del','la','las','los','y','e','san','santa','van','von','mc','mac','da','das','dos','di'];
/* Convierte "Nombre(s) Ap. paterno Ap. materno" en "Ap. paterno Ap. materno Nombre(s)".
 Respeta partículas de apellidos compuestos (DE LA CRUZ, DEL RIO, etc.). Es una regla: revisa los casos raros. */
function reordenarNombrePrimero(txt) {
 const p = String(txt).trim().replace(/\s+/g, ' ').split(' ');
 if (p.length < 2) return p.join(' ');
 const tomarApellido = () => {
  const ap = [p.pop()];
  while (p.length > 1 && PARTICULAS.includes(norm(p[p.length - 1]))) ap.unshift(p.pop());
  return ap;
 };
 const ap2 = tomarApellido();
 if (p.length <= 1) return ap2.concat(p).join(' '); // solo un apellido
 const ap1 = tomarApellido();
 return ap1.concat(ap2, p).join(' ');
}
/* fila = renglón del archivo; desde = columna donde empieza el nombre.
 Si vienen 3 columnas (paterno, materno, nombre) las une en ese orden;
 si viene una sola, la reordena o la deja tal cual según el selector. */
function armarNombre(fila, desde, modo) {
 const c = [0,1,2].map(i => String(fila[desde + i] == null ? '' : fila[desde + i]).trim());
 if (c[1] && c[2]) return c.join(' ').replace(/\s+/g, ' ').trim();
 const t = c[0].replace(/\s+/g, ' ');
 return modo === 'reordenar' ? reordenarNombrePrimero(t) : t;
}
/* ============ LECTURA DE ARCHIVOS (Excel / CSV / PDF) ============
 Punto único para leer listas de nombres: si es .xlsx/.xls/.csv usa SheetJS (como antes);
 si es .pdf usa pdf.js y trata cada línea de texto del PDF como una fila (una sola columna).
 callback(filas) recibe un arreglo de arreglos, igual que sheet_to_json({header:1}). */
function leerFilasDesdeArchivo(file, callback, onError) {
 if (/\.pdf$/i.test(file.name)) {
 if (typeof pdfjsLib === 'undefined') { onError && onError('No se pudo cargar el lector de PDF. Revisa tu conexión a internet.'); return; }
 const rd = new FileReader();
 rd.onload = async e => {
 try {
 const pdf = await pdfjsLib.getDocument({data: e.target.result}).promise;
 const filas = [];
 for (let p = 1; p <= pdf.numPages; p++) {
 const page = await pdf.getPage(p);
 const contenido = await page.getTextContent();
 const lineasPorY = {};
 contenido.items.forEach(it => {
 const y = Math.round(it.transform[5]);
 (lineasPorY[y] = lineasPorY[y] || []).push(it.str);
 });
 Object.keys(lineasPorY).sort((a, b) => b - a).forEach(y => {
 const texto = lineasPorY[y].join(' ').replace(/\s+/g, ' ').trim();
 if (texto) filas.push([texto]);
 });
 }
 callback(filas);
 } catch (err) { onError && onError('No se pudo leer el PDF: ' + err); }
 };
 rd.readAsArrayBuffer(file);
 } else {
 const rd = new FileReader();
 rd.onload = e => {
 const wb = XLSX.read(e.target.result, {type:'array'});
 callback(XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], {header:1}));
 };
 rd.readAsArrayBuffer(file);
 }
}
/* ============ ORIENTADORES: alta y consulta ============ */
function subirOrientadores() {
 if (!permisoPermite()) return;
 const file = $('#archivo-orientadores')[0].files[0];
 if (!file) { alert('Selecciona un archivo con los nombres de los orientadores.'); return; }
 if (!/\.(xlsx|xls|csv|pdf)$/i.test(file.name)) { alert('Usa un archivo Excel (.xlsx/.xls), .csv o .pdf.'); return; }
 leerFilasDesdeArchivo(file, rows => {
 rows = rows.filter(r => r[0] && String(r[0]).trim());
 if (rows.length && /nombre|orientador/i.test(rows[0][0])) rows.shift();
 let nuevos = 0, existentes = 0;
 rows.forEach(r => {
 const nombre = String(r[0]).trim();
 if (!nombre) return;
 if (db.orientadores.some(o => norm(o.nombre) === norm(nombre))) { existentes++; return; }
 db.orientadores.push({id: generarId(), nombre: nombre});
 nuevos++;
 });
 save();
 poblarSelectOrientadorHorario();
 renderListaOrientadores();
 $('#resumen-orientadores').text('Se agregaron ' + nuevos + ' orientador(a)(es) nuevo(s). ' + existentes + ' ya existían.');
 $('#archivo-orientadores').val('');
 }, msg => alert(msg));
}
function renderListaOrientadores() {
 if (!db.orientadores.length) { $('#lista-orientadores-actual').html('<span class="text-muted">Aún no hay orientadores dados de alta.</span>'); return; }
 $('#lista-orientadores-actual').html('<b>Orientadores dados de alta:</b> ' + db.orientadores.map(o => {
 const asignado = Object.entries(db.horarios).find(([k,v]) => v.orientadorId === o.id);
 const c = asignado ? parseClave(asignado[0]) : null;
 const etiqueta = c ? ' (Grado ' + c.grado + ', Grupo ' + c.grupo + ', ' + c.turno + ')' : ' (sin grupo)';
 return '<span class="d-inline-flex align-items-center gap-1 me-3 mb-1">' + esc(o.nombre) + etiqueta +
 ' <button type="button" data-o="' + esc(o.id) + '" onclick="eliminarOrientador(this.dataset.o)" class="btn btn-outline-danger btn-sm py-0 px-1" title="Eliminar orientador"><i class="fa-solid fa-trash"></i></button></span>';
 }).join(''));
}
/* Elimina un orientador del servidor y de este navegador. Sus grupos quedan "sin asignar". Solo Control Escolar. */
function eliminarOrientador(id) {
 if (rolActivo !== 'control') { alert('Solo el Encargado de Control Escolar puede eliminar orientadores.'); return; }
 const o = db.orientadores.find(x => x.id === id); if (!o) return;
 const grupos = Object.entries(db.horarios).filter(([k, v]) => v.orientadorId === id).map(([k]) => { const c = parseClave(k); return 'Grado ' + c.grado + ' Grupo ' + c.grupo + ' (' + c.turno + ')'; });
 if (!confirm('¿Eliminar a ' + o.nombre + '?' + (grupos.length ? '\nSe quitará de: ' + grupos.join(', ') + ' (quedará sin orientador).' : '') + '\nEsta acción no se puede deshacer.')) return;
 fetch('/api/sica/sync', {
 method: 'POST',
 headers: {'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''},
 body: JSON.stringify({accion:'eliminar_orientador', datos:{nombre:o.nombre}})
 }).then(r => {
 if (!r.ok) return Promise.reject();
 db.orientadores = db.orientadores.filter(x => x.id !== id);
 Object.values(db.horarios).forEach(h => { if (h.orientadorId === id) h.orientadorId = ''; });
 localStorage.setItem(LS, JSON.stringify(db));
 renderListaOrientadores(); poblarSelectOrientadorHorario(); renderTabla();
 }).catch(() => alert('No se pudo eliminar en el servidor. Revisa tu conexión e inténtalo de nuevo.'));
}
/* Grupos que le corresponden a un orientador, según lo que asignó Control Escolar */
function calcularGruposOrientador(orientadorId) {
 return Object.entries(db.horarios)
 .filter(([k, v]) => v.orientadorId === orientadorId)
 .map(([k]) => parseClave(k));
}
/* Orientadores ya asignados a OTRO grupo/turno distinto al que se está editando (para bloquearlos) */
function orientadoresBloqueadosParaGrupo(claveActual) {
 const ocupados = {};
 Object.entries(db.horarios).forEach(([k, v]) => { if (v.orientadorId && k !== claveActual) ocupados[v.orientadorId] = k; });
 return ocupados;
}
function poblarSelectOrientadorHorario() {
 const sel = $('#orientador-grupo-select');
 const claveAct = filtro ? claveGrupo(filtro.grado, filtro.grupo, turnoActivo) : '';
 const actualId = filtro ? (db.horarios[claveAct] || {}).orientadorId : '';
 const ocupados = filtro ? orientadoresBloqueadosParaGrupo(claveAct) : {};
 let html = '<option value="">Sin asignar</option>';
 db.orientadores.forEach(o => {
 const bloqueado = ocupados[o.id] && o.id !== actualId;
 const c = bloqueado ? parseClave(ocupados[o.id]) : null;
 html += '<option value="' + esc(o.id) + '" ' + (bloqueado ? 'disabled' : '') + '>' + esc(o.nombre) +
 (bloqueado ? ' (ya asignado: Grado ' + c.grado + ' Grupo ' + c.grupo + ', ' + c.turno + ')' : '') + '</option>';
 });
 sel.html(html);
 sel.val(actualId || '');
}
/* ============ NAVEGACIÓN / ROLES ============ */
function actualizarCredenciales() {
 const rol = $('#rol-select').val();
 $('#input-correo').val(correosRoles[rol] || '');
 const esOrientador = rol === 'orientador';
 $('#campo-orientador-login').toggleClass('d-none', !esOrientador);
 if (esOrientador) {
 const sel = $('#select-orientador-login');
 sel.html('<option value="" selected disabled>Selecciona tu nombre...</option>' +
 db.orientadores.map(o => '<option value="' + esc(o.id) + '">' + esc(o.nombre) + '</option>').join(''));
 $('#aviso-sin-orientadores').toggleClass('d-none', db.orientadores.length > 0);
 }
}
/* Muestra u oculta lo que se escribe en la contraseña (útil en teléfono y en equipos nuevos) */
function verOcultarClave() {
 const campo = document.getElementById('input-clave');
 const boton = document.getElementById('btn-ver-clave');
 const mostrar = campo.type === 'password';
 campo.type = mostrar ? 'text' : 'password';
 document.getElementById('ojo-abierto').classList.toggle('d-none', mostrar);
 document.getElementById('ojo-cerrado').classList.toggle('d-none', !mostrar);
 const txt = mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña';
 boton.setAttribute('aria-label', txt);
 boton.setAttribute('title', txt);
 boton.setAttribute('aria-pressed', mostrar ? 'true' : 'false');
 campo.focus();
}
function intentarEntrar() {
 const rol = $('#rol-select').val();
 const clave = $('#input-clave').val();
 if (!clave) { alert('Escribe tu contraseña.'); return; }
 if (rol === 'orientador') {
 const id = $('#select-orientador-login').val();
 if (!id) { alert('Selecciona tu nombre de la lista para continuar.'); return; }
 orientadorActivo = db.orientadores.find(o => o.id === id) || null;
 if (!orientadorActivo) { alert('No se encontró ese orientador. Intenta de nuevo.'); return; }
 } else {
 orientadorActivo = null;
 }
 fetch('/auth/login', {
 method: 'POST',
 headers: {'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''},
 body: JSON.stringify({email: $('#input-correo').val(), password: clave})
 })
 .then(r => r.json().then(d => ({status: r.status, ...d})))
 .then(d => {
 if (!d.ok) {
 orientadorActivo = null;
 alert(d.mensaje || 'No se pudo iniciar sesión.');
 return;
 }
 if (d.csrf) $('meta[name="csrf-token"]').attr('content', d.csrf);
 // El rol real lo dice el SERVIDOR. Si no coincide con el elegido (alguien tocó la página con F12), se cierra la sesión.
 if (d.rol !== rol) {
 orientadorActivo = null;
 fetch('/auth/logout', {method:'POST', headers:{'Accept':'application/json', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''}})
 .then(r => r.json()).then(x => { if (x && x.csrf) $('meta[name="csrf-token"]').attr('content', x.csrf); }).catch(() => {});
 alert('Esa cuenta no corresponde al rol seleccionado.');
 return;
 }
 if (orientadorActivo) sessionStorage.setItem('sica_orientador', orientadorActivo.id);   // para recordar quién eres si recargas la página
 $('#input-clave').val('').attr('type','password');
 $('#ojo-abierto').removeClass('d-none'); $('#ojo-cerrado').addClass('d-none');
 cargarDesdeServidor().then(() => {
 if (orientadorActivo) gruposDelOrientador = calcularGruposOrientador(orientadorActivo.id);
 mostrarSeccion('vista-sistema');
 });
 })
 .catch(() => {
 orientadorActivo = null;
 alert('No se pudo conectar con el servidor. Revisa tu conexión e inténtalo de nuevo.');
 });
}
function mostrarSeccion(id) {
 $('#vista-inicio,#vista-login,#vista-sistema').addClass('d-none');
 $('#cuerpo-contenedor').toggleClass('bg-escuela', id !== 'vista-inicio').toggleClass('bg-login', id === 'vista-inicio');
 $('#' + id).removeClass('d-none');
 if (id === 'vista-login') actualizarCredenciales();
 if (id === 'vista-sistema') {
 const rol = rolActivo = $('#rol-select').val();
 if (!plantillasPedidas && !(ES_MOVIL && rol === 'orientador')) { plantillasPedidas = true; cargarPlantillasDefault(); }   // las 4 imágenes pesadas solo se bajan al entrar
 $('#badge-rol-activo').text('Rol: ' + nombresRoles[rol] + (orientadorActivo ? ' — ' + orientadorActivo.nombre : ''));
 indicePanelActual = 0;
 if (rol === 'orientador') {
 gruposDelOrientador = calcularGruposOrientador(orientadorActivo.id);
 $('#selector-pestanas-superior').hide();
 $('#btn-regresar-panel').addClass('d-none');
 seleccionarPerfil('orientador');
 } else {
 $('#selector-pestanas-superior').show();
 $('#btn-regresar-panel').removeClass('d-none');
 const esDirSub = (rol === 'director' || rol === 'subdirector');
 $('#tab-lector').closest('.col-tab-pestana').toggleClass('d-none', esDirSub);
 $('#fila-pestanas .col-tab-pestana').not($('#tab-lector').closest('.col-tab-pestana'))
 .removeClass('col-md-3').addClass(esDirSub ? 'col-md-4' : 'col-md-3');
 if (!esDirSub) $('#fila-pestanas .col-tab-pestana').removeClass('col-md-4').addClass('col-md-3');
 seleccionarPerfil(rol === 'control' ? 'control' : 'orientador');
 }
 // En teléfono, el orientador solo ve la cámara de escaneo
 document.body.classList.toggle('modo-movil', ES_MOVIL && rol === 'orientador');
 aplicarPermisos(rol);
 // En teléfono, la cámara (trasera) se enciende sola
 if (ES_MOVIL && rol === 'orientador') setTimeout(activarCamaraOrientador, 400);
 }
}
function cerrarSesion() {
 Object.keys(scanners).forEach(detener);
 sessionStorage.removeItem('sica_orientador');
 orientadorActivo = null;
 rolActivo = null;
 document.body.classList.remove('modo-movil');
 fetch('/auth/logout', {
 method: 'POST',
 headers: {'Accept':'application/json', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''}
 }).then(r => r.json())
 .then(d => { if (d && d.csrf) $('meta[name="csrf-token"]').attr('content', d.csrf); })
 .catch(() => {})
 .then(() => cargarOrientadoresLogin());
 mostrarSeccion('vista-inicio');
}
function seleccionarPerfil(perfil) {
 Object.keys(scanners).forEach(detener);
 gradoSeleccionado = null;
 $('.profile-card').removeClass('active'); $('.panel-perfil').addClass('d-none');
 $('#tab-' + perfil).addClass('active'); $('#panel-' + perfil).removeClass('d-none');
 if (perfil === 'orientador') { configurarPanelOrientador(); renderTabla(); }
 if (perfil === 'control') renderStats();
 if (perfil === 'alumno') refrescarPreviewPlantillaOficial(); // vista previa de la plantilla oficial (solo la ve Control Escolar)
 const i = ordenPaneles.indexOf(perfil); if (i !== -1) indicePanelActual = i;
}
function regresarPanel() {
 do {
 indicePanelActual = (indicePanelActual - 1 + ordenPaneles.length) % ordenPaneles.length;
 } while (ordenPaneles[indicePanelActual] === 'lector' && (rolActivo === 'director' || rolActivo === 'subdirector'));
 seleccionarPerfil(ordenPaneles[indicePanelActual]);
}
/* ============ PERMISOS ============ */
function permisoPermite() {
 if (rolActivo === 'control') return true;
 if ((db.permisos[rolActivo] || 'ver') !== 'editar') { alert('No tienes permiso para realizar esta acción. Se requiere autorización del Encargado de Control Escolar.'); return false; }
 return true;
}
function aplicarPermisos(rol) {
 const ok = rol === 'control' || db.permisos[rol] === 'editar';
 $('.btn-accion-restringida').toggleClass('bloqueado-permiso', !ok).attr('title', ok ? null : 'Requiere autorización de Control Escolar');
 $('#aviso-camara-bloqueada').toggleClass('d-none', ok);
 $('#bloque-permisos-roles').toggleClass('d-none', rol !== 'control');
 $('#bloque-plantilla-oficial').toggleClass('d-none', rol !== 'control'); // solo Control Escolar ve el bloque
 configurarPanelOrientador();
}
function guardarPermisoRol() {
 const rol = $('#select-rol-permiso').val(), tarea = $('#select-tarea-permiso').val();
 db.permisos[rol] = tarea; save();
 $('#resumen-permisos').text('Permiso actualizado: ' + nombresRoles[rol] + ' → ' + (tarea === 'editar' ? 'Puede editar / realizar acciones (incluye cámara)' : 'Solo visualizar'));
 sincronizarConServidor('guardar_permisos', db.permisos);
}
/* ============ ESCÁNER QR (cámara) ============ */
function iniciarScanner(el, modoFn) {
 if (scanners[el]) return;
 if (typeof Html5Qrcode === 'undefined') { alert('No se cargó la librería del escáner. Revisa tu conexión a internet.'); return; }
 const s = new Html5Qrcode(el); scanners[el] = s;
 $('#' + el).siblings('.cam-ph').hide();
 // Teléfono: cámara trasera ('environment'). Computadora: la que haya ('user').
 const cfg = {fps:10, qrbox: w => { const m = Math.min(w.width, w.height); return {width:Math.floor(m*0.7), height:Math.floor(m*0.7)}; }};
 const alLeer = txt => procesarQR(txt.trim().toUpperCase(), modoFn(), el);
 const fallo = e => {
 delete scanners[el]; $('#' + el).siblings('.cam-ph').show();
 alert('No se pudo abrir la cámara (requiere permiso y https o localhost): ' + e);
 };
 const probar = (intentos, i) => s.start(intentos[i], cfg, alLeer).catch(e => (i + 1 < intentos.length) ? probar(intentos, i + 1) : fallo(e));
 if (!ES_MOVIL) { probar([{facingMode:'user'}], 0); return; } // Computadora: cámara frontal
 // Teléfono/tablet: SIEMPRE trasera. Se busca en la lista real de cámaras la que se llame back/rear/trasera...
 const reTrasera = /back|rear|trasera|posterior|traseira|environment|arri[eè]re/i, reExtra = /ultra|wide|tele|macro|depth|front|frontal|delantera/i;
 Html5Qrcode.getCameras().then(devs => {
 const traseras = devs.filter(d => reTrasera.test(d.label || ''));
 const elegida = traseras.find(d => !reExtra.test(d.label)) || traseras[0] || (devs.length > 1 ? devs[devs.length - 1] : null);
 return elegida ? [elegida.id] : [];
 }).catch(() => []).then(ids => {
 const intentos = ids.concat([{facingMode:{exact:'environment'}}, {facingMode:'environment'}]); // nunca se cae a la frontal
 return probar(intentos, 0);
 });
}
/* CAMBIO: el orientador elige si la cámara registra Entrada o Salida. Funciona con cualquier alumno,
 sin importar si es de su grupo (el registro busca por CURP en toda la base). */
let modoOrient = 'Entrada';
function setModoOrient(m) {
 modoOrient = m;
 $('#btn-orient-entrada').toggleClass('btn-success', m === 'Entrada').toggleClass('btn-outline-dark bg-white', m !== 'Entrada');
 $('#btn-orient-salida').toggleClass('btn-vinotinto', m === 'Salida').toggleClass('btn-outline-dark bg-white', m !== 'Salida');
}
function activarCamaraOrientador() { if (!permisoPermite()) return; iniciarScanner('cam-orient', () => modoOrient); }
function detener(el) {
 const s = scanners[el]; if (!s) return; delete scanners[el];
 s.stop().then(() => s.clear()).catch(() => {}); $('#' + el).siblings('.cam-ph').show();
}
function procesarQR(curp, modo, el) {
 if (ultimo.t === curp && Date.now() - ultimo.ts < 4000) return;
 ultimo = {t:curp, ts:Date.now()};
 const r = registrarPorCurp(curp, modo);
 $('#res-' + el).removeClass('text-success text-danger').addClass(r.ok ? 'text-success' : 'text-danger').text(r.msg);
 if (navigator.vibrate) navigator.vibrate(r.ok ? 150 : [100,60,100]);
}
function aplicarRegistro(a, modo) {
 if (!a) return {ok:false, msg:'Alumno no encontrado'};
 const f = hoy(), r = a.reg[f] = a.reg[f] || {};
 if (modo === 'Entrada') {
 if (r.entrada) return {ok:false, msg:a.nombre + ': la entrada ya estaba registrada (' + r.entrada + ')'};
 r.entrada = horaAhora();
 } else {
 if (!r.entrada) return {ok:false, msg:a.nombre + ': no tiene entrada registrada hoy'};
 if (r.salida) return {ok:false, msg:a.nombre + ': la salida ya estaba registrada (' + r.salida + ')'};
 r.salida = horaAhora();
 }
 // Cada escaneo se envía solo al servidor para que aparezca en la computadora del orientador
 sincronizarConServidor('registrar_asistencia', {curp: a.curp, modo: modo, fecha: f, hora: horaAhora()});
 save(); renderTabla();
 return {ok:true, msg:'✔ ' + modo + ' registrada: ' + a.nombre + ' (' + horaAhora() + ')'};
}
function registrarPorCurp(curp, modo) {
 const a = db.alumnos.find(x => x.curp && x.curp === curp);
 if (!a) return {ok:false, msg:'QR no reconocido: ' + curp};
 return aplicarRegistro(a, modo);
}
function registrarPorId(id, modo) { const a = db.alumnos.find(x => x.id === id); return aplicarRegistro(a, modo); }
/* ============ LECTOR FIJO ============ */
function setModoEscaneo(m) {
 modoEscaneo = m;
 $('#btn-modo-entrada').toggleClass('btn-success', m === 'Entrada').toggleClass('btn-outline-dark bg-white', m !== 'Entrada');
 $('#btn-modo-salida').toggleClass('btn-vinotinto', m === 'Salida').toggleClass('btn-outline-dark bg-white', m !== 'Salida');
 $('#lbl-modo').text(m);
}
function toggleCamaraFija() { scanners['cam-fija'] ? detener('cam-fija') : iniciarScanner('cam-fija', () => modoEscaneo); }
/* ============ PANEL DE ORIENTADORES ============ */
function pintarBotonesTurno() {
 const t = turnoActivo;
 $('#btn-turno-matutino').toggleClass('btn-vinotinto', t === 'Matutino').toggleClass('btn-outline-dark bg-white', t !== 'Matutino');
 $('#btn-turno-vespertino').toggleClass('btn-vinotinto', t === 'Vespertino').toggleClass('btn-outline-dark bg-white', t !== 'Vespertino');
 $('#titulo-tabla-orientador').text('Turno ' + t);
}
/* Rellena horario, orientador y etiqueta del grupo según el grupo y el turno activos */
function refrescarHorarioForm() {
 if (!filtro) return;
 $('#lbl-grupo-lista').val(filtro.grado + '° grado - Grupo ' + filtro.grupo + ' (' + turnoActivo + ')');
 const h = db.horarios[claveGrupo(filtro.grado, filtro.grupo, turnoActivo)];
 $('#horario-entrada-limite').val(h ? h.entrada : '');
 $('#horario-salida-limite').val(h ? h.salida : '');
 poblarSelectOrientadorHorario();
}
function setTurno(t) {
 turnoActivo = t;
 pintarBotonesTurno();
 refrescarHorarioForm();
 renderTabla();
}
function configurarPanelOrientador() {
 const c = rolActivo === 'control';
 const o = rolActivo === 'orientador';
 $('#bloque-camara-orientador').toggleClass('d-none', c);
 $('#bloque-orientadores-control').toggleClass('d-none', !c);
 $('#bloque-listas-orientador').toggleClass('d-none', !c);
 $('#bloque-horario-control').toggleClass('d-none', !c);
 $('#bloque-visualizar-grupo').toggleClass('d-none', o);
 $('#bloque-mi-grupo-orientador').toggleClass('d-none', !o);
 $('#bloque-turno-panel').toggleClass('d-none', o); // el orientador ve solo el turno de su grupo
 if (c) { renderListaOrientadores(); poblarSelectOrientadorHorario(); }
 if (o) configurarMiGrupoOrientador();
}
function configurarMiGrupoOrientador() {
 const tieneGrupos = gruposDelOrientador.length > 0;
 $('#sin-grupo-orientador').toggleClass('d-none', tieneGrupos);
 $('#con-grupo-orientador').toggleClass('d-none', !tieneGrupos);
 if (!tieneGrupos) { filtro = null; return; }
 $('#sel-mi-grupo').html(gruposDelOrientador.map(g =>
 '<option value="' + claveGrupo(g.grado, g.grupo, g.turno) + '">' + nomGrado[g.grado] + ' grado - Grupo ' + g.grupo + ' (' + g.turno + ')</option>').join(''));
 const primero = claveGrupo(gruposDelOrientador[0].grado, gruposDelOrientador[0].grupo, gruposDelOrientador[0].turno);
 $('#sel-mi-grupo').val(primero);
 elegirMiGrupo(primero);
}
function elegirMiGrupo(valor) {
 const c = parseClave(valor);
 filtro = {grado:c.grado, grupo:c.grupo};
 turnoActivo = c.turno;
 pintarBotonesTurno();
 renderTabla();
}
function elegirGrupo(grado, grupo) {
 [1,2,3].filter(g => g !== grado).forEach(g => $('#sel-grado-' + g).val(''));
 filtro = {grado:grado, grupo:Number(grupo)};
 refrescarHorarioForm();
 renderTabla();
}
function filtrados() {
 return db.alumnos.filter(a => a.turno === turnoActivo && (!filtro || (a.grado === filtro.grado && a.grupo === filtro.grupo)));
}
function renderTabla() {
 const f = $('#calendario-orientador').val() || hoy(), q = norm($('#buscador-alumno').val());
 const rows = filtrados().filter(a => norm(a.nombre).includes(q)).map(a => {
 const r = a.reg[f] || {}, e = estatusDe(a, r, f), dis = (!r.entrada || r.salida || f !== hoy()) ? 'disabled' : '';
 return `<tr><td>${a.curp ? '<code>'+esc(a.curp)+'</code>' : '<span class="badge bg-secondary">Sin CURP</span>'}</td><td>${esc(a.nombre)}</td><td>${a.grado}</td><td>${a.grupo}</td><td>${r.entrada || '—'}</td><td>${r.salida || 'Pendiente'}</td><td>${permDe(r)}</td><td><span class="badge ${claseEst[e]}">${e}</span></td><td class="text-center"><button data-c="${esc(a.id)}" onclick="marcarSalida(this.dataset.c)" class="btn btn-outline-dark btn-sm btn-accion-restringida" ${dis}><i class="fa-solid fa-door-open me-1"></i> Marcar Salida</button></td></tr>`;
 }).join('') || '<tr><td colspan="9" class="text-center text-muted py-3">Sin alumnos. Control Escolar debe subir la lista de este grupo.</td></tr>';
 $('#tabla-cuerpo-orientador').html(rows);
 if (rolActivo) aplicarPermisosSolo();
 renderStats();
}
function aplicarPermisosSolo() {
 const ok = rolActivo === 'control' || db.permisos[rolActivo] === 'editar';
 $('.btn-accion-restringida').toggleClass('bloqueado-permiso', !ok);
 $('#aviso-camara-bloqueada').toggleClass('d-none', ok);
}
function marcarSalida(id) { if (!permisoPermite()) return; const r = registrarPorId(id, 'Salida'); if (!r.ok) alert(r.msg); }
function subirLista() {
 if (!permisoPermite()) return;
 if (!filtro) { alert('Primero selecciona el grado y el grupo arriba (Visualizar Grupo).'); return; }
 const file = $('#archivo-lista')[0].files[0];
 if (!file) { alert('Selecciona un archivo para la lista.'); return; }
 if (!/\.(xlsx|xls|csv|pdf)$/i.test(file.name)) { alert('Para cargar los alumnos automáticamente usa un archivo Excel (.xlsx/.xls), .csv o .pdf.'); return; }
 const modo = $('#orden-nombre-lista').val();
 leerFilasDesdeArchivo(file, rows => {
 rows = rows.filter(r => r[0] && String(r[0]).trim());
 if (rows.length && /nombre|apellido/i.test(rows[0][0])) rows.shift();
 let nuevos = 0, actualizados = 0;
 rows.forEach(r => {
 const nombre = armarNombre(r, 0, modo);
 if (!nombre) return;
 let a = db.alumnos.find(x => x.grado === filtro.grado && x.grupo === filtro.grupo && x.turno === turnoActivo && norm(x.nombre) === norm(nombre));
 if (a) { actualizados++; }
 else { db.alumnos.push({id:generarId(), curp:'', nombre:nombre, grado:filtro.grado, grupo:filtro.grupo, turno:turnoActivo, reg:{}}); nuevos++; }
 });
 save(); renderTabla();
 alert('Lista cargada en ' + filtro.grado + '° grado, grupo ' + filtro.grupo + ' (' + turnoActivo + '): ' + nuevos + ' alumno(s) nuevo(s), ' + actualizados + ' actualizado(s).\nRecuerda asignar su CURP y credencial desde el Portal del Alumno.');
 $('#archivo-lista').val('');
 }, msg => alert(msg));
}
function subirListaCredencial() {
 if (!permisoPermite()) return;
 if (!credSel) return;
 const file = $('#input-subir-lista-credencial')[0].files[0];
 if (!file) { alert('Selecciona un archivo con CURP y nombre.'); return; }
 if (!/\.(xlsx|xls|csv)$/i.test(file.name)) { alert('Para cargar los alumnos automáticamente usa un archivo Excel (.xlsx/.xls) o .csv.'); return; }
 const modo = $('#orden-nombre-cred').val();
 const rd = new FileReader();
 rd.onload = e => {
 const wb = XLSX.read(e.target.result, {type:'array'});
 const rows = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], {header:1}).filter(r => r[0] && r[1]);
 if (rows.length && /curp|matr/i.test(rows[0][0])) rows.shift();
 let asignados = 0, creados = 0;
 rows.forEach(r => {
 const curp = String(r[0]).trim().toUpperCase(), nombre = armarNombre(r, 1, modo);
 if (!curp || !nombre) return;
 let a = db.alumnos.find(x => x.curp === curp);
 if (!a) a = db.alumnos.find(x => x.grado === credSel.grado && x.grupo === credSel.grupo && x.turno === turnoCred && !x.curp && norm(x.nombre) === norm(nombre));
 if (a) { a.curp = curp; a.nombre = nombre; a.grado = credSel.grado; a.grupo = credSel.grupo; a.turno = turnoCred; asignados++; }
 else { db.alumnos.push({id:generarId(), curp:curp, nombre:nombre, grado:credSel.grado, grupo:credSel.grupo, turno:turnoCred, reg:{}}); creados++; }
 });
 save(); selCred(credSel.grado, credSel.grupo);
 alert('CURP asignado a ' + asignados + ' alumno(s) y ' + creados + ' registrado(s) nuevo(s) en ' + nomGrado[credSel.grado] + ' ' + credSel.grupo + ' (' + turnoCred + ').');
 $('#input-subir-lista-credencial').val('');
 };
 rd.readAsArrayBuffer(file);
}
function guardarHorarioGrupo() {
 if (!permisoPermite()) return;
 if (!filtro) { alert('Primero selecciona el grado y el grupo arriba (Visualizar Grupo).'); return; }
 const e = $('#horario-entrada-limite').val(), s = $('#horario-salida-limite').val(), oid = $('#orientador-grupo-select').val();
 if (!e || !s) { alert('Indica la hora límite de entrada y la hora de cierre.'); return; }
 db.horarios[claveGrupo(filtro.grado, filtro.grupo, turnoActivo)] = {entrada:e, salida:s, orientadorId: oid || ''};
 save(); renderTabla(); poblarSelectOrientadorHorario(); renderListaOrientadores();
 const nombreOrientador = oid ? (db.orientadores.find(o => o.id === oid) || {}).nombre : 'Sin asignar';
 $('#resumen-horario-grupo').text('Datos guardados para ' + filtro.grado + '° grado, grupo ' + filtro.grupo + ' (' + turnoActivo + '): entrada límite ' + e + ', cierre ' + s + ', orientador(a): ' + nombreOrientador + '.');
}
function subirHorarios() {
 if (!permisoPermite()) return;
 const file = $('#archivo-horarios')[0].files[0];
 if (!file) { alert('Selecciona un archivo de horarios.'); return; }
 const rd = new FileReader();
 rd.onload = e => {
 const wb = XLSX.read(e.target.result, {type:'array'});
 const rows = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], {header:1}).filter(r => r[0] !== undefined && r[1] !== undefined && r[2] && r[3]);
 if (rows.length && /grado/i.test(rows[0][0])) rows.shift();
 rows.forEach(r => {
 const grado = Number(r[0]), grupo = Number(r[1]), entrada = String(r[2]).trim(), salida = String(r[3]).trim(), nombreOrientador = r[4] ? String(r[4]).trim() : '';
 const turno = r[5] ? (/vesp/i.test(String(r[5])) ? 'Vespertino' : 'Matutino') : turnoActivo; // columna 6 (opcional)
 if (!grado || !grupo) return;
 let orientadorId = '';
 if (nombreOrientador) {
 let o = db.orientadores.find(x => norm(x.nombre) === norm(nombreOrientador));
 if (!o) { o = {id: generarId(), nombre: nombreOrientador}; db.orientadores.push(o); }
 orientadorId = o.id;
 }
 db.horarios[claveGrupo(grado, grupo, turno)] = {entrada:entrada, salida:salida, orientadorId: orientadorId};
 });
 save(); renderTabla(); poblarSelectOrientadorHorario(); renderListaOrientadores();
 refrescarHorarioForm();
 alert('Horarios cargados para ' + rows.length + ' grupo(s).');
 $('#archivo-horarios').val('');
 };
 rd.readAsArrayBuffer(file);
}
/* Descargas (Excel / PDF reales) */
function descargar(nombre, filas, formato, titulo) {
 if (!filas.length) { alert('No hay datos para descargar.'); return; }
 if (formato === 'Excel') {
 const wb = XLSX.utils.book_new(); XLSX.utils.book_append_sheet(wb, XLSX.utils.json_to_sheet(filas), 'Reporte'); XLSX.writeFile(wb, nombre + '.xlsx');
 } else {
 const d = new window.jspdf.jsPDF({orientation:'landscape'}), k = Object.keys(filas[0]);
 d.setFontSize(13); d.text(titulo, 14, 14);
 d.autoTable({head:[k], body:filas.map(o => k.map(x => o[x])), startY:20, styles:{fontSize:8}, headStyles:{fillColor:[128,0,32]}});
 d.save(nombre + '.pdf');
 }
}
function descargarReporte(formato) {
 if (!permisoPermite()) return;
 const periodo = $('#sel-periodo-reporte').val() || 'semana';
 const ref = $('#calendario-orientador').val() || hoy();
 const dias = diasDePeriodo(periodo, ref);
 const tp = tituloPeriodo(periodo, ref);
 const nombreArchivo = 'asistencia_' + (periodo === 'mes' ? ref.slice(0,7) : dias[0]);
 const filas = [];
 filtrados().forEach(a => dias.forEach(f => { const r = a.reg[f]; if (r && r.entrada) filas.push({Fecha:f, CURP:a.curp, Alumno:a.nombre, Grado:a.grado, Grupo:a.grupo, Turno:a.turno, Entrada:r.entrada, Salida:r.salida || 'Pendiente', Permanencia:permDe(r), Estatus:estatusDe(a, r, f)}); }));
 descargar(nombreArchivo, filas, formato, 'Reporte de asistencia - Turno ' + turnoActivo + ' - ' + tp);
}
/* ============ CONTROL ESCOLAR: ESTADÍSTICAS Y GRÁFICA ============ */
function statsGrados(periodo) {
 periodo = periodo || 'hoy';
 const dias = diasDePeriodo(periodo);
 return [1,2,3].map(g => {
 const l = db.alumnos.filter(a => a.grado === g);
 let as = 0, re = 0, pe = 0;
 l.forEach(a => dias.forEach(f => { const e = estatusDe(a, a.reg[f], f); if (e === 'Asistencia') as++; else if (e === 'Retardo') re++; else if (e === 'Permanencia') pe++; }));
 const base = l.length * dias.length;
 const p = n => base ? Math.round(n*100/base) : 0;
 return {Grado:g + '° grado', gradoNum:g, Alumnos:l.length, Asistencias:as, '% Asistencias':p(as), Retardos:re, '% Retardos':p(re), Permanencias:pe};
 });
}
/* Comparativa por grupo de un grado. Incluye los grupos de ambos turnos
 (los del vespertino solo aparecen si ya tienen alumnos). */
function statsPorGrupo(grado, periodo) {
 periodo = periodo || 'hoy';
 const dias = diasDePeriodo(periodo), res = [];
 ['Matutino','Vespertino'].forEach(turno => [1,2,3,4].forEach(grupo => {
 const l = db.alumnos.filter(a => a.grado === grado && a.grupo === grupo && a.turno === turno);
 if (!l.length && turno === 'Vespertino') return;
 let as = 0, re = 0, pe = 0;
 l.forEach(a => dias.forEach(f => { const e = estatusDe(a, a.reg[f], f); if (e === 'Asistencia') as++; else if (e === 'Retardo') re++; else if (e === 'Permanencia') pe++; }));
 const h = db.horarios[claveGrupo(grado, grupo, turno)] || {};
 const orientadorNombre = h.orientadorId ? ((db.orientadores.find(o => o.id === h.orientadorId) || {}).nombre || 'Sin asignar') : 'Sin asignar';
 res.push({turno:turno, grupo:grupo, asistencias:as, retardos:re, permanencias:pe, alumnos:l.length, orientador: orientadorNombre});
 }));
 return res;
}
function renderStats() {
 const periodo = $('#sel-periodo-stats').val() || 'hoy';
 $('#subtitulo-periodo-stats').text('Estadísticas de asistencia - ' + tituloPeriodo(periodo));
 const s = statsGrados(periodo), sum = k => s.reduce((t, x) => t + x[k], 0), tot = sum('Alumnos');
 $('#st-total').text(tot);
 $('#st-asis').text(sum('Asistencias') + ' (' + (tot ? Math.round(sum('Asistencias')*100/tot) : 0) + '%)');
 $('#st-ret').text((sum('Retardos') + sum('Permanencias')) + ' (' + (tot ? Math.round((sum('Retardos') + sum('Permanencias'))*100/tot) : 0) + '%)');
 $('#stats-grados').html(s.map(x => `<div class="col-md-4"><button id="btn-grado-${x.gradoNum}" onclick="seleccionarGrado(${x.gradoNum})" class="btn tarjeta-grado p-3 border rounded-3 bg-light w-100 text-center" title="Selecciona este grado y usa Excel o PDF (arriba) para descargar su comparativa">
 <span class="fw-bold small d-block text-secondary">${x.Grado}</span>
 <span class="text-success fw-bold d-block">Asistencias: ${x['% Asistencias']}% (${x.Asistencias} de ${x.Alumnos})</span>
 <span class="text-warning fw-bold d-block">Retardos: ${x['% Retardos']}% (${x.Retardos})</span>
 <span class="text-danger fw-bold d-block">Permanencia: ${x.Permanencias} alumnos</span>
 </button></div>`).join(''));
 actualizarBotonesGrado();
 dibujarGraficaAsistencias(s);
}
function seleccionarGrado(grado) { gradoSeleccionado = (gradoSeleccionado === grado) ? null : grado; actualizarBotonesGrado(); }
function actualizarBotonesGrado() {
 [1,2,3].forEach(g => {
 const sel = gradoSeleccionado === g;
 const desactivado = gradoSeleccionado !== null && !sel;
 $('#btn-grado-' + g).toggleClass('seleccionada', sel).toggleClass('grado-desactivado', desactivado);
 });
 $('#lbl-descarga-seleccion').text(gradoSeleccionado ? ('Comparativa de ' + nomGrado[gradoSeleccionado] + ' grado (por grupo y turno, con orientador)') : 'Estadísticas generales (todos los grados)');
}
function dibujarGraficaAsistencias(s) {
 const ctx = document.getElementById('grafica-asistencias');
 if (!ctx || typeof Chart === 'undefined') return;
 const data = { labels: s.map(x => x.Grado), datasets: [
 {label:'Asistencias', data: s.map(x => x.Asistencias), backgroundColor:'#198754'},
 {label:'Retardos', data: s.map(x => x.Retardos), backgroundColor:'#ffc107'},
 {label:'Permanencias', data: s.map(x => x.Permanencias), backgroundColor:'#dc3545'}
 ]};
 const titulo = 'Estadísticas de asistencia - ' + tituloPeriodo($('#sel-periodo-stats').val() || 'hoy');
 if (chartAsistencias) { chartAsistencias.data = data; chartAsistencias.options.plugins.title.text = titulo; chartAsistencias.update(); return; }
 chartAsistencias = new Chart(ctx, { type: 'bar', data: data, options: {
 responsive:true, maintainAspectRatio:true,
 plugins:{ legend:{position:'bottom'}, title:{display:true, text:titulo, font:{size:13, weight:'bold'}, color:'#800020', padding:{bottom:10}} },
 scales:{y:{beginAtZero:true, ticks:{precision:0}}}
 }});
}
function descargarGraficas(formato) {
 if (!permisoPermite()) return;
 if (gradoSeleccionado) { if (formato === 'PDF') descargarComparativaGradoPDF(gradoSeleccionado); else descargarComparativaGradoExcel(gradoSeleccionado); return; }
 const periodo = $('#sel-periodo-stats').val() || 'hoy';
 const s = statsGrados(periodo);
 const tp = tituloPeriodo(periodo);
 if (formato === 'Excel') { descargar('estadisticas_' + hoy(), s.map(x => ({Grado:x.Grado, Alumnos:x.Alumnos, Asistencias:x.Asistencias, '% Asistencias':x['% Asistencias'], Retardos:x.Retardos, '% Retardos':x['% Retardos'], Permanencias:x.Permanencias})), 'Excel', 'Estadísticas de asistencia - ' + tp); return; }
 const d = new window.jspdf.jsPDF();
 d.setFontSize(14); d.text('Estadísticas de asistencia - ' + tp, 14, 15);
 let startY = 22;
 const canvas = document.getElementById('grafica-asistencias');
 if (canvas && chartAsistencias) {
 const img = canvas.toDataURL('image/png', 1.0);
 const ratio = canvas.height / canvas.width, imgW = 180, imgH = Math.round(imgW * ratio);
 d.addImage(img, 'PNG', 14, 22, imgW, imgH);
 startY = 22 + imgH + 8;
 }
 const k = ['Grado','Alumnos','Asistencias','% Asistencias','Retardos','% Retardos','Permanencias'];
 d.autoTable({head:[k], body: s.map(o => k.map(x => o[x])), startY: startY, styles:{fontSize:8}, headStyles:{fillColor:[128,0,32]}});
 d.save('estadisticas_' + hoy() + '.pdf');
}
function descargarComparativaGradoPDF(grado) {
 const periodo = $('#sel-periodo-stats').val() || 'hoy';
 const datos = statsPorGrupo(grado, periodo);
 const tp = tituloPeriodo(periodo);
 const d = new window.jspdf.jsPDF();
 d.setFontSize(14); d.text('Comparativa de asistencia - ' + nomGrado[grado] + ' grado - ' + tp, 14, 15);
 let y = 24, contadorEnPagina = 0;
 const cont = document.getElementById('canvas-temporal');
 datos.forEach((g) => {
 if (contadorEnPagina === 2) { d.addPage(); y = 15; contadorEnPagina = 0; }
 const canvas = document.createElement('canvas'); canvas.width = 700; canvas.height = 380; cont.appendChild(canvas);
 const tempChart = new Chart(canvas.getContext('2d'), { type:'bar',
 data:{ labels:['Asistencias','Retardos','Permanencias'], datasets:[{ data:[g.asistencias,g.retardos,g.permanencias], backgroundColor:['#198754','#ffc107','#dc3545'] }] },
 options:{ responsive:false, animation:false, devicePixelRatio:2,
 plugins:{ legend:{display:false}, title:{display:true, text:nomGrado[grado]+' '+g.grupo+' ('+g.turno+') — Orientador(a): '+g.orientador, font:{size:15, weight:'bold'}, color:'#800020'} },
 scales:{ y:{beginAtZero:true, ticks:{precision:0}} } }});
 tempChart.update();
 const img = canvas.toDataURL('image/png', 1.0);
 const imgW = 180, imgH = Math.round(imgW * (canvas.height/canvas.width));
 d.addImage(img, 'PNG', 14, y, imgW, imgH);
 y += imgH + 8; contadorEnPagina++;
 tempChart.destroy(); cont.removeChild(canvas);
 });
 d.save('comparativa_' + nomGrado[grado] + '_grado_' + hoy() + '.pdf');
}
function descargarComparativaGradoExcel(grado) {
 const periodo = $('#sel-periodo-stats').val() || 'hoy';
 const tp = tituloPeriodo(periodo);
 const datos = statsPorGrupo(grado, periodo);
 const filas = datos.map(g => ({Grupo: nomGrado[grado] + ' ' + g.grupo, Turno: g.turno, 'Orientador(a)': g.orientador, Alumnos: g.alumnos, Asistencias: g.asistencias, Retardos: g.retardos, Permanencias: g.permanencias}));
 descargar('comparativa_' + nomGrado[grado] + '_grado_' + hoy(), filas, 'Excel', 'Comparativa - ' + nomGrado[grado] + ' grado - ' + tp);
}
/* ============ PLANTILLAS OFICIALES POR TURNO (frente y reverso) ============
 Cada turno tiene su propia plantilla (cambia el C.C.T.). Se cargan una sola vez desde /public/img
 al abrir el sistema y se guardan en memoria como dataURL, para usarse en la vista previa y en el PDF.
 El PRIMER nombre de cada lista es el que guarda el servidor al subir una plantilla nueva
 (plantillamatutinofrente.png, plantillavespertinoreverso.png, etc.); los siguientes son los nombres
 originales de tus archivos, que se usan mientras no se haya subido una plantilla nueva.
 Se agrega ?v=Date.now() para que el navegador no use una imagen vieja en caché. Si Control Escolar
 subió una plantilla nueva en este navegador (plantillaOficial[turno]), esa tiene prioridad. */
const ARCHIVOS_PLANTILLA = {
 Matutino: {
  frente:  ['plantillamatutinofrente.png', 'plantillamatutinofrente.jpg'],
  reverso: ['plantillamatutinoreverso.png', 'trantillamatutinoreverso.png']
 },
 Vespertino: {
  frente:  ['plantillavespertinofrente.png', 'plantillavespertinofrente.jpg'],
  reverso: ['plantillavespertinoreverso.png', 'trantillavespertinoreverso.png']
 }
};
const BASE_IMG = "{{ asset('img') }}/";
let plantillasPedidas = false;
function cargarPlantillasDefault() {
 Object.keys(ARCHIVOS_PLANTILLA).forEach(turno => {
  ['frente','reverso'].forEach(lado => {
   const lista = ARCHIVOS_PLANTILLA[turno][lado];
   const intentar = i => {
    if (i >= lista.length) { console.warn('No se encontró la plantilla ' + turno + ' ' + lado + ' en public/img'); return; }
    const img = new Image();
    img.onload = () => {
     const c = document.createElement('canvas'); c.width = img.naturalWidth; c.height = img.naturalHeight;
     c.getContext('2d').drawImage(img, 0, 0);
     const propia = plantillaOficial[turno] && plantillaOficial[turno][lado];
     plantillasDefault[turno][lado] = (propia && propia.img) || c.toDataURL('image/png');
     refrescarPreviewPlantillaOficial();
     if (idEditando) actualizarVistaPreviaCredencial();
    };
    img.onerror = () => intentar(i + 1);
    img.src = BASE_IMG + lista[i] + '?v=' + Date.now();
   };
   intentar(0);
  });
 });
}
/* Devuelve {img,fmt} de la plantilla del turno y lado indicados (o null si aún no cargó). */
function plantillaDe(turno, lado) {
 const t = plantillasDefault[turno || 'Matutino'];
 return (t && t[lado]) ? {img: t[lado], fmt:'PNG'} : null;
}
/* ============ PLANTILLA OFICIAL EDITABLE (solo Control Escolar) ============
 Reemplaza la plantilla oficial de frente/reverso del turno elegido para todos sus grupos.
 - En el navegador: se usa de inmediato (vista previa y PDF).
 - En el servidor: se envía a /api/sica/plantilla-oficial/{lado} con el campo "turno"
 (ControlEscolarController@plantillaOficial), que sobrescribe public/img/plantilla{turno}{lado}.png. */
const LS_PL = 'sica_epo6_plantilla_oficial'; // clave aparte para no inflar el localStorage principal
let plantillaOficial = {}; // {Matutino:{frente:{img},reverso:{img}}, Vespertino:{...}}
try { plantillaOficial = JSON.parse(localStorage.getItem(LS_PL) || '{}'); } catch (e) { plantillaOficial = {}; }
const RATIO_PLANTILLA = {frente: 797/541, reverso: 930/575};

/* CAMBIO: muestra la plantilla del turno elegido. Si esa plantilla aún no está cargada,
 oculta la imagen para que no se quede la del otro turno. */
function refrescarPreviewPlantillaOficial() {
 const t = $('#sel-turno-plantilla').val() || 'Matutino';
 ['frente','reverso'].forEach(l => {
  const src = (plantillasDefault[t] && plantillasDefault[t][l]) || '';
  if (src) $('#prev-plantilla-oficial-' + l).attr('src', src).show();
  else $('#prev-plantilla-oficial-' + l).removeAttr('src').hide();
 });
}
function subirPlantillaOficial(lado) {
 if (rolActivo !== 'control') { alert('Solo el Encargado de Control Escolar puede cambiar la plantilla oficial.'); return; }
 const turno = $('#sel-turno-plantilla').val() || 'Matutino';
 const f = $('#input-plantilla-oficial-' + lado)[0].files[0];
 if (!f) { alert('Selecciona la imagen de la nueva plantilla.'); return; }
 if (!/^image\/(png|jpe?g)$/.test(f.type)) { alert('Solo se aceptan imágenes .png o .jpg.'); return; }
 const url = URL.createObjectURL(f), img = new Image();
 img.onload = () => {
 URL.revokeObjectURL(url);
 const ratio = img.naturalWidth / img.naturalHeight, esperado = RATIO_PLANTILLA[lado];
 if (Math.abs(ratio - esperado) / esperado > 0.02) {
 alert('La imagen mide ' + img.naturalWidth + '×' + img.naturalHeight + ' y no tiene la proporción de la credencial (' +
 esperado.toFixed(3) + ':1). Si se acepta, nombre, CURP y QR quedarían desalineados.');
 return;
 }
 // Siempre se normaliza a PNG
 const c = document.createElement('canvas'); c.width = img.naturalWidth; c.height = img.naturalHeight;
 c.getContext('2d').drawImage(img, 0, 0);
 const dataURL = c.toDataURL('image/png');
 plantillaOficial[turno] = plantillaOficial[turno] || {};
 plantillaOficial[turno][lado] = {img: dataURL, fmt: 'PNG'};
 let aviso = '';
 try { localStorage.setItem(LS_PL, JSON.stringify(plantillaOficial)); }
 catch (e) { aviso = ' (la imagen es muy pesada para guardarla en este navegador; solo quedará en el servidor)'; }
 plantillasDefault[turno][lado] = dataURL; // se usa de inmediato en vista previa y PDF
 refrescarPreviewPlantillaOficial();
 if (idEditando) actualizarVistaPreviaCredencial();
 // Reemplazo real del archivo en el servidor
 c.toBlob(blob => {
 const fd = new FormData();
 fd.append('archivo', blob, 'plantilla' + turno.toLowerCase() + lado + '.png');
 fd.append('turno', turno);
 fetch('/api/sica/plantilla-oficial/' + lado, {
 method: 'POST',
 headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''},
 body: fd
 }).then(r => r.ok ? r.json() : Promise.reject())
 .then(() => $('#resumen-plantilla-oficial').removeClass('text-danger').addClass('text-success')
 .text('¡Plantilla ' + turno + ' (' + lado + ') actualizada con éxito en el servidor! Las nuevas credenciales de ese turno ya se generarán con este diseño.'))
 .catch(() => $('#resumen-plantilla-oficial').removeClass('text-success').addClass('text-danger')
 .text('Plantilla ' + turno + ' (' + lado + ') actualizada solo en este navegador; el servidor no respondió.' + aviso));
 }, 'image/png');
 $('#input-plantilla-oficial-' + lado).val('');
 };
 img.onerror = () => alert('No se pudo leer la imagen.');
 img.src = url;
}
function restaurarPlantillaOficial() {
 if (rolActivo !== 'control') return;
 const t = $('#sel-turno-plantilla').val() || 'Matutino';
 if (!confirm('¿Volver a la plantilla original del turno ' + t + '?')) return;
 delete plantillaOficial[t];
 try { localStorage.setItem(LS_PL, JSON.stringify(plantillaOficial)); } catch (e) {}
 plantillasDefault[t] = {frente:null, reverso:null};
 cargarPlantillasDefault(); // vuelve a leer de /public/img
 setTimeout(refrescarPreviewPlantillaOficial, 800);
 $('#resumen-plantilla-oficial').removeClass('text-danger').addClass('text-success').text('Se restauró la plantilla original del turno ' + t + ' en este navegador.');
}
/* ============ PORTAL DEL ALUMNO / CREDENCIALES ============ */
const qrImg = curp => new QRious({value:curp, size:220}).toDataURL();
const alumnosCred = () => db.alumnos.filter(a => a.grado === credSel.grado && a.grupo === credSel.grupo && a.turno === turnoCred);
/* CAMBIO: al elegir Matutino/Vespertino abajo, el selector "Turno de la plantilla" y la
 vista previa (frente y reverso) de arriba cambian al mismo turno. */
function setTurnoCred(t) {
 turnoCred = t;
 $('#btn-turno-cred-matutino').toggleClass('btn-vinotinto', t === 'Matutino').toggleClass('btn-outline-dark bg-white', t !== 'Matutino');
 $('#btn-turno-cred-vespertino').toggleClass('btn-vinotinto', t === 'Vespertino').toggleClass('btn-outline-dark bg-white', t !== 'Vespertino');
 $('#sel-turno-plantilla').val(t);      // sincroniza el selector de la plantilla de arriba
 refrescarPreviewPlantillaOficial();    // cambia la vista previa de frente y reverso
 if (credSel) selCred(credSel.grado, credSel.grupo);
}
function selCred(grado, grupo) {
 credSel = {grado:grado, grupo:grupo};
 $('#titulo-grupo-credencial').text('Credenciales Digitales - ' + nomGrado[grado] + ' ' + grupo + ' (' + turnoCred + ')');
 $('#badge-plantilla').text('Plantilla ' + turnoCred);
 $('#vista-editar-credencial-individual').addClass('d-none'); $('#resultado-credencial-box').removeClass('d-none');
 $('#tabla-alumnos-credencial').html(alumnosCred().map(a => `<tr><td>${a.curp ? '<code>'+esc(a.curp)+'</code>' : '<span class="badge bg-secondary">Sin CURP</span>'}</td><td>${esc(a.nombre)}</td><td class="text-center"><button data-c="${esc(a.id)}" onclick="verCredencial(this.dataset.c)" class="btn btn-vinotinto btn-sm py-1 px-3"><i class="fa-solid fa-id-card me-1"></i> Ver Credencial</button> <button data-c="${esc(a.id)}" onclick="eliminarAlumno(this.dataset.c)" class="btn btn-outline-danger btn-sm py-1 px-2" title="Eliminar alumno"><i class="fa-solid fa-trash"></i></button></td></tr>`).join('') || '<tr><td colspan="3" class="text-center text-muted py-3">Este grupo aún no tiene alumnos en este turno. Control Escolar debe subir la lista.</td></tr>');
}
function verCredencial(id) {
 const a = db.alumnos.find(x => x.id === id); idEditando = id;
 $('#edit-curp-alumno').val(a.curp); $('#edit-nombre-alumno').val(a.nombre);
 $('#resultado-credencial-box').addClass('d-none'); $('#vista-editar-credencial-individual').removeClass('d-none');
 actualizarVistaPreviaCredencial();
}
function regresarListaGrupo() { selCred(credSel.grado, credSel.grupo); }
/* Elimina un alumno (y sus asistencias) del servidor y de este navegador. Solo Control Escolar. */
function eliminarAlumno(id) {
 if (rolActivo !== 'control') { alert('Solo el Encargado de Control Escolar puede eliminar alumnos.'); return; }
 const a = db.alumnos.find(x => x.id === id); if (!a) return;
 if (!confirm('¿Eliminar a ' + a.nombre + (a.curp ? ' (' + a.curp + ')' : '') + '? Se borrarán también sus registros de asistencia. Esta acción no se puede deshacer.')) return;
 const quitarLocal = () => {
 db.alumnos = db.alumnos.filter(x => x.id !== id);
 localStorage.setItem(LS, JSON.stringify(db));
 selCred(credSel.grado, credSel.grupo); renderTabla();
 };
 if (!a.curp) { quitarLocal(); return; } // nunca se guardó en el servidor
 fetch('/api/sica/sync', {
 method: 'POST',
 headers: {'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''},
 body: JSON.stringify({accion:'eliminar_alumno', datos:{curp:a.curp}})
 }).then(r => r.ok ? quitarLocal() : Promise.reject())
 .catch(() => alert('No se pudo eliminar en el servidor. Revisa tu conexión e inténtalo de nuevo.'));
}
function guardarCambiosCredencial() {
 if (!permisoPermite()) return;
 const a = db.alumnos.find(x => x.id === idEditando), curp = $('#edit-curp-alumno').val().trim().toUpperCase(), nombre = $('#edit-nombre-alumno').val().trim();
 if (!curp || !nombre) { alert('CURP y nombre son obligatorios.'); return; }
 a.curp = curp; a.nombre = nombre; save();
 actualizarVistaPreviaCredencial();
 alert('Cambios guardados en la credencial.');
}
/* Dibuja la vista previa en vivo (canvas) de frente y reverso mientras se edita al alumno,
 usando siempre los datos que hay en los inputs (aunque no se hayan guardado todavía). */
function actualizarVistaPreviaCredencial() {
 const a = db.alumnos.find(x => x.id === idEditando);
 if (!a) return;
 const datosPreview = {
 nombre: $('#edit-nombre-alumno').val().trim() || a.nombre,
 curp: $('#edit-curp-alumno').val().trim().toUpperCase() || a.curp,
 grado: a.grado, grupo: a.grupo, turno: a.turno
 };
 pintarCredencialCanvas(document.getElementById('preview-canvas-frente'), datosPreview, 'frente');
 pintarCredencialCanvas(document.getElementById('preview-canvas-reverso'), datosPreview, 'reverso');
}
/* Dibuja una credencial (frente o reverso) en un <canvas>. Es la misma lógica que se usa para
 generar las imágenes que después se insertan en el PDF, así la vista previa es idéntica al resultado final. */
// Coordenadas medidas sobre la plantilla oficial (tamaño original 797x541). La plantilla vespertina
// tiene la misma proporción y distribución, así que usa las mismas coordenadas.
// Se escalan solas (factor k) sin importar si el canvas es la vista previa chica o el de alta
// resolución para el PDF, siempre que mantengan esta misma proporción (797:541).
// CURP, grado, grupo y turno van A LA DERECHA de su etiqueta y el texto se encoge solo (maxW)
// para no encimarse con la siguiente etiqueta (p. ej. "GRUPO:").
// Si algún dato queda desalineado, ajusta solo su x (derecha +) o y (abajo +).
const REF_FRENTE_W = 797;
const CAMPOS_FRENTE = {
 nombre: {x: 38,  y: 294, maxW: 370, fontMax: 16},  // alineado con el borde izquierdo de "NOMBRE DEL ESTUDIANTE"
 curp:   {x: 134, y: 331, font: 14, maxW: 275},   // CURP, GRADO y TURNO comparten la misma columna (x=134)
 grado:  {x: 134, y: 382, font: 11, maxW: 52},    // termina antes de la etiqueta "GRUPO:"
 grupo:  {x: 282, y: 382, font: 14, maxW: 40},    // justo después de la etiqueta "GRUPO:"
 turno:  {x: 134, y: 437, font: 14, maxW: 250},
 qr:     {x: 423, y: 261, lado: 122}
};
function pintarCredencialCanvas(canvas, a, lado, onListo) {
 if (!canvas) return;
 const ctx = canvas.getContext('2d'), w = canvas.width, h = canvas.height;
 ctx.clearRect(0, 0, w, h);
 const pl = plantillaDe(a.turno, lado); // plantilla del turno del alumno
 const terminar = () => { if (onListo) onListo(); };
 const dibujarContenido = () => {
 if (lado !== 'frente') { terminar(); return; } // el reverso ya viene completo en tu plantilla
 const k = w / REF_FRENTE_W; // factor de escala: plantilla real -> este canvas
 ctx.fillStyle = '#000';
 if (!pl) { // respaldo mínimo si no hay plantilla cargada
 ctx.fillStyle = '#800020'; ctx.fillRect(0, 0, w, h * 0.14);
 ctx.fillStyle = '#fff'; ctx.font = 'bold ' + Math.round(h * 0.06) + 'px Segoe UI';
 ctx.fillText('EPO 6 - Credencial Digital', w * 0.03, h * 0.09);
 ctx.fillStyle = '#000';
 }
 // Nombre: negritas, MAYÚSCULAS, se encoge solo si no cabe
 const c = CAMPOS_FRENTE.nombre, nombreTxt = (a.nombre || '(SIN NOMBRE)').toUpperCase();
 let tam = c.fontMax * k;
 ctx.font = 'bold ' + Math.round(tam) + 'px Segoe UI';
 while (ctx.measureText(nombreTxt).width > c.maxW * k && tam > 9 * k) {
 tam -= k * 0.5; ctx.font = 'bold ' + Math.round(tam) + 'px Segoe UI';
 }
 ctx.fillText(nombreTxt, c.x * k, c.y * k);
 // CURP / Grado / Grupo / Turno: negritas, MAYÚSCULAS, se encogen solos si no caben
 ['curp','grado','grupo','turno'].forEach(campo => {
 const d = CAMPOS_FRENTE[campo];
 const valor = String(campo === 'curp'  ? (a.curp || '(SIN CURP)')
 : campo === 'grado' ? (nomGrado[a.grado] || '')
 : campo === 'grupo' ? (ROMANOS[a.grupo] || String(a.grupo || ''))
 : (a.turno || '')).toUpperCase();
 let t = d.font * k;
 ctx.font = 'bold ' + Math.round(t) + 'px Segoe UI';
 while (ctx.measureText(valor).width > d.maxW * k && t > 7 * k) {
 t -= k * 0.5; ctx.font = 'bold ' + Math.round(t) + 'px Segoe UI';
 }
 ctx.fillText(valor, d.x * k, d.y * k);
 });
 // QR único por alumno (se genera con su propia CURP)
 if (a.curp) {
 const qr = new Image(), ladoQR = CAMPOS_FRENTE.qr.lado * k;
 qr.onload = () => { ctx.drawImage(qr, CAMPOS_FRENTE.qr.x * k, CAMPOS_FRENTE.qr.y * k, ladoQR, ladoQR); terminar(); };
 qr.onerror = terminar;
 qr.src = qrImg(a.curp);
 } else { terminar(); }
 };
 if (pl && pl.img) {
 const tpl = new Image();
 tpl.onload = () => { ctx.drawImage(tpl, 0, 0, w, h); dibujarContenido(); };
 tpl.onerror = dibujarContenido;
 tpl.src = pl.img;
 } else {
 ctx.fillStyle = '#f4f6f9'; ctx.fillRect(0, 0, w, h);
 dibujarContenido();
 }
}
/* Genera, para insertar en un PDF, las imágenes (dataURL) de frente y reverso de un alumno,
 usando un canvas oculto de alta resolución (para que se vea nítido al imprimir). */
function generarImagenesCredencialPDF(a, cb) {
 const cFrente = document.createElement('canvas'); cFrente.width = 950; cFrente.height = 645;
 const cReverso = document.createElement('canvas'); cReverso.width = 950; cReverso.height = 587;
 let listos = 0;
 const revisar = () => { if (++listos === 2) cb(cFrente.toDataURL('image/png'), cReverso.toDataURL('image/png')); };
 pintarCredencialCanvas(cFrente, a, 'frente', revisar);
 pintarCredencialCanvas(cReverso, a, 'reverso', revisar);
}
function descargarCredencialesPDF() {
 if (!permisoPermite()) return;
 const l = alumnosCred().filter(a => a.curp);
 const sinCurp = alumnosCred().length - l.length;
 if (!l.length) { alert('No hay alumnos con CURP asignado en este grupo. Sube primero la lista con CURP y nombre completo.'); return; }
 const d = new window.jspdf.jsPDF();
 // Tamaño de cada credencial en mm, respetando la proporción real de tus plantillas
 // (797x541 el frente, 930x575 el reverso) para que no se vean estiradas.
 const w = 90, hFrente = 61, hReverso = 56;
 const margen = 10, colGap = 8, filaGap = 8, entreFrenteReverso = 3;
 const altoPar = hFrente + entreFrenteReverso + hReverso;
 const porHoja = 4; // 2 columnas x 2 filas
 const procesarSiguiente = () => {
 if (i >= l.length) { d.save('credenciales_' + nomGrado[credSel.grado] + '_' + credSel.grupo + '_' + turnoCred + '.pdf'); if (sinCurp > 0) alert('Se descargaron ' + l.length + ' credenciales (frente y reverso). ' + sinCurp + ' alumno(s) no tienen CURP asignado todavía y no se incluyeron.'); return; }
 const a = l[i];
 generarImagenesCredencialPDF(a, (imgFrente, imgReverso) => {
 const posEnHoja = i % porHoja;
 if (i > 0 && posEnHoja === 0) d.addPage();
 const col = posEnHoja % 2, fila = Math.floor(posEnHoja / 2);
 const x = margen + col * (w + colGap);
 const y = margen + fila * (altoPar + filaGap);
 d.addImage(imgFrente, 'PNG', x, y, w, hFrente);
 d.addImage(imgReverso, 'PNG', x, y + hFrente + entreFrenteReverso, w, hReverso);
 i++; procesarSiguiente();
 });
 };
 let i = 0;
 procesarSiguiente();
}
function descargarCredencialIndividual() {
 if (!permisoPermite()) return;
 const a = db.alumnos.find(x => x.id === idEditando);
 if (!a.curp) { alert('Este alumno aún no tiene CURP asignado. Sube su CURP desde "Subir lista de alumnos" antes de descargar su credencial.'); return; }
 generarImagenesCredencialPDF(a, (imgFrente, imgReverso) => {
 const d = new window.jspdf.jsPDF();
 d.addImage(imgFrente, 'PNG', 10, 10, 95, 60);
 d.addImage(imgReverso, 'PNG', 10, 74, 95, 60);
 d.save('credencial_' + a.curp + '.pdf');
 });
}
/* Si al abrir la página ya hay sesión en el servidor, entra directo al sistema (sin volver a pedir contraseña al recargar) */
function restaurarSesion(S) {
 $('#rol-select').val(S.rol);
 if (S.rol === 'orientador') {
 const id = sessionStorage.getItem('sica_orientador');
 orientadorActivo = db.orientadores.find(o => o.id === id) || null;
 if (!orientadorActivo) { mostrarSeccion('vista-login'); return; }   // sesión abierta pero sin nombre elegido: que lo elija
 }
 cargarDesdeServidor().then(() => {
 if (orientadorActivo) gruposDelOrientador = calcularGruposOrientador(orientadorActivo.id);
 if (!rolActivo) mostrarSeccion('vista-sistema');
 });
}
$(function () {
 if (Array.isArray(window.SICA_ORIENTADORES)) db.orientadores = window.SICA_ORIENTADORES;   // ya vienen en la página
 actualizarCredenciales();
 $('#calendario-orientador').val(hoy());
 if (window.SICA_SESION) restaurarSesion(window.SICA_SESION);   // solo pide datos al servidor si hay sesión (cero errores 401 al abrir)
 // Las computadoras ven los escaneos del teléfono en ~5 s; se pausa si la pestaña está oculta para no gastar datos ni servidor
 if (!ES_MOVIL) setInterval(() => { if (rolActivo && !document.hidden) cargarDesdeServidor(); }, 5000);
 document.addEventListener('visibilitychange', () => { if (!document.hidden && rolActivo) cargarDesdeServidor(); });
});
</script>
</body>
</html>