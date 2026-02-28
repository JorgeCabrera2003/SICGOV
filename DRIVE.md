## 📚 **GUÍA DEFINITIVA Y SUPER DETALLADA PARA EL EQUIPO SICGOV**

Bienvenidos a la **guía más completa** para gestionar el proyecto como profesionales. Esto no es solo un manual, es el **ADN de nuestro equipo**.

---

## 📑 **ÍNDICE DE LA GUÍA**

1. [Filosofía del Equipo](#-filosofía-del-equipo)
2. [Estructura de Google Drive](#-estructura-de-google-drive)
3. [Tablero 1: JAD & Planificación](#-tablero-1-jad--planificación-detallado)
4. [Tablero 2: Desarrollo & Sprints](#-tablero-2-desarrollo--sprints-detallado)
5. [Sistema de Colores (El Corazón)](#-sistema-de-colores-el-corazón-de-trello)
6. [Cómo Conectar los Tableros con Unito](#-cómo-conectar-los-tableros-con-unito)
7. [Flujo de Trabajo Paso a Paso](#-flujo-de-trabajo-paso-a-paso-con-ejemplos)
8. [Herramientas Complementarias](#-herramientas-complementarias)
9. [Checklists para Cada Rol](#-checklists-para-cada-rol)
10. [Resolución de Problemas Comunes](#-resolución-de-problemas-comunes)

---

## 🧠 **1. FILOSOFÍA DEL EQUIPO**

### **Nuestros 5 Mandamientos:**

| # | Regla | Explicación |
|---|-------|-------------|
| **1** | **Si no está en Trello, no existe** | Toda tarea, idea o requisito debe estar en una tarjeta |
| **2** | **Drive es nuestra memoria** | Todos los archivos van a Google Drive con la nomenclatura correcta |
| **3** | **Un miembro, máximo 2 tareas en progreso** | Para no saturarnos y terminar lo que empezamos |
| **4** | **Las reuniones se digitalizan** | Toda acta debe escanearse con Genius Scan y subirse en 24h |
| **5** | **Los colores hablan** | Una mirada al tablero debe decirnos todo: tipo, prioridad, estado |

---

## 📁 **2. ESTRUCTURA DE GOOGLE DRIVE**

### **Árbol Completo de Carpetas**

```
📁 [SICGOV] - Sistema Good Vibes
│
├── 📁 01_INTERNO_EQUIPO (Privado - Solo equipo)
│   ├── 📁 01.1_Modelos_Drawio
│   │   └── 🎯 Archivos .drawio editables
│   │   └── Ej: modelo_bd_usuarios.drawio
│   │
│   ├── 📁 01.2_Backups_BD
│   │   └── 🗄️ Archivos .sql
│   │   └── Ej: 2026-02-28_migracion_usuarios.sql
│   │
│   ├── 📁 01.3_Borradores_Docs
│   │   └── 📝 Notas internas, versiones preliminares
│   │
│   └── 📁 01.4_Assets_Fuente
│       └── 🎨 Logos PSD, fuentes originales, iconos .ai
│
├── 📁 02_PUBLICO_PROFESORES (Lectura para profesores)
│   ├── 📁 02.1_Documentacion_Final
│   │   └── 📄 PDFs de manuales y tesis
│   │
│   ├── 📁 02.2_Diagramas
│   │   └── 🖼️ PNG/PDF exportados (versiones finales)
│   │   └── Ej: 2026-02-28_Diagrama_ER_Usuarios_v1.png
│   │
│   ├── 📁 02.3_Manuales
│   │   └── 📘 Manual de Usuario, Manual Técnico
│   │
│   └── 📁 02.4_Presentaciones
│       └── 📽️ PPT/PDF para defensas
│
├── 📁 03_EVIDENCIAS (Compartido - Con firma digital)
│   ├── 📁 03.1_Asesorias_Firmadas
│   │   └── ✍️ Actas escaneadas con Genius Scan
│   │   └── Ej: 2026-02-28_Acta_Asesoria_01.pdf
│   │
│   ├── 📁 03.2_Capturas_Sistema
│   │   └── 📸 Evolución visual del sistema
│   │   └── Ej: 2026-03-01_login_maqueta.png
│   │
│   └── 📁 03.3_Reuniones_JAD
│       └── 📋 Minutas internas del equipo
│
└── 📁 04_ENTREGAS_FINALES
    └── 🔗 Accesos_GitHub_Trello.txt (links importantes)
```

### **📏 Nomenclatura OBLIGATORIA**

| Tipo | Formato | Ejemplo |
|------|---------|---------|
| **Acta de reunión** | `YYYY-MM-DD_Acta_Asesoria_NN.pdf` | `2026-02-28_Acta_Asesoria_01.pdf` |
| **Diagrama exportado** | `YYYY-MM-DD_Diagrama_Tipo_vNN.png` | `2026-02-28_Diagrama_ER_Usuarios_v1.png` |
| **Archivo drawio** | `descripcion_breve.drawio` | `modelo_bd_usuarios.drawio` |
| **Migración SQL** | `YYYY-MM-DD_migracion_descripcion.sql` | `2026-02-28_migracion_usuarios.sql` |
| **Captura de sistema** | `YYYY-MM-DD_descripcion.png` | `2026-03-01_login_funcional.png` |

---

## 📋 **3. TABLERO 1: JAD & PLANIFICACIÓN (DETALLADO)**

### **Listas y sus Propósitos**

| # | Lista | Color | WIP* | Qué va aquí | Cuándo mover |
|---|-------|-------|------|-------------|--------------|
| 1 | **📥 BACKLOG** | `#E0E0E0` | ∞ | Ideas locas, features futuros, "algún día" | Cuando alguien tiene una idea |
| 2 | **🤝 SESIONES JAD** | `#9C27B0` | 5 | Reuniones planificadas con profesor | Al programar una reunión |
| 3 | **✅ ACORDADO CON PROFESOR** | `#4CAF50` | ∞ | Requisitos aprobados formalmente | Después de la reunión y subir acta |
| 4 | **🔍 EN REVISIÓN** | `#FF9800` | 3 | Pendiente de validación por profesor | Cuando necesitamos feedback |
| 5 | **📦 LISTO PARA DEV** | `#2196F3` | ∞ | Listo para que UNITO lo pase a desarrollo | Cuando todo está aprobado |

*WIP = Work In Progress (límite de tarjetas)

### **Plantilla de Tarjeta para SESIONES JAD**

```markdown
# 🟣 [JAD-NNN] Título descriptivo de la reunión

## 📅 DATOS DE LA REUNIÓN
- **Fecha:** DD/MM/2026
- **Hora:** HH:MM - HH:MM
- **Lugar:** [Salón / Virtual]
- **Asistentes:** @jorge @leizer @mariangel @santiago @abrahan + Profesor

## 📝 ACUERDOS Y DECISIONES
1. 
2. 
3. 

## 📎 EVIDENCIAS REQUERIDAS
- [ ] ACTA FIRMADA → Drive/03.1_Asesorias_Firmadas/
- [ ] DIAGRAMA EDITABLE → Drive/01.1_Modelos_Drawio/
- [ ] DIAGRAMA EXPORTADO → Drive/02.2_Diagramas/

## ✅ CHECKLIST DE LA TARJETA
- [ ] Digitalizar acta con Genius Scan
- [ ] Subir PDF a Drive
- [ ] Crear diagrama en Draw.io
- [ ] Guardar .drawio
- [ ] Exportar PNG
- [ ] Adjuntar archivos a Trello
- [ ] Mover a "✅ ACORDADO CON PROFESOR"
```

---

## 🚀 **4. TABLERO 2: DESARROLLO & SPRINTS (DETALLADO)**

### **Listas y sus Propósitos**

| # | Lista | Color | WIP | Qué va aquí | Quién la mueve |
|---|-------|-------|-----|-------------|----------------|
| 1 | **🎯 SPRINT ACTUAL** | `#2196F3` | ∞ | Tareas comprometidas para esta semana | UNITO automático |
| 2 | **🚧 EN PROGRESO** | `#FFC107` | 2 pp | Lo que estás programando AHORA | El desarrollador asignado |
| 3 | **🔍 QA / REVISIÓN** | `#FF9800` | 3 | Código listo para probar | El desarrollador al terminar |
| 4 | **✅ TERMINADO EN DEV** | `#4CAF50` | ∞ | Listo, pero en rama develop | El revisor al aprobar |
| 5 | **📦 EN PRODUCCIÓN** | `#212121` | ∞ | Entregado al profesor/cliente | El líder al validar |

### **Plantilla de Tarjeta para DESARROLLO**

```markdown
# 🔵 [TIPO] Descripción técnica

## 🔗 ORIGEN JAD
[Enlace a tarjeta JAD-XXX]

## 👤 ASIGNACIÓN
- **Responsable:** @usuario
- **Revisor:** @otro_usuario
- **Fecha inicio:** DD/MM
- **Fecha límite:** DD/MM

## 📋 CHECKLIST TÉCNICO
- [ ] Analizar requisitos
- [ ] Crear archivos necesarios
- [ ] Probar localmente
- [ ] Documentar cambios
- [ ] Subir a GitHub (rama feature/nombre)
- [ ] Solicitar revisión

## 📎 RECURSOS
- Drive: [link al diagrama]
- BD: [tablas afectadas]

## 💬 COMENTARIOS DE AVANCE
```

---

## 🎨 **5. SISTEMA DE COLORES (EL CORAZÓN DE TRELLO)**

### **Etiquetas - 8 Colores con Significado**

| Color | Nombre | HEX | Uso | Ejemplo |
|-------|--------|-----|-----|---------|
| 🔴 **Rojo** | `Urgente / Bloqueo` | `#EB144C` | Algo detiene el proyecto | Error en BD, servidor caído |
| 🔵 **Azul** | `Backend / Lógica` | `#0079BF` | PHP, BD, APIs | Controladores, modelos |
| 🟡 **Amarillo** | `Frontend / UI` | `#F2D600` | HTML, CSS, JS | Vistas, diseño responsive |
| 🟢 **Verde** | `Documentación` | `#61BD4F` | PDFs, actas, manuales | Subir a Drive |
| 🟣 **Morado** | `JAD / Requisito` | `#9C27B0` | Viene de reunión con profe | Tareas acordadas |
| 🟠 **Naranja** | `QA / Pruebas` | `#FF9800` | Testing, revisión | Probar funcionalidad |
| ⚫ **Negro** | `Infraestructura` | `#212121` | Servidores, Git, hosting | Configuración |
| ⚪ **Gris** | `Idea / Propuesta` | `#E0E0E0` | Sin priorizar | Backlog |

### **Reglas de Etiquetado**

```
📌 REGLA DE ORO: Toda tarjeta DEBE tener AL MENOS 2 etiquetas:
   1. Una de tipo (🔵 Backend, 🟡 Frontend, 🟢 Documentación, etc.)
   2. Una de origen (🟣 JAD si viene de reunión)

📌 EXCEPCIÓN: Las tarjetas de reunión (🟣) llevan siempre 🟢 Documentación
```

### **Ejemplos Visuales**

#### **Tarjeta de Reunión JAD**
```
┌─────────────────────────────────────────────────────┐
│ 🟣 [JAD-001] Definir BD Usuarios                    │
│ 🏷️ 🟣 JAD, 🟢 Documentación                          │
│ 📅 28/02/2026                                       │
└─────────────────────────────────────────────────────┘
```

#### **Tarjeta de Tarea Backend (viene de JAD)**
```
┌─────────────────────────────────────────────────────┐
│ 🔵 [DEV-023] Implementar Login                      │
│ 🏷️ 🔵 Backend, 🟣 JAD                               │
│ 👤 @jorge                                           │
└─────────────────────────────────────────────────────┘
```

#### **Tarjeta de Bug Crítico**
```
┌─────────────────────────────────────────────────────┐
│ 🔴 [BUG-001] Error: Captcha no carga                │
│ 🏷️ 🔴 Urgente, 🔵 Backend, 🟡 Frontend               │
│ 👤 @jorge, @leizer                                  │
└─────────────────────────────────────────────────────┘
```

---

## 🔗 **6. CÓMO CONECTAR LOS TABLEROS CON UNITO**

### **Paso 1: Activar el Power-Up**

1. Abre **Tablero 1 (JAD)**
2. Haz clic en **"Mostrar menú"** (parte superior derecha)
3. Ve a **"Power-Ups"** → **"Añadir Power-Ups"**
4. Busca **"2-Way Card Mirror Plus by Unito"**
5. Haz clic en **"Añadir"**

### **Paso 2: Crear el Flujo de Sincronización**

```
🔧 CONFIGURACIÓN DEL FLUJO:

1. Haz clic en "Create flow"
2. Selecciona "Connect two boards"

3. PRIMER TABLERO (Origen):
   - Tablero: JAD - SICGOV
   - Lista: 📦 LISTO PARA DEV
   - Disparador: "When a card is added to this list"

4. SEGUNDO TABLERO (Destino):
   - Tablero: DEV - SICGOV
   - Lista: 🎯 SPRINT ACTUAL
   - Acción: "Create a new card"

5. MAPEO DE CAMPOS:
   - Título: [Título original] + " [DEV]"
   - Descripción: Copiar completa
   - Miembros: Asignar al líder técnico (@jorge)
   - Etiquetas: Copiar todas + agregar 🔵 (Backend por defecto)
   - Adjuntos: Copiar todos los archivos de Drive
```

### **Paso 3: Probar la Sincronización**

```
🧪 PRUEBA RÁPIDA:

1. En Tablero 1, crea una tarjeta en "📦 LISTO PARA DEV"
2. Título: "[TEST] Tarjeta de prueba"
3. Adjunta un archivo cualquiera
4. Espera 10-30 segundos
5. Ve a Tablero 2 → "🎯 SPRINT ACTUAL"
6. ✅ Deberías ver la tarjeta con el sufijo "[DEV]"
7. ✅ Debería tener el enlace a la tarjeta original
```

---

## 🔄 **7. FLUJO DE TRABAJO PASO A PASO CON EJEMPLOS**

### **ESCENARIO REAL: Reunión para definir BD de Usuarios**

#### **FASE 1: Antes de la Reunión**

```
📅 DÍA ANTES (Jorge):
   1. Crear tarjeta en "🤝 SESIONES JAD"
   2. Título: "🟣 [JAD-001] Definir BD Usuarios"
   3. Agregar etiquetas: 🟣 JAD, 🟢 Documentación
   4. Invitar a todos los miembros
   5. Poner fecha: 28/02/2026 10:00

   La tarjeta se ve así:
   ┌─────────────────────────────────────────────┐
   │ 🟣 [JAD-001] Definir BD Usuarios            │
   │ 📅 28/02 10:00                              │
   │ 👥 @jorge @leizer @mariangel @santiago      │
   │    @abrahan + Profesor                      │
   │ 🏷️ 🟣 JAD, 🟢 Documentación                  │
   └─────────────────────────────────────────────┘
```

#### **FASE 2: Durante la Reunión**

```
📝 TODOS:
   - Tomar notas en papel
   - Definir estructuras de tablas
   - Acordar responsables

🖥️ Jorge (anotando en la tarjeta):
   - Agregar a la descripción los acuerdos
   - Marcar checklist de pendientes
```

#### **FASE 3: Después de la Reunión (Primeras 24h)**

```
⏰ 14:00 - Leizer (Digitalización):
   1. Escanear acta con Genius Scan
   2. Guardar como: 2026-02-28_Acta_Asesoria_01.pdf
   3. Subir a Drive/03.1_Asesorias_Firmadas/
   4. Adjuntar PDF a la tarjeta
   5. Comentar: "✅ Acta subida a Drive"

⏰ 15:30 - Mariangel (Diagramas):
   1. Abrir Draw.io → Guardar en Google Drive
   2. Crear diagrama entidad-relación
   3. Guardar editable: modelo_bd_usuarios.drawio
      📁 Drive/01.1_Modelos_Drawio/
   4. Exportar PNG: 2026-02-28_Diagrama_ER_Usuarios_v1.png
      📁 Drive/02.2_Diagramas/
   5. Adjuntar ambos archivos a la tarjeta
   6. Comentar: "✅ Diagramas listos"

⏰ 16:30 - Santiago (Verificación):
   1. Revisar que todos los archivos están adjuntos
   2. Marcar todo el checklist como completo
   3. Mover tarjeta a "✅ ACORDADO CON PROFESOR"
   4. Comentar: "✅ Todo listo, @jorge para revisión con profesor"
```

#### **FASE 4: Validación con Profesor**

```
📅 SIGUIENTE CLASE - Jorge:
   1. Mostrar al profesor la tarjeta y los archivos
   2. Si el profesor aprueba:
      - Mover tarjeta a "📦 LISTO PARA DEV"
      - Comentar: "✅ Profesor aprobó, pasa a desarrollo"
   3. Si pide cambios:
      - Mover tarjeta a "🔍 EN REVISIÓN"
      - Agregar checklist con los cambios solicitados
      - @mencionar al responsable
```

#### **FASE 5: UNITO - Sincronización Automática**

```
⚡ AUTOMÁTICO (5 segundos después):
   - UNITO detecta la tarjeta en "📦 LISTO PARA DEV"
   - Crea tarjeta gemela en Tablero 2 → "🎯 SPRINT ACTUAL"
   - Título: "[JAD-001] Definir BD Usuarios [DEV]"
   - Copia todos los adjuntos
   - Agrega enlace a la tarjeta original
   - Asigna a @jorge (líder técnico)
```

#### **FASE 6: Desarrollo (Tablero 2)**

```
📌 DÍA 1 - Jorge (asignación):
   1. Ve la nueva tarjeta en "🎯 SPRINT ACTUAL"
   2. La asigna a @abrahan (encargado de BD)
   3. Comenta: "@abrahan tu tarea, revisa diagramas"

📌 DÍA 1 - Abrahan (inicio):
   1. Mueve tarjeta a "🚧 EN PROGRESO"
   2. Abre los diagramas adjuntos
   3. Crea archivo: 2026-02-28_migracion_usuarios.sql
   4. Guarda en Drive/01.2_Backups_BD/
   5. Comenta: "✅ Migración creada, probando local"

📌 DÍA 2 - Abrahan (finalización):
   1. Ejecuta migración en su XAMPP local
   2. Toma captura: 2026-02-29_bd_usuarios_creada.png
   3. Guarda en Drive/03.2_Capturas_Sistema/
   4. Adjunta captura a la tarjeta
   5. Mueve a "🔍 QA / REVISIÓN"
   6. Comenta: "@santiago para revisión por favor"

📌 DÍA 2 - Santiago (QA):
   1. Prueba la migración en su máquina
   2. Si funciona ✅:
      - Mueve a "✅ TERMINADO EN DEV"
      - Comenta: "✅ Aprobado, listo para producción"
   3. Si falla ❌:
      - Mueve de vuelta a "🚧 EN PROGRESO"
      - Comenta el error
```

---

## 🛠️ **8. HERRAMIENTAS COMPLEMENTARIAS**

### **Genius Scan (Digitalización de Actas)**

```
📱 CONFIGURACIÓN RECOMENDADA:

1. Modo: "Documento" (Blanco y negro)
2. Resolución: 200 DPI (suficiente, no pesa)
3. Formato: PDF
4. Destino: Google Drive

📋 PASOS:
   1. Escanear cada página del acta
   2. Unir todas las páginas en un solo PDF
   3. Nombrar: YYYY-MM-DD_Acta_Asesoria_NN.pdf
   4. Subir a Drive/03.1_Asesorias_Firmadas/
   5. Adjuntar a la tarjeta en Trello
```

### **Draw.io (Diagramas UML)**

```
💻 CONFIGURACIÓN:

1. Abrir app.diagrams.net
2. Seleccionar "Guardar en Google Drive"
3. Crear carpeta: SICGOV/01_INTERNO_EQUIPO/01.1_Modelos_Drawio/

📋 FLUJO DE TRABAJO:
   1. Crear diagrama editable (.drawio)
      → Guardar en 01.1_Modelos_Drawio/
   2. Exportar a PNG (Archivo → Exportar → PNG)
      → Guardar en 02.2_Diagramas/
      → Nombre: YYYY-MM-DD_Descripcion_vN.png
   3. Adjuntar AMBOS archivos a la tarjeta
```

### **GitHub (Código)**

```
🌿 RAMAS:
   - main: producción (solo código estable)
   - develop: integración (lo que funciona)
   - feature/nombre: tu rama personal

📝 COMMITS:
   Formato: [TIPO]: Descripción corta
   Ejemplo: "FEAT: Implementar login con reCAPTCHA"

📋 TIPOS DE COMMIT:
   - FEAT: Nueva funcionalidad
   - FIX: Corrección de bug
   - DOCS: Documentación
   - STYLE: CSS, formato
```

---

## ✅ **9. CHECKLISTS PARA CADA ROL**

### **Checklist de Jorge (Líder Técnico)**

```
📋 CADA MAÑANA:
   [ ] Revisar Tablero 1 (nuevas tareas JAD)
   [ ] Revisar Tablero 2 (qué está en progreso)
   [ ] Asignar tareas pendientes
   [ ] Responder menciones

📋 ANTES DE REUNIÓN CON PROFESOR:
   [ ] Ver tarjetas en "🔍 EN REVISIÓN"
   [ ] Preparar demo de lo terminado
   [ ] Imprimir actas para firmar

📋 FIN DE SEMANA:
   [ ] Revisar "✅ TERMINADO EN DEV"
   [ ] Mover a "📦 EN PRODUCCIÓN" si aplica
   [ ] Preparar informe de avance
```

### **Checklist de Leizer (Documentación)**

```
📋 DESPUÉS DE CADA REUNIÓN (24h):
   [ ] Digitalizar acta con Genius Scan
   [ ] Subir a Drive/03.1_Asesorias_Firmadas/
   [ ] Nombrar correctamente: YYYY-MM-DD_Acta_Asesoria_NN.pdf
   [ ] Adjuntar a la tarjeta en Trello
   [ ] Comentar en la tarjeta: "✅ Acta subida"

📋 SEMANAL:
   [ ] Revisar que todas las actas estén en Drive
   [ ] Verificar nomenclatura correcta
```

### **Checklist de Mariangel (Diagramas)**

```
📋 POR CADA NUEVO REQUISITO:
   [ ] Leer especificaciones en tarjeta JAD
   [ ] Crear diagrama en Draw.io
   [ ] Guardar editable en 01.1_Modelos_Drawio/
   [ ] Exportar PNG a 02.2_Diagramas/
   [ ] Adjuntar ambos archivos
   [ ] Comentar: "✅ Diagramas listos"
```

### **Checklist de Santiago (QA/Pruebas)**

```
📋 CUANDO LE ASIGNAN UNA REVISIÓN:
   [ ] Mover tarjeta de "🚧 EN PROGRESO" a "🔍 QA"
   [ ] Probar en mi entorno local
   [ ] Si funciona ✅:
       - Mover a "✅ TERMINADO EN DEV"
       - Comentar: "✅ Aprobado"
   [ ] Si falla ❌:
       - Mover de vuelta a "🚧 EN PROGRESO"
       - Explicar el error en comentarios
       - @mencionar al responsable
```

### **Checklist de Abrahan (Desarrollador)**

```
📋 AL INICIAR UNA TAREA:
   [ ] Mover tarjeta a "🚧 EN PROGRESO"
   [ ] Leer requisitos y diagramas
   [ ] Crear rama en Git: feature/descripcion

📋 DURANTE EL DESARROLLO:
   [ ] Commits pequeños y descriptivos
   [ ] Marcar checklist de la tarjeta
   [ ] Comentar avances diarios

📋 AL TERMINAR:
   [ ] Probar localmente
   [ ] Subir a GitHub
   [ ] Tomar captura de pantalla
   [ ] Subir captura a Drive/03.2_Capturas_Sistema/
   [ ] Adjuntar captura a la tarjeta
   [ ] Mover a "🔍 QA / REVISIÓN"
   [ ] @mencionar al revisor
```

---

## ❓ **10. RESOLUCIÓN DE PROBLEMAS COMUNES**

### **Problema 1: UNITO no sincroniza**
```
🔍 CAUSAS POSIBLES:
   - La tarjeta no está en "📦 LISTO PARA DEV"
   - El Power-Up no está activado en ambos tableros
   - Límite de acciones gratuitas alcanzado

✅ SOLUCIÓN:
   1. Verificar que la tarjeta esté en la lista correcta
   2. Revisar que el Power-Up esté activado
   3. Si es urgente, crear tarjeta manualmente con enlace
```

### **Problema 2: Archivos no se ven en Trello**
```
🔍 CAUSAS POSIBLES:
   - No se adjuntaron correctamente
   - Permisos de Drive incorrectos

✅ SOLUCIÓN:
   1. Revisar que el archivo esté en Drive
   2. Verificar que tenga permisos "Cualquier persona con el enlace"
   3. Volver a adjuntar en Trello
```

### **Problema 3: Alguien tiene más de 2 tareas en progreso**
```
🔍 CAUSA: Estamos empezando mucho y terminando poco

✅ SOLUCIÓN:
   1. Reunión rápida de equipo
   2. Reasignar tareas
   3. Priorizar terminar antes de empezar nuevas
```

### **Problema 4: El profesor pide cambios en algo ya terminado**
```
🔍 CAUSA: Cambio de requisitos (normal en JAD)

✅ SOLUCIÓN:
   1. Crear nueva tarjeta en Tablero 1: "🟣 [JAD-NNN] Modificación X"
   2. Mover la tarjeta original a "🔍 EN REVISIÓN"
   3. Cuando el profe apruebe, pasar a desarrollo
```

---

## 🎯 **RESUMEN VISUAL DE TODO EL FLUJO**

```
REUNIÓN PRESENCIAL
        ↓
   TARJETA en "🤝 SESIONES JAD" (🟣)
        ↓
   DIGITALIZAR acta (Genius Scan)
   CREAR diagramas (Draw.io)
        ↓
   SUBIR a DRIVE (03.1, 01.1, 02.2)
        ↓
   ADJUNTAR a tarjeta
        ↓
   MOVER a "✅ ACORDADO CON PROFESOR" (🟢)
        ↓
   PROFESOR APRUEBA → MOVER a "📦 LISTO PARA DEV" (🔵)
        ↓
   ⚡ UNITO sincroniza automáticamente
        ↓
   TARJETA en TABLERO 2 → "🎯 SPRINT ACTUAL" (🔵)
        ↓
   DESARROLLADOR asigna, mueve a "🚧 EN PROGRESO" (🟡)
        ↓
   CODIFICA, COMMITS, CAPTURAS
        ↓
   MUEVE a "🔍 QA / REVISIÓN" (🟠)
        ↓
   REVISOR prueba → Si OK → "✅ TERMINADO EN DEV" (🟢)
        ↓
   LÍDER valida → "📦 EN PRODUCCIÓN" (⚫)
```

---

## 📢 **MENSAJE FINAL PARA EL EQUIPO**

> "Equipo, esta guía es nuestra Biblia. Cada vez que tengan una duda, vuelvan aquí. Los colores no son decoración, son nuestro lenguaje. Las carpetas no son un trámite, son nuestra memoria. Trello no es una lista, es nuestro cerebro colectivo.
>
> Si seguimos esto al pie de la letra, el profesor no solo nos pondrá buena nota, nos preguntará cómo lo hicimos para estar tan organizados.
>
> ¡Vamos con todo! 🚀" - Jorge (Líder Técnico)

---

**¿Quieres que te ayude a crear la primera tarjeta JAD con toda esta estructura ya predefinida?** 🎯
