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
    <div class="col-md-6">
        <label for="nombre" class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" required>
    </div>

    <div class="col-md-6">
        <label for="apellido" class="form-label fw-semibold">Apellido <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="apellido" name="apellido" maxlength="100" required>
    </div>

    <div class="col-md-4">
        <label for="nacionalidad" class="form-label fw-semibold">Nacionalidad <span class="text-danger">*</span></label>
        <select class="form-select" id="nacionalidad" name="nacionalidad" required>
            <option value="" selected disabled>Seleccionar</option>
            <option value="V">V</option>
            <option value="E">E</option>
            <option value="J">J</option>
            <option value="G">G</option>
        </select>
    </div>

    <div class="col-md-8">
        <label for="cedula" class="form-label fw-semibold">Cédula <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Ej: 12345678" maxlength="12" required>
    </div>

    <div class="col-md-6">
        <label for="fecha_nacimiento" class="form-label fw-semibold">Fecha de Nacimiento <span class="text-danger">*</span></label>
        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" max="<?= date('Y-m-d', strtotime('-1 day')) ?>" required>
    </div>

    <div class="col-md-6">
        <label for="sexo" class="form-label fw-semibold">Sexo <span class="text-danger">*</span></label>
        <select class="form-select" id="sexo" name="sexo" required>
            <option value="" selected disabled>Seleccionar</option>
            <option value="M">Masculino</option>
            <option value="F">Femenino</option>
        </select>
    </div>

    <div class="col-md-6">
        <label for="correo" class="form-label fw-semibold">Correo Electrónico <span class="text-danger">*</span></label>
        <input type="email" class="form-control" id="correo" name="correo" maxlength="100" required>
    </div>

    <div class="col-md-6">
        <label for="telefono" class="form-label fw-semibold">Teléfono</label>
        <input type="text" class="form-control" id="telefono" name="telefono" maxlength="20" placeholder="Ej: 04121234567">
    </div>

    <div class="col-md-12">
        <label for="direccion" class="form-label fw-semibold">Dirección <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="direccion" name="direccion" maxlength="255" required>
    </div>

    <div class="col-md-12">
        <label for="username" class="form-label fw-semibold">Nombre de Usuario <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="username" name="username" maxlength="100" required>
    </div>

    <div class="col-md-6">
        <label for="clave" class="form-label fw-semibold">Contraseña <span class="text-danger">*</span></label>
        <div class="input-group shadow-sm rounded">
            <input type="password" class="form-control" id="clave" name="clave" maxlength="100" required autocomplete="new-password">
            <button type="button" class="btn btn-outline-secondary" data-password-toggle="#clave" aria-label="Mostrar contraseña">
                <i class="fa fa-eye"></i>
            </button>
        </div>
        <div class="mt-2 small" id="password-requirements">
            <ul class="text-white-50 mb-0 ps-3">
                <li id="req-length">Al menos 8 caracteres</li>
                <li id="req-upper">Al menos una letra mayúscula</li>
                <li id="req-number">Al menos un número</li>
                <li id="req-special">Al menos un carácter especial (!@#$%^&*)</li>
            </ul>
        </div>
    </div>

    <?php if ($showConfirmPassword): ?>
        <div class="col-md-6">
            <label for="rclave" class="form-label fw-semibold">Confirmar contraseña <span class="text-danger">*</span></label>
            <div class="input-group shadow-sm rounded">
                <input type="password" class="form-control" id="rclave" name="rclave" maxlength="100" required autocomplete="new-password">
                <button type="button" class="btn btn-outline-secondary" data-password-toggle="#rclave" aria-label="Mostrar contraseña">
                    <i class="fa fa-eye"></i>
                </button>
            </div>
            <div class="mt-2 small text-danger d-none" id="match-error">
                <i class="fas fa-times-circle me-1"></i> Las contraseñas no coinciden.
            </div>
        </div>
    <?php endif; ?>
</div>
