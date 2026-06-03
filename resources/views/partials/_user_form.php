<?php
/**
 * Formulario de usuario reutilizable.
 * Puede incluirse en la página de login (registro) y en el modal de usuario.
 * Variables disponibles:
 *  - $formContext: 'auth' | 'admin'
 */
$formContext = $formContext ?? 'auth';
$showConfirmPassword = $showConfirmPassword ?? true;
?>
<div class="row g-3">
    <!-- Nombre -->
    <div class="col-md-6 position-relative">
        <label for="nombre" class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
        <div class="input-group shadow-sm rounded">
            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
            <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" placeholder="Ej: Juan" required>
        </div>
        <span id="snombre" style="width: fit-content;"></span>
    </div>

    <!-- Apellido -->
    <div class="col-md-6 position-relative">
        <label for="apellido" class="form-label fw-semibold">Apellido <span class="text-danger">*</span></label>
        <div class="input-group shadow-sm rounded">
            <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
            <input type="text" class="form-control" id="apellido" name="apellido" maxlength="100" placeholder="Ej: Perez" required>
        </div>
        <span id="sapellido" style="width: fit-content;"></span>
    </div>

    <!-- Cédula -->
    <div class="col-md-6 position-relative">
        <label class="form-label fw-semibold">Cédula <span class="text-danger">*</span></label>
        <div class="input-group shadow-sm rounded">
            <span class="input-group-text"><i class="fa-solid fa-id-card"></i></span>
            <select class="form-select" id="nacionalidad" name="nacionalidad" required style="max-width: 80px;" data-bs-theme="dark">
                <option value="" selected disabled>▼</option>
                <option value="V">V</option>
                <option value="E">E</option>
                <option value="J">J</option>
                <option value="G">G</option>
            </select>
            <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Ej: 12345678" maxlength="12" required>
        </div>
        <span id="scedula" style="width: fit-content;"></span>
    </div>

    <!-- Fecha de Nacimiento -->
    <div class="col-md-6 position-relative">
        <label for="fecha_nacimiento" class="form-label fw-semibold">Fecha de Nacimiento <span class="text-danger">*</span></label>
        <div class="input-group shadow-sm rounded">
            <span class="input-group-text"><i class="fa-solid fa-calendar-day"></i></span>
            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" max="<?= date('Y-m-d', strtotime('-1 day')) ?>" required>
        </div>
        <span id="sfecha_nacimiento" style="width: fit-content;"></span>
    </div>

    <!-- Sexo -->
    <div class="col-md-6 position-relative">
        <label for="sexo" class="form-label fw-semibold">Sexo <span class="text-danger">*</span></label>
        <div class="input-group shadow-sm rounded">
            <span class="input-group-text"><i class="fa-solid fa-venus-mars"></i></span>
            <select class="form-select" id="sexo" name="sexo" required data-bs-theme="dark">
                <option value="" selected disabled>Seleccionar</option>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
            </select>
        </div>
        <span id="ssexo" style="width: fit-content;"></span>
    </div>

    <!-- Correo Electrónico -->
    <div class="col-md-6 position-relative">
        <label for="correo" class="form-label fw-semibold">Correo Electrónico <span class="text-danger">*</span></label>
        <div class="input-group shadow-sm rounded">
            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
            <input type="email" class="form-control" id="correo" name="correo" maxlength="100" placeholder="correo@ejemplo.com" required>
        </div>
        <span id="scorreo" style="width: fit-content;"></span>
    </div>

    <!-- Teléfono -->
    <div class="col-md-6 position-relative">
        <label class="form-label fw-semibold">Teléfono <span class="text-danger">*</span></label>
        <div class="input-group shadow-sm rounded">
            <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
            <select class="form-select" id="prefijo_telefono" style="max-width: 95px;" required data-bs-theme="dark">
                <option value="" selected disabled>▼</option>
                <option value="0412">0412</option>
                <option value="0422">0422</option>
                <option value="0414">0414</option>
                <option value="0424">0424</option>
                <option value="0416">0416</option>
                <option value="0426">0426</option>
            </select>
            <input type="text" class="form-control" id="numero_telefono" maxlength="7" placeholder="Ej: 1234567" required>
        </div>
        <input type="hidden" id="telefono" name="telefono">
        <span id="stelefono" style="width: fit-content;"></span>
    </div>

    <!-- Dirección -->
    <div class="col-md-6 position-relative">
        <label for="direccion" class="form-label fw-semibold">Dirección <span class="text-danger">*</span></label>
        <div class="input-group shadow-sm rounded">
            <span class="input-group-text"><i class="fa-solid fa-map-location-dot"></i></span>
            <input type="text" class="form-control" id="direccion" name="direccion" maxlength="255" placeholder="Dirección detallada" required>
        </div>
        <span id="sdireccion" style="width: fit-content;"></span>
    </div>

    <!-- Nombre de Usuario -->
    <div class="col-md-6 position-relative">
        <label for="username" class="form-label fw-semibold">Nombre de Usuario <span class="text-danger">*</span></label>
        <div class="input-group shadow-sm rounded">
            <span class="input-group-text">@</span>
            <input type="text" class="form-control" id="username" name="username" maxlength="100" placeholder="usuario123" required>
        </div>
        <span id="susername" style="width: fit-content;"></span>
    </div>

    <!-- Contraseña -->
    <div class="col-md-6 position-relative">
        <label for="clave" class="form-label fw-semibold">Contraseña <span class="text-danger">*</span></label>
        <div class="input-group shadow-sm rounded">
            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
            <input type="password" class="form-control" id="clave" name="clave" maxlength="100" required autocomplete="new-password" placeholder="Mínimo 8 caracteres">
            <button type="button" class="btn btn-outline-secondary" data-password-toggle="#clave" aria-label="Mostrar contraseña">
                <i class="fa fa-eye"></i>
            </button>
        </div>
        <span id="sclave" style="width: fit-content;"></span>
    </div>

    <?php if ($showConfirmPassword): ?>
        <!-- Confirmar Contraseña -->
        <div class="col-md-6 position-relative">
            <label for="rclave" class="form-label fw-semibold">Confirmar contraseña <span class="text-danger">*</span></label>
            <div class="input-group shadow-sm rounded">
                <span class="input-group-text"><i class="fa-solid fa-check-double"></i></span>
                <input type="password" class="form-control" id="rclave" name="rclave" maxlength="100" required autocomplete="new-password" placeholder="Repite tu contraseña">
                <button type="button" class="btn btn-outline-secondary" data-password-toggle="#rclave" aria-label="Mostrar contraseña">
                    <i class="fa fa-eye"></i>
                </button>
            </div>
            <span id="srclave" style="width: fit-content;"></span>
        </div>
    <?php endif; ?>
</div>
