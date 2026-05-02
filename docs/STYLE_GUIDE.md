# 🎨 Guía de Estilos y Arquitectura CSS - SICGOV

Esta guía detalla el estándar de desarrollo para la capa de presentación del sistema **SICGOV**, basado en principios modernos de escalabilidad, mantenibilidad y rendimiento.

---

## 🏗 Arquitectura: BEM + Anidamiento Nativo

El sistema utiliza una combinación de la metodología **BEM** (Block, Element, Modifier) y las nuevas capacidades de **anidamiento nativo** de CSS3 (sin preprocesadores como Sass).

### 1. Metodología BEM
Mantiene la especificidad baja y evita conflictos de nombres.

- **Bloque (`.block`)**: Entidad independiente (ej: `.auth-card`, `.nav-sidebar`).
- **Elemento (`.block__element`)**: Parte del bloque que no tiene significado por sí sola (ej: `.auth-card__banner`, `.auth-card__form`).
- **Modificador (`.block--modifier`)**: Versión diferente de un bloque o elemento (ej: `.btn--primary`, `.status--pending`).

### 2. Anidamiento Nativo (Native Nesting)
Utilizamos el selector `&` para anidar reglas de forma limpia.

**Regla de Oro:** En CSS nativo, `&` representa al selector padre completo. **NO** se puede usar para concatenar sufijos (como `&__element`).

✅ **Correcto:**
```css
.auth-card {
    background: white;
    
    /* Anidamiento de elementos */
    .auth-card__banner {
        color: black;
    }
    
    /* Anidamiento de pseudo-clases */
    &:hover {
        transform: scale(1.02);
    }
}
```

❌ **Incorrecto (Sintaxis Sass - NO USAR):**
```css
.auth-card {
    &__banner { /* ESTO FALLA EN CSS NATIVO */
        color: black;
    }
}
```

---

## 🌓 Modo Oscuro (Dark Mode)

El sistema implementa un modo oscuro global mediante la clase `.dark` en el elemento `<body>` o un contenedor raíz.

### Uso de Variables (Tokens)
Utilice siempre las variables de `:root` para asegurar la compatibilidad con el cambio de tema.

```css
:root {
    --bg-principal: #F4F7F6;
    --bg-tarjetas: #FFFFFF;
    --color-sidebar: #1A1C20;
    --color-acento: #FFD600;
}

.dark {
    --bg-principal: #0F1114;
    --bg-tarjetas: #1A1C20;
    --color-sidebar: #F8F9FA;
}
```

---

## 🎭 Animaciones y Transiciones Premium

Para componentes de alta interacción (como el Login o Modales), seguimos estos principios:

1.  **Easing Moderno:** Usar `cubic-bezier(0.4, 0, 0.2, 1)` para movimientos naturales.
2.  **Efecto Papel (Subtle Paper Swap):** 
    - Desplazamiento leve (`30px`).
    - Rotación 3D sutil (`5deg`) para dar profundidad.
    - Escalamiento mínimo (`0.98`) para simular capas.

---

## 🛠 Componentes Específicos

### Selectores Avanzados (Select2)
Para garantizar la visibilidad en modo oscuro:
- El fondo debe ser `--bs-tertiary-bg` o un tono similar.
- El hover debe usar un fondo translúcido (`rgba(var(--bs-primary-rgb), 0.2)`) y texto de alto contraste.

### Formularios
- **Placeholders:** Usar `rgba(255, 255, 255, 0.5)` en modo oscuro para legibilidad.
- **Inputs:** Fondo `#15171A` en modo oscuro con bordes suaves.

---

## 📋 Checklist de Estilo
- [ ] ¿He usado clases BEM descriptivas?
- [ ] ¿He evitado el uso de `!important` innecesario?
- [ ] ¿El anidamiento es compatible con CSS nativo?
- [ ] ¿He verificado el contraste en Modo Claro y Modo Oscuro?
- [ ] ¿Las animaciones son fluidas y no exageradas?
