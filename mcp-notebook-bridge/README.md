# MCP Notebook Bridge 📓⚡

Servidor de **Model Context Protocol (MCP)** para conectar **Antigravity IDE** (y otros agentes/LLMs compatibles) con cuadernos interactivos **Jupyter Notebook (.ipynb)**.

Permite al agente:
- Leer el contenido estructurado de celdas (código y markdown).
- Inspeccionar resultados y salidas previas (`outputs`: errores, texto plano, trazas).
- Obtener un esquema rápido del notebook sin consumir contexto innecesario.
- Editar celdas existentes preservando la estructura del JSON.
- Insertar nuevas celdas de código o markdown.

---

## 🛠️ Requisitos e Instalación

1. Asegúrate de tener **Node.js** (>= 18) instalado.
2. Instala las dependencias y compila el proyecto:

```powershell
cd c:\xampp\htdocs\mcp-notebook-bridge
npm install
npm run build
```

---

## ⚙️ Configuración en Antigravity IDE

Para que Antigravity reconozca automáticamente este servidor, regístralo en tu archivo de configuración de MCP:

### Ubicación del archivo de configuración global:
`~/.gemini/config/mcp_config.json`

### Contenido:
```json
{
  "mcpServers": {
    "notebook-bridge": {
      "command": "node",
      "args": [
        "c:/xampp/htdocs/mcp-notebook-bridge/dist/index.js"
      ],
      "env": {}
    }
  }
}
```

---

## 🧰 Herramientas Expuestas (MCP Tools)

| Herramienta | Descripción | Parámetros principales |
| :--- | :--- | :--- |
| `read_notebook_cells` | Lee todas las celdas y salidas de ejecución de un archivo `.ipynb`. | `filePath` (string), `includeOutputs` (boolean), `maxOutputLength` (number) |
| `get_notebook_outline` | Obtiene un resumen rápido del cuaderno (tipo de celda, cantidad de líneas y vista previa). | `filePath` (string) |
| `edit_notebook_cell` | Modifica el código o texto de una celda específica. | `filePath` (string), `cellIndex` (number), `newSource` (string) |
| `add_notebook_cell` | Agrega una celda al cuaderno en una posición específica o al final. | `filePath` (string), `cellType` ("code" \| "markdown"), `source` (string), `insertAtIndex` (number, opcional) |

---

## 🧪 Prueba Manual en Consola

Puedes probar el funcionamiento del servidor directamente con Node:

```powershell
node dist/index.js
```
(El servidor iniciará esperando mensajes JSON-RPC en `stdin`).
