<!-- ==========================================================================
     MÓDULO DE PERFIL DE USUARIO - GOOD VIBES (HTML5 SEMÁNTICO)
     ========================================================================== -->

<main class="container-fluid py-4">
    <!-- Header de Perfil Estilo Facebook -->
    <section class="profile-header-container">
        <!-- Foto de Portada (Banner) -->
        <div class="profile-cover">
            <?php if (!empty($portada)): ?>
                <img src="<?= $portada ?>" alt="Foto de portada" class="profile-cover-img" id="imgPortada">
            <?php else: ?>
                <div class="profile-cover-img" id="imgPortada" style="background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%); width: 100%; height: 100%;"></div>
            <?php endif; ?>
            
            <div class="profile-cover-overlay"></div>
            
            <!-- Botón para Editar Portada -->
            <button class="btn btn-edit-cover fw-bold" id="btnEditarPortada" aria-label="Cambiar foto de portada">
                <i class="bi bi-camera-fill me-2"></i>Editar foto de portada
            </button>
            <input type="file" id="inputPortada" class="d-none" accept="image/*">
        </div>

        <!-- Información Básica & Avatar Overlap -->
        <div class="position-relative">
            <!-- Foto de Perfil (Avatar) -->
            <div class="profile-avatar-container">
                <img src="<?= $datos['foto'] ?>" alt="Foto de perfil" class="profile-avatar" id="imgAvatar">
                <div class="avatar-edit-overlay" id="btnEditarAvatar" role="button" aria-label="Cambiar foto de perfil">
                    <i class="bi bi-camera-fill"></i>
                    <span>Actualizar</span>
                </div>
                <input type="file" id="inputAvatar" class="d-none" accept="image/*">
            </div>

            <!-- Nombres, Apellidos y Rol -->
            <div class="profile-header-info">
                <div class="profile-user-details">
                    <h1 class="profile-name text-body">
                        <?= htmlspecialchars(($perfil['nombre'] ?? $perfil['nombres'] ?? 'Usuario') . ' ' . ($perfil['apellido'] ?? $perfil['apellidos'] ?? '')) ?>
                    </h1>
                    <div class="profile-username">
                        <span>@<?= htmlspecialchars($perfil['username'] ?? 'usuario') ?></span>
                        <span class="profile-role-badge bg-primary text-dark"><?= htmlspecialchars($perfil['rol'] ?? 'Personal') ?></span>
                    </div>
                </div>
            </div>
        </div>

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
                    <i class="bi bi-shield-lock-fill me-2"></i>Seguridad
                </button>
                <button class="nav-link profile-tab-btn" id="tab-actividad" data-bs-toggle="tab" data-bs-target="#content-actividad" type="button" role="tab" aria-controls="content-actividad" aria-selected="false">
                    <i class="bi bi-clock-history me-2"></i>Mi Actividad
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
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label fw-bold">Nombres</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?= htmlspecialchars($perfil['nombre'] ?? $perfil['nombres'] ?? '') ?>" 
                                           placeholder="Escriba sus nombres" required>
                                </div>

                                <!-- Apellidos -->
                                <div class="col-md-6">
                                    <label for="apellido" class="form-label fw-bold">Apellidos</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" 
                                           value="<?= htmlspecialchars($perfil['apellido'] ?? $perfil['apellidos'] ?? '') ?>" 
                                           placeholder="Escriba sus apellidos" required>
                                </div>

                                <!-- Correo Electrónico -->
                                <div class="col-md-6">
                                    <label for="correo" class="form-label fw-bold">Correo Electrónico</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control" id="correo" name="correo" 
                                               value="<?= htmlspecialchars($perfil['correo'] ?? '') ?>" 
                                               placeholder="nombre@ejemplo.com" required>
                                    </div>
                                </div>

                                <!-- Teléfono Celular -->
                                <div class="col-md-6">
                                    <label for="telefono" class="form-label fw-bold">Teléfono Celular</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                        <input type="text" class="form-control" id="telefono" name="telefono" 
                                               value="<?= htmlspecialchars($perfil['telefono'] ?? '') ?>" 
                                               placeholder="0412-1234567" required>
                                    </div>
                                    <small class="text-muted">Formato: 4 dígitos de código + guion + 7 dígitos (ej: 0412-1234567).</small>
                                </div>

                                <!-- Género -->
                                <div class="col-md-6">
                                    <label for="sexo" class="form-label fw-bold">Sexo / Género</label>
                                    <select class="form-select" id="sexo" name="sexo" required>
                                        <option value="" disabled>Seleccione...</option>
                                        <option value="M" <?= ($perfil['sexo'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                                        <option value="F" <?= ($perfil['sexo'] ?? '') === 'F' ? 'selected' : '' ?>>Femenino</option>
                                    </select>
                                </div>

                                <!-- Fecha de Nacimiento -->
                                <div class="col-md-6">
                                    <label for="fecha_nacimiento" class="form-label fw-bold">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                                           value="<?= htmlspecialchars($perfil['fecha_nacimiento'] ?? '') ?>" required>
                                </div>

                                <!-- Dirección de Habitación -->
                                <div class="col-12">
                                    <label for="direccion" class="form-label fw-bold">Dirección Completa de Habitación</label>
                                    <textarea class="form-control" id="direccion" name="direccion" rows="3" 
                                              placeholder="Estado, Municipio, Parroquia, Avenida/Calle, Casa/Apto" required><?= htmlspecialchars($perfil['direccion'] ?? '') ?></textarea>
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

        <!-- PESTAÑA: SEGURIDAD -->
        <div class="tab-pane fade" id="content-seguridad" role="tabpanel" aria-labelledby="tab-seguridad">
            <div class="row">
                <!-- Formulario Cambio Contraseña -->
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <div class="card border-0 shadow-sm p-4">
                        <h2 class="h5 fw-bold mb-4">
                            <i class="bi bi-shield-lock-fill text-primary me-2"></i>Cambiar Contraseña de Acceso
                        </h2>
                        
                        <form id="formCambiarClave" novalidate>
                            <input type="hidden" name="peticion" value="cambiar-clave">
                            
                            <div class="row g-3">
                                <!-- Contraseña Actual -->
                                <div class="col-12">
                                    <label for="clave_actual" class="form-label fw-bold">Contraseña Actual</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" class="form-control" id="clave_actual" name="clave_actual" 
                                               placeholder="Ingrese su contraseña actual" required>
                                    </div>
                                </div>

                                <!-- Nueva Contraseña -->
                                <div class="col-12">
                                    <label for="clave_nueva" class="form-label fw-bold">Nueva Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                        <input type="password" class="form-control" id="clave_nueva" name="clave_nueva" 
                                               placeholder="Mínimo 4 caracteres" required>
                                    </div>
                                </div>

                                <!-- Confirmar Nueva Contraseña -->
                                <div class="col-12">
                                    <label for="clave_confirmar" class="form-label fw-bold">Confirmar Nueva Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-check-lg"></i></span>
                                        <input type="password" class="form-control" id="clave_confirmar" name="clave_confirmar" 
                                               placeholder="Repita la nueva contraseña" required>
                                    </div>
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
                                <li>Debe contar con al menos 4 caracteres de longitud.</li>
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

        <!-- PESTAÑA: MI ACTIVIDAD (TIMELINE) -->
        <div class="tab-pane fade" id="content-actividad" role="tabpanel" aria-labelledby="tab-actividad">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                            <h2 class="h5 fw-bold mb-0">
                                <i class="bi bi-clock-history text-primary me-2"></i>Historial de Actividad (Últimas 15 Acciones)
                            </h2>
                            <button class="btn btn-outline-primary btn-sm" id="btnRecargarActividad" title="Recargar actividad">
                                <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
                            </button>
                        </div>
                        
                        <!-- Timeline Container -->
                        <div class="timeline" id="timelineContainer">
                            <!-- AJAX cargará el contenido dinámicamente aquí -->
                            <div class="timeline-loader">
                                <i class="bi bi-arrow-repeat spin"></i>
                                <span>Cargando su historial de movimientos...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Recurso JavaScript específico para el Perfil -->
<script src="<?= BASE_URL ?>/assets/js/perfil.js" defer></script>
