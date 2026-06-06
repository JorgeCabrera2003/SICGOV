<!-- ==========================================================================
     MÓDULO DE PERFIL DE USUARIO - GOOD VIBES (HTML5 SEMÁNTICO)
     ========================================================================== -->

<main class="container-fluid py-4">
    <!-- Header de Perfil -->
    <section class="profile-header-container p-4 pb-0">
        <!-- Información Básica & Avatar Overlap -->
        <div class="d-flex align-items-center flex-column flex-md-row gap-4 mb-4">
            <!-- Foto de Perfil (Avatar) -->
            <div class="position-relative" style="width: 140px; height: 140px;">
                <img src="<?= $datos['foto'] ?>" alt="Foto de perfil" id="imgAvatar" class="rounded-circle shadow-sm" style="width: 100%; height: 100%; object-fit: cover; border: 4px solid var(--color-border);">
                <div class="position-absolute d-flex justify-content-center align-items-center rounded-circle" id="btnEditarAvatar" role="button" aria-label="Cambiar foto de perfil" style="top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); color: white; opacity: 0; transition: opacity 0.3s; cursor: pointer;">
                    <i class="bi bi-camera-fill fs-3"></i>
                </div>
                <input type="file" id="inputAvatar" class="d-none" accept="image/*">
            </div>

            <!-- Nombres, Apellidos y Rol -->
            <div class="profile-user-details text-center text-md-start">
                <h1 class="profile-name text-body mb-2" style="font-size: 2rem; font-weight: 800;">
                    <?= htmlspecialchars(($perfil['nombre'] ?? $perfil['nombres'] ?? 'Usuario') . ' ' . ($perfil['apellido'] ?? $perfil['apellidos'] ?? '')) ?>
                </h1>
                <div class="profile-username justify-content-center justify-content-md-start">
                    <span>@<?= htmlspecialchars($perfil['username'] ?? 'usuario') ?></span>
                    <span class="profile-role-badge bg-primary text-dark"><?= htmlspecialchars($perfil['rol'] ?? 'Personal') ?></span>
                </div>
            </div>
        </div>
        
        <style>
            #btnEditarAvatar:hover { opacity: 1 !important; }
        </style>

        <!-- Navegación de Pestañas (Tabs) -->
        <div class="profile-tabs-wrapper">
            <nav class="nav profile-tabs" id="perfilTabs" role="tablist">
                <button class="nav-link profile-tab-btn active" id="tab-info" data-bs-toggle="tab" data-bs-target="#content-info" type="button" role="tab" aria-controls="content-info" aria-selected="true">
                    <i class="bi bi-person-lines-fill me-2"></i>Información
                </button>
                <button class="nav-link profile-tab-btn" id="tab-editar" data-bs-toggle="tab" data-bs-target="#content-editar" type="button" role="tab" aria-controls="content-editar" aria-selected="false">
                    <i class="bi bi-pencil-square me-2"></i>Editar Perfil
                </button>
                <button class="nav-link profile-tab-btn" id="tab-seguridad" data-bs-toggle="tab" data-bs-target="#content-seguridad" type="button" role="tab" aria-controls="content-seguridad" aria-selected="false">
                    <i class="bi bi-person-fill-gear me-2"></i>Usuario
                </button>
            </nav>
        </div>
    </section>

    <!-- Contenido de las Pestañas -->
    <section class="tab-content" id="perfilTabsContent">
        <!-- PESTAÑA: INFORMACIÓN GENERAL -->
        <div class="tab-pane fade show active" id="content-info" role="tabpanel" aria-labelledby="tab-info">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4">
                        <h2 class="h5 fw-bold mb-4">
                            <i class="bi bi-info-circle-fill text-primary me-2"></i>Información General de la Cuenta
                        </h2>
                        
                        <div class="profile-info-grid">
                            <!-- Cédula -->
                            <article class="info-item">
                                <div class="info-item-icon">
                                    <i class="bi bi-card-text"></i>
                                </div>
                                <div class="info-item-details">
                                    <span class="info-item-label">Cédula de Identidad</span>
                                    <span class="info-item-value"><?= htmlspecialchars($perfil['cedula'] ?? 'N/D') ?></span>
                                </div>
                            </article>

                            <!-- Nombre Completo -->
                            <article class="info-item">
                                <div class="info-item-icon">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                                <div class="info-item-details">
                                    <span class="info-item-label">Nombre Completo</span>
                                    <span class="info-item-value">
                                        <?= htmlspecialchars(($perfil['nombre'] ?? $perfil['nombres'] ?? 'N/D') . ' ' . ($perfil['apellido'] ?? $perfil['apellidos'] ?? '')) ?>
                                    </span>
                                </div>
                            </article>

                            <!-- Correo -->
                            <article class="info-item">
                                <div class="info-item-icon">
                                    <i class="bi bi-envelope-fill"></i>
                                </div>
                                <div class="info-item-details">
                                    <span class="info-item-label">Correo Electrónico</span>
                                    <span class="info-item-value"><?= htmlspecialchars($perfil['correo'] ?? 'Sin correo') ?></span>
                                </div>
                            </article>

                            <!-- Teléfono -->
                            <article class="info-item">
                                <div class="info-item-icon">
                                    <i class="bi bi-telephone-fill"></i>
                                </div>
                                <div class="info-item-details">
                                    <span class="info-item-label">Teléfono Celular</span>
                                    <span class="info-item-value"><?= htmlspecialchars($perfil['telefono'] ?? 'Sin teléfono') ?></span>
                                </div>
                            </article>

                            <!-- Sexo / Género -->
                            <article class="info-item">
                                <div class="info-item-icon">
                                    <i class="bi bi-gender-ambiguous"></i>
                                </div>
                                <div class="info-item-details">
                                    <span class="info-item-label">Género</span>
                                    <span class="info-item-value">
                                        <?php 
                                            $s = $perfil['sexo'] ?? '';
                                            if ($s === 'M') echo 'Masculino';
                                            elseif ($s === 'F') echo 'Femenino';
                                            else echo 'No Especificado';
                                        ?>
                                    </span>
                                </div>
                            </article>

                            <!-- Fecha Nacimiento -->
                            <article class="info-item">
                                <div class="info-item-icon">
                                    <i class="bi bi-calendar-event-fill"></i>
                                </div>
                                <div class="info-item-details">
                                    <span class="info-item-label">Fecha de Nacimiento</span>
                                    <span class="info-item-value">
                                        <?php 
                                            $fn = $perfil['fecha_nacimiento'] ?? '';
                                            echo !empty($fn) ? date('d/m/Y', strtotime($fn)) : 'N/D';
                                        ?>
                                    </span>
                                </div>
                            </article>
                        </div>

                        <!-- Dirección completa span -->
                        <div class="mt-4 p-3 rounded bg-light border border-light-subtle">
                            <div class="d-flex gap-3 align-items-start">
                                <div class="info-item-icon bg-white text-primary">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </div>
                                <div>
                                    <span class="info-item-label text-muted d-block" style="font-size: 0.72rem;">Dirección de Habitación</span>
                                    <p class="mb-0 fw-semibold text-body" style="font-size: 0.98rem;">
                                        <?= htmlspecialchars($perfil['direccion'] ?? 'Sin dirección especificada') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PESTAÑA: EDITAR PERFIL -->
        <div class="tab-pane fade" id="content-editar" role="tabpanel" aria-labelledby="tab-editar">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4">
                        <h2 class="h5 fw-bold mb-4">
                            <i class="bi bi-pencil-square text-primary me-2"></i>Actualizar Información Personal
                        </h2>
                        
                        <form id="formEditarPerfil" novalidate>
                            <input type="hidden" name="peticion" value="actualizar-perfil">
                            
                            <div class="row g-3">
                                <!-- Nombres -->
                                <div class="col-md-6 position-relative">
                                    <label for="nombre" class="form-label fw-bold">Nombres <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?= htmlspecialchars($perfil['nombre'] ?? $perfil['nombres'] ?? '') ?>" 
                                           placeholder="Escriba sus nombres" required>
                                    <span id="snombre" style="width: fit-content;"></span>
                                </div>

                                <!-- Apellidos -->
                                <div class="col-md-6 position-relative">
                                    <label for="apellido" class="form-label fw-bold">Apellidos <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" 
                                           value="<?= htmlspecialchars($perfil['apellido'] ?? $perfil['apellidos'] ?? '') ?>" 
                                           placeholder="Escriba sus apellidos" required>
                                    <span id="sapellido" style="width: fit-content;"></span>
                                </div>

                                <!-- Correo Electrónico -->
                                <div class="col-md-6 position-relative">
                                    <label for="correo" class="form-label fw-bold">Correo Electrónico <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control" id="correo" name="correo" 
                                               value="<?= htmlspecialchars($perfil['correo'] ?? '') ?>" 
                                               placeholder="nombre@ejemplo.com" required>
                                    </div>
                                    <span id="scorreo" style="width: fit-content;"></span>
                                </div>

                                <!-- Teléfono Celular -->
                                <div class="col-md-6 position-relative">
                                    <label for="telefono" class="form-label fw-bold">Teléfono Celular <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select class="form-select" id="prefijo_telefono" name="prefijo_telefono" style="max-width: 90px;" required>
                                            <option value="0412" <?= (substr($perfil['telefono'] ?? '', 0, 4) === '0412') ? 'selected' : '' ?>>0412</option>
                                            <option value="0414" <?= (substr($perfil['telefono'] ?? '', 0, 4) === '0414') ? 'selected' : '' ?>>0414</option>
                                            <option value="0424" <?= (substr($perfil['telefono'] ?? '', 0, 4) === '0424') ? 'selected' : '' ?>>0424</option>
                                            <option value="0416" <?= (substr($perfil['telefono'] ?? '', 0, 4) === '0416') ? 'selected' : '' ?>>0416</option>
                                            <option value="0426" <?= (substr($perfil['telefono'] ?? '', 0, 4) === '0426') ? 'selected' : '' ?>>0426</option>
                                        </select>
                                        <input type="text" class="form-control" id="telefono" name="telefono" 
                                               value="<?= htmlspecialchars(substr($perfil['telefono'] ?? '', -7)) ?>" 
                                               placeholder="1234567" required>
                                    </div>
                                    <small class="text-muted">Seleccione el prefijo e ingrese 7 dígitos.</small>
                                    <span id="stelefono" style="width: fit-content;"></span>
                                </div>

                                <!-- Género -->
                                <div class="col-md-6 position-relative">
                                    <label for="sexo" class="form-label fw-bold">Sexo / Género <span class="text-danger">*</span></label>
                                    <select class="form-select" id="sexo" name="sexo" required>
                                        <option value="default" disabled <?= empty($perfil['sexo']) ? 'selected' : '' ?>>Seleccione...</option>
                                        <option value="M" <?= ($perfil['sexo'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                                        <option value="F" <?= ($perfil['sexo'] ?? '') === 'F' ? 'selected' : '' ?>>Femenino</option>
                                    </select>
                                    <span id="ssexo" style="width: fit-content;"></span>
                                </div>

                                <!-- Fecha de Nacimiento -->
                                <div class="col-md-6 position-relative">
                                    <label for="fecha_nacimiento" class="form-label fw-bold">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                                           value="<?= htmlspecialchars($perfil['fecha_nacimiento'] ?? '') ?>" required max="<?= date('Y-m-d', strtotime('-1 day')) ?>">
                                    <span id="sfecha_nacimiento" style="width: fit-content;"></span>
                                </div>

                                <!-- Dirección de Habitación -->
                                <div class="col-12 position-relative">
                                    <label for="direccion" class="form-label fw-bold">Dirección Completa de Habitación <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="direccion" name="direccion" rows="3" 
                                              placeholder="Estado, Municipio, Parroquia, Avenida/Calle, Casa/Apto" required><?= htmlspecialchars($perfil['direccion'] ?? '') ?></textarea>
                                    <span id="sdireccion" style="width: fit-content;"></span>
                                </div>

                                <!-- Botón Guardar -->
                                <div class="col-12 text-end mt-4">
                                    <button type="submit" class="btn btn-primary px-4 py-2" id="btnGuardarPerfil">
                                        <i class="bi bi-save me-2"></i>Guardar Cambios
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- PESTAÑA: USUARIO (Seguridad y Username) -->
        <div class="tab-pane fade" id="content-seguridad" role="tabpanel" aria-labelledby="tab-seguridad">
            <div class="row">
                <!-- Columna Izquierda: Formularios -->
                <div class="col-lg-7 mb-4 mb-lg-0">
                    
                    <!-- Formulario Nombre de Usuario -->
                    <div class="card border-0 shadow-sm p-4 mb-4">
                        <h2 class="h5 fw-bold mb-4">
                            <i class="bi bi-person-badge-fill text-primary me-2"></i>Nombre de Usuario
                        </h2>
                        <form id="formActualizarUsername" novalidate>
                            <input type="hidden" name="peticion" value="actualizar-username">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-8 position-relative">
                                    <label for="username_input" class="form-label fw-bold">Nombre de Usuario Actual</label>
                                    <div class="input-group">
                                        <span class="input-group-text">@</span>
                                        <input type="text" class="form-control" id="username_input" name="username" 
                                               value="<?= htmlspecialchars($perfil['username'] ?? '') ?>" 
                                               placeholder="Escriba su nuevo nombre de usuario" required>
                                    </div>
                                    <span id="susername_input" style="width: fit-content;"></span>
                                </div>
                                <div class="col-md-4 text-end">
                                    <button type="submit" class="btn btn-primary w-100" id="btnGuardarUsername">
                                        <i class="bi bi-save me-2"></i>Actualizar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Formulario Cambio Contraseña -->
                    <div class="card border-0 shadow-sm p-4">
                        <h2 class="h5 fw-bold mb-4">
                            <i class="bi bi-shield-lock-fill text-primary me-2"></i>Cambiar Contraseña de Acceso
                        </h2>
                        
                        <form id="formCambiarClave" novalidate>
                            <input type="hidden" name="peticion" value="cambiar-clave">
                            
                            <div class="row g-3">
                                <!-- Nueva Contraseña -->
                                <div class="col-12 position-relative">
                                    <label for="clave_nueva" class="form-label fw-bold">Nueva Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                        <input type="password" class="form-control" id="clave_nueva" name="clave_nueva" 
                                               placeholder="Mínimo 8 caracteres, 1 mayúscula, 1 número, 1 símbolo" required>
                                    </div>
                                    <span id="sclave_nueva" style="width: fit-content;"></span>
                                </div>

                                <!-- Confirmar Nueva Contraseña -->
                                <div class="col-12 position-relative">
                                    <label for="clave_confirmar" class="form-label fw-bold">Confirmar Nueva Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-check-lg"></i></span>
                                        <input type="password" class="form-control" id="clave_confirmar" name="clave_confirmar" 
                                               placeholder="Repita la nueva contraseña" required>
                                    </div>
                                    <span id="sclave_confirmar" style="width: fit-content;"></span>
                                </div>

                                <!-- Botón Cambiar -->
                                <div class="col-12 text-end mt-4">
                                    <button type="submit" class="btn btn-primary px-4 py-2" id="btnGuardarClave">
                                        <i class="bi bi-shield-check me-2"></i>Actualizar Contraseña
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Requisitos y Recomendaciones -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <h3 class="h5 fw-bold mb-3">Consejos de Seguridad</h3>
                        <p class="text-muted small">Mantener su cuenta protegida es nuestra prioridad. Por favor, tome en cuenta estas recomendaciones de seguridad informática:</p>
                        
                        <div class="password-requirements mb-4">
                            <h6 class="text-dark fw-bold"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Requisitos obligatorios:</h6>
                            <ul>
                                <li>Debe contar con al menos 8 caracteres de longitud.</li>
                                <li>Debe incluir al menos una letra mayúscula, un número y un símbolo especial.</li>
                                <li>No debe coincidir con su contraseña actual.</li>
                                <li>La confirmación de la contraseña debe ser exactamente igual.</li>
                            </ul>
                        </div>

                        <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis rounded-3 p-3">
                            <div class="d-flex gap-2">
                                <i class="bi bi-info-circle-fill fs-5"></i>
                                <div>
                                    <span class="fw-bold d-block mb-1">¿Sabía qué?</span>
                                    <span class="small">Cambiar su clave periódicamente (cada 3 a 6 meses) mitiga significativamente el riesgo de robo de identidad o accesos no autorizados.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            </div>
        </div>
    </section>
</main>

