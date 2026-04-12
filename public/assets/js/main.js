/**
 * MAIN.JS - Funcionalidades globales
 * 
 * Incluye:
 * - Sidebar colapsable con persistencia
 * - Toggle de tema (claro/oscuro)
 * - Bandeja de notificaciones interactiva
 * - Perfil de usuario
 * - Botón volver arriba
 */

const SICGOV = (function($) {
    'use strict';

    // ==========================================
    // CONFIGURACIÓN
    // ==========================================
    
    const CONFIG = {
        selectors: {
            themeToggle: '#theme-toggle',
            themeIcon: '#theme-icon',
            sidebar: '#sidebar',
            mainContent: '#main-content',
            collapseBtn: '#collapse-btn',
            sidebarToggle: '#sidebar-toggle',
            notificationBadge: '#notificationBadge',
            notificationList: '#notificationList',
            markAllRead: '#markAllRead'
        },
        classes: {
            dark: 'dark',
            collapsed: 'collapsed',
            expanded: 'expanded',
            mobileOpen: 'mobile-open'
        },
        icons: {
            moon: 'bi-moon-stars',
            sun: 'bi-brightness-high',
            chevronLeft: 'bi-chevron-left',
            chevronRight: 'bi-chevron-right'
        },
        storage: {
            theme: 'sicgov-theme',
            sidebar: 'sicgov-sidebar'
        }
    };

    // ==========================================
    // TEMA (claro/oscuro)
    // ==========================================
    
    function initTheme() {
        const $toggle = $(CONFIG.selectors.themeToggle);
        const $icon = $(CONFIG.selectors.themeIcon);
        
        if (!$toggle.length) return;

        // Detectar preferencia del sistema
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const savedTheme = localStorage.getItem(CONFIG.storage.theme);

        // Aplicar tema guardado o preferencia del sistema
        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            document.documentElement.classList.add(CONFIG.classes.dark);
            $icon.removeClass(CONFIG.icons.moon).addClass(CONFIG.icons.sun);
        }

        // Evento click
        $toggle.on('click', function(e) {
            e.preventDefault();
            
            const html = document.documentElement;
            html.classList.toggle(CONFIG.classes.dark);
            
            const isDark = html.classList.contains(CONFIG.classes.dark);
            localStorage.setItem(CONFIG.storage.theme, isDark ? 'dark' : 'light');
            
            $icon.toggleClass(CONFIG.icons.moon, !isDark)
                 .toggleClass(CONFIG.icons.sun, isDark);
        });
    }

    // ==========================================
    // SIDEBAR COLAPSABLE
    // ==========================================
    
    function initSidebar() {
        const $sidebar = $(CONFIG.selectors.sidebar);
        const $mainContent = $(CONFIG.selectors.mainContent);
        const $collapseBtn = $(CONFIG.selectors.collapseBtn);
        const $toggleBtn = $(CONFIG.selectors.sidebarToggle);
        const $closeBtn = $('#sidebar-close');

        // Solo si existe el botón de colapsar (escritorio)
        if ($collapseBtn.length) {
            // Recuperar estado guardado
            const savedState = localStorage.getItem(CONFIG.storage.sidebar);
            if (savedState === CONFIG.classes.collapsed) {
                $sidebar.addClass(CONFIG.classes.collapsed);
                $mainContent.addClass(CONFIG.classes.expanded);
                updateChevron($collapseBtn, true);
            }

            // Evento colapsar
            $collapseBtn.on('click', function() {
                $sidebar.toggleClass(CONFIG.classes.collapsed);
                $mainContent.toggleClass(CONFIG.classes.expanded);
                
                const isCollapsed = $sidebar.hasClass(CONFIG.classes.collapsed);
                localStorage.setItem(CONFIG.storage.sidebar, 
                    isCollapsed ? CONFIG.classes.collapsed : '');
                
                updateChevron($(this), isCollapsed);
            });
        }

        // Móvil: abrir sidebar
        if ($toggleBtn.length && $sidebar.length) {
            $toggleBtn.on('click', function(e) {
                e.stopPropagation();
                $sidebar.addClass(CONFIG.classes.mobileOpen);
            });
        }

        // Móvil: cerrar sidebar
        if ($closeBtn.length) {
            $closeBtn.on('click', function() {
                $sidebar.removeClass(CONFIG.classes.mobileOpen);
            });
        }

        // Cerrar al hacer click fuera (móvil)
        $(document).on('click', function(e) {
            if (window.innerWidth >= 992) return;
            
            const $target = $(e.target);
            if (!$target.closest(CONFIG.selectors.sidebar).length && 
                !$target.closest(CONFIG.selectors.sidebarToggle).length) {
                $sidebar.removeClass(CONFIG.classes.mobileOpen);
            }
        });
    }

    function updateChevron($btn, isCollapsed) {
        const $icon = $btn.find('i');
        $icon.toggleClass(CONFIG.icons.chevronLeft, !isCollapsed)
             .toggleClass(CONFIG.icons.chevronRight, isCollapsed);
    }

    // ==========================================
    // NOTIFICACIONES
    // ==========================================
    
    function initNotifications() {
        cargarNotificaciones();
        
        // Recargar cada 30 segundos
        setInterval(cargarNotificaciones, 30000);
        
        // Marcar todas como leídas
        $(CONFIG.selectors.markAllRead).on('click', function(e) {
            e.preventDefault();
            marcarTodasLeidas();
        });
    }

    function cargarNotificaciones() {
        $.ajax({
            url: BASE_URL + '/?page=notificaciones&action=listar',
            type: 'GET',
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                renderNotificaciones(response.data);
                actualizarBadge(response.noLeidas);
            }
        })
        .fail(function() {
            console.error('Error al cargar notificaciones');
        });
    }

    function renderNotificaciones(notificaciones) {
        const $list = $(CONFIG.selectors.notificationList);
        
        if (!notificaciones || notificaciones.length === 0) {
            $list.html(`
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                    <span>No hay notificaciones</span>
                </div>
            `);
            return;
        }

        let html = '';
        notificaciones.forEach(notif => {
            const iconClass = getIconClass(notif.tipo);
            const unreadClass = notif.leida ? '' : 'unread';
            
            html += `
                <div class="notification-item ${unreadClass}" data-id="${notif.id}">
                    <div class="notification-icon ${iconClass}">
                        <i class="bi ${getIcon(notif.tipo)}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${notif.titulo}</div>
                        <div class="notification-text">${notif.mensaje}</div>
                        <div class="notification-time">${notif.hace}</div>
                    </div>
                    <div class="notification-actions">
                        ${!notif.leida ? 
                            `<button class="btn btn-sm btn-link p-0 mark-read" data-id="${notif.id}" title="Marcar como leída">
                                <i class="bi bi-check"></i>
                            </button>` : 
                            ''
                        }
                    </div>
                </div>
            `;
        });

        $list.html(html);
    }

    function getIconClass(tipo) {
        const clases = {
            'exito': 'success',
            'info': 'info',
            'alerta': 'warning',
            'default': 'primary'
        };
        return clases[tipo] || clases.default;
    }

    function getIcon(tipo) {
        const iconos = {
            'exito': 'bi-check-circle-fill',
            'info': 'bi-info-circle-fill',
            'alerta': 'bi-exclamation-triangle-fill',
            'default': 'bi-bell-fill'
        };
        return iconos[tipo] || iconos.default;
    }

    function actualizarBadge(noLeidas) {
        const $badge = $(CONFIG.selectors.notificationBadge);
        
        if (noLeidas > 0) {
            $badge.text(noLeidas).show();
        } else {
            $badge.hide();
        }
    }

    function marcarTodasLeidas() {
        $.ajax({
            url: BASE_URL + '/?page=notificaciones&action=marcar-todas',
            type: 'POST',
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                cargarNotificaciones();
            }
        });
    }

    // ==========================================
    // PERFIL DE USUARIO
    // ==========================================
    
    function initUserMenu() {
        // Cargar datos del perfil si es necesario
        // Por ahora solo manejamos el logout
    }

    // ==========================================
    // VOLVER ARRIBA
    // ==========================================
    
    function initBackToTop() {
        const $backBtn = $('.back-to-top');
        if (!$backBtn.length) return;

        $(window).on('scroll', function() {
            if ($(this).scrollTop() > 300) {
                $backBtn.addClass('active');
            } else {
                $backBtn.removeClass('active');
            }
        });

        $backBtn.on('click', function(e) {
            e.preventDefault();
            $('html, body').animate({ scrollTop: 0 }, 300);
        });
    }

    // ==========================================
    // INICIALIZACIÓN
    // ==========================================
    
    $(document).ready(function() {
        initTheme();
        initSidebar();
        initNotifications();
        initUserMenu();
        initBackToTop();
        
        console.log('✅ SICGOV inicializado correctamente');
    });

    // API pública (opcional)
    return {
        notificaciones: {
            recargar: cargarNotificaciones
        },
        tema: {
            toggle: function() { $(CONFIG.selectors.themeToggle).trigger('click'); }
        }
    };

})(jQuery);